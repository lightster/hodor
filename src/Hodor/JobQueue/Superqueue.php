<?php

namespace Hodor\JobQueue;

use DateTime;
use Hodor\Database\AdapterFactory as DbAdapterFactory;
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
     * @return int
     */
    public function queueJobsFromDatabaseToWorkerQueue()
    {
        $db = $this->getDatabase();

        $db->beginTransaction();
        $job_generator = $db->getJobsToRunGenerator();
        $jobs_queued = 0;
        foreach ($job_generator() as $job) {
            $meta = $db->markJobAsQueued($job);

            $queue = $this->queue_factory->getWorkerQueue($job['queue_name']);
            $queue->push($job['queue_name'], $job['job_params'], $meta);

            ++$jobs_queued;
        }

        $db->commitTransaction();

        return $jobs_queued;
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

        $db = $this->getDatabase();
        $db->beginTransaction();

        $mark_finished($meta);

        $db->commitTransaction();
        $message->acknowledge();
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
