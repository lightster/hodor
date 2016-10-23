<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\Adapter\SuperqueuerInterface;

class Superqueue
{
    /**
     * @var FactoryInterface
     */
    private $database;

    /**
     * @var WorkerQueueFactory
     */
    private $worker_queue_factory;

    /**
     * @var int
     */
    private $jobs_queued = 0;

    /**
     * @param SuperqueuerInterface $database
     * @param WorkerQueueFactory $worker_queue_factory
     */
    public function __construct(SuperqueuerInterface $database, WorkerQueueFactory $worker_queue_factory)
    {
        $this->database = $database;
        $this->worker_queue_factory = $worker_queue_factory;
    }

    /**
     * @return bool
     */
    public function requestProcessLock()
    {
        return $this->database->requestAdvisoryLock('superqueuer', 'default');
    }

    /**
     * @return int
     */
    public function queueJobsFromDatabaseToWorkerQueue()
    {
        $job_generator = $this->database->getJobsToRunGenerator();
        $total_jobs_queued = 0;
        foreach ($job_generator as $job) {
            $this->batchJob($job);
            ++$total_jobs_queued;
        }

        $this->publishBatch();

        return $total_jobs_queued;
    }

    /**
     * @param array $job
     */
    private function batchJob(array $job)
    {
        $db = $this->database;

        if (0 === $this->jobs_queued) {
            $this->worker_queue_factory->beginBatch();
            $db->beginBatch();
        }

        $meta = $db->markJobAsQueued($job);

        $queue = $this->worker_queue_factory->getWorkerQueue($job['queue_name']);
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
        $this->database->publishBatch();
        $this->worker_queue_factory->publishBatch();

        $this->jobs_queued = 0;
    }

    /**
     * @return int
     */
    private function getBatchSize()
    {
        return 250;
    }
}
