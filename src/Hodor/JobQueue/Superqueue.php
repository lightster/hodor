<?php

namespace Hodor\JobQueue;

use DateTime;
use Hodor\Database\AdapterInterface as DbAdapterInterface;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Hodor\MessageQueue\IncomingMessage;

class Superqueue
{
    /**
     * @var QueueManager
     */
    private $queue_manager;

    /**
     * @var DbAdapterInterface
     */
    private $database;

    /**
     * @var int
     */
    private $jobs_queued = 0;

    /**
     * @param QueueManager $queue_manager
     */
    public function __construct(QueueManager $queue_manager)
    {
        $this->queue_manager = $queue_manager;
    }

    /**
     * @param IncomingMessage $message
     */
    public function bufferJobFromBufferQueueToDatabase(IncomingMessage $message)
    {
        $db = $this->getDatabase();

        $db->beginTransaction();

        $content = $message->getContent();
        $queue_name = $this->queue_manager->getWorkerQueueNameForJob(
            $content['name'],
            $content['params'],
            $content['options']
        );

        $db->bufferJob($queue_name, [
            'name'    => $content['name'],
            'params'  => $content['params'],
            'options' => $content['options'],
            'meta'    => $content['meta'],
        ]);

        $db->commitTransaction();
        $message->acknowledge();
    }

    /**
     * @return bool
     */
    public function requestProcessLock()
    {
        return $this->getDatabase()->requestAdvisoryLock('superqueuer', 'default');
    }

    /**
     * @return int
     */
    public function queueJobsFromDatabaseToWorkerQueue()
    {
        $job_generator = $this->getDatabase()->getJobsToRunGenerator();
        $total_jobs_queued = 0;
        foreach ($job_generator as $job) {
            $this->batchJob($job);
            ++$total_jobs_queued;
        }

        $this->publishBatch();

        return $total_jobs_queued;
    }

    /**
     * @param IncomingMessage $message
     * @param DateTime $started_running_at
     */
    public function markJobAsSuccessful(IncomingMessage $message, DateTime $started_running_at)
    {
        $this->markJobAsFinished($message, $started_running_at, function ($meta) {
            $this->getDatabase()->markJobAsSuccessful($meta);
        });
    }

    /**
     * @param IncomingMessage $message
     * @param DateTime $started_running_at
     */
    public function markJobAsFailed(IncomingMessage $message, DateTime $started_running_at)
    {
        $this->markJobAsFinished($message, $started_running_at, function ($meta) {
            $this->getDatabase()->markJobAsFailed($meta);
        });
    }

    /**
     * @param array $job
     */
    private function batchJob(array $job)
    {
        $db = $this->getDatabase();

        if (0 === $this->jobs_queued) {
            $this->queue_manager->beginBatch();
            $db->beginTransaction();
        }

        $meta = $db->markJobAsQueued($job);

        $queue = $this->queue_manager->getWorkerQueue($job['queue_name']);
        $queue->push($job['job_name'], $job['job_params'], $meta);

        ++$this->jobs_queued;

        $this->publishBatchIfNecessary();
    }

    private function publishBatchIfNecessary()
    {
        if ($this->jobs_queued >= $this->getBatchSize()) {
            $this->publishBatch();
        }
    }

    private function publishBatch()
    {
        // the database transaction needs to be committed before the
        // message is pushed to Rabbit MQ to prevent jobs from being
        // processed by workers before they have been moved to buffered_jobs
        $this->getDatabase()->commitTransaction();
        $this->queue_manager->publishBatch();

        $this->jobs_queued = 0;
    }

    /**
     * @return int
     */
    private function getBatchSize()
    {
        return 250;
    }

    /**
     * @param IncomingMessage $message
     * @param DateTime $started_running_at
     * @param callable $mark_finished
     * @throws BufferedJobNotFoundException
     */
    private function markJobAsFinished(
        IncomingMessage $message,
        DateTime $started_running_at,
        callable $mark_finished
    ) {
        $content = $message->getContent();
        $meta = $content['meta'];
        $meta['started_running_at'] = $started_running_at->format('c');

        try {
            $db = $this->getDatabase();
            $db->beginTransaction();

            $mark_finished($meta);

            $db->commitTransaction();
            $message->acknowledge();
        } catch (BufferedJobNotFoundException $exception) {
            $message->acknowledge();
            throw $exception;
        }
    }

    /**
     * @return DbAdapterInterface
     */
    private function getDatabase()
    {
        if ($this->database) {
            return $this->database;
        }

        $this->database = $this->queue_manager->getDatabase();

        return $this->database;
    }
}
