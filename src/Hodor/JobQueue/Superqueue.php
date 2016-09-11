<?php

namespace Hodor\JobQueue;

use DateTime;
use Hodor\Database\Adapter\FactoryInterface;
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
     * @return bool
     */
    public function requestProcessLock()
    {
        return $this->getDatabase()->getSuperqueuer()->requestAdvisoryLock('superqueuer', 'default');
    }

    /**
     * @return int
     */
    public function queueJobsFromDatabaseToWorkerQueue()
    {
        $job_generator = $this->getDatabase()->getSuperqueuer()->getJobsToRunGenerator();
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
        $db = $this->getDatabase();

        if (0 === $this->jobs_queued) {
            $this->queue_manager->beginBatch();
            $db->getSuperqueuer()->beginBatch();
        }

        $meta = $db->getSuperqueuer()->markJobAsQueued($job);

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
        $this->getDatabase()->getSuperqueuer()->publishBatch();
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
     * @return FactoryInterface
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
