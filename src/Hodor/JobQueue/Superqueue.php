<?php

namespace Hodor\JobQueue;

use DateTime;
use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Hodor\MessageQueue\Message;

class Superqueue
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var QueueFactory
     */
    private $queue_factory;

    /**
     * @var DbAdapterInterface
     */
    private $database;

    /**
     * @var int
     */
    private $jobs_queued = 0;

    /**
     * @param array $config
     * @param QueueFactory $queue_factory
     */
    public function __construct(array $config, QueueFactory $queue_factory)
    {
        $this->config = $config;
        $this->queue_factory = $queue_factory;
    }

    /**
     * @param Message $message
     */
    public function bufferJobFromBufferQueueToDatabase(Message $message)
    {
        $db = $this->getDatabase();

        $db->beginTransaction();

        $content = $message->getContent();
        $queue_name = $this->queue_factory->getWorkerQueueNameForJob(
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
     * @param Message $message
     * @param DateTime $started_running_at
     */
    public function markJobAsSuccessful(Message $message, DateTime $started_running_at)
    {
        $this->markJobAsFinished($message, $started_running_at, function ($meta) {
            $this->getDatabase()->markJobAsSuccessful($meta);
        });
    }

    /**
     * @param Message $message
     * @param DateTime $started_running_at
     */
    public function markJobAsFailed(Message $message, DateTime $started_running_at)
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
            $this->queue_factory->beginBatch();
            $db->beginTransaction();
        }

        $meta = $db->markJobAsQueued($job);

        $queue = $this->queue_factory->getWorkerQueue($job['queue_name']);
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
        $this->queue_factory->publishBatch();

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
     * @param Message $message
     * @param DateTime $started_running_at
     * @param callable $mark_finished
     */
    private function markJobAsFinished(
        Message $message,
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

        $db_adapter_factory = new DbAdapterFactory($this->config['database']);

        $this->database = $db_adapter_factory->getAdapter($this->config['database']['type']);

        return $this->database;
    }
}
