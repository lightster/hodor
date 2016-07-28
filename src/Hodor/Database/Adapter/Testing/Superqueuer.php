<?php

namespace Hodor\Database\Adapter\Testing;

use Generator;
use Hodor\Database\Adapter\SuperqueuerInterface;

class Superqueuer implements SuperqueuerInterface
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var int
     */
    private $connection_id;

    /**
     * @var bool
     */
    private $in_batch = false;

    /**
     * @var array
     */
    private $batched_jobs = [];

    /**
     * @param Database $database
     * @param int $connection_id
     */
    public function __construct(Database $database, $connection_id)
    {
        $this->database = $database;
        $this->connection_id = $connection_id;
    }

    public function __destruct()
    {
        $this->database->releaseAdvisoryLocks($this->connection_id);
    }

    /**
     * @param string $category
     * @param string $name
     * @return bool
     */
    public function requestAdvisoryLock($category, $name)
    {
        return $this->database->requestAdvisoryLock($this->connection_id, $category, $name);
    }

    /**
     * @return Generator
     */
    public function getJobsToRunGenerator()
    {
        $buffered_jobs = $this->sortBufferedJobs(
            $this->filterFutureJobs($this->database->getAll('buffered_jobs'))
        );
        $active_mutexes = $this->determineActiveMutexes($this->database->getAll('queued_jobs'));

        foreach ($buffered_jobs as $buffered_job) {
            if (isset($active_mutexes[$buffered_job['mutex_id']])) {
                continue;
            }

            $active_mutexes[$buffered_job['mutex_id']] = $buffered_job;

            if (isset($this->batched_jobs[$buffered_job['buffered_job_id']])) {
                continue;
            }

            yield $buffered_job;
        }
    }

    public function beginBatch()
    {
        $this->in_batch = true;
    }

    /**
     * @param array $job
     * @return array
     */
    public function markJobAsQueued(array $job)
    {
        $this->batched_jobs[$job['buffered_job_id']] = $job;

        if (!$this->in_batch) {
            $this->publishBatch();
        }

        return ['buffered_job_id' => $job['buffered_job_id']];
    }

    public function publishBatch()
    {
        $this->in_batch = false;

        foreach ($this->batched_jobs as $job) {
            $job['queued_job_id'] = uniqid();
            $this->database->delete('buffered_jobs', $job['buffered_job_id']);
            $this->database->insert('queued_jobs', $job['buffered_job_id'], $job);
        }

        $this->batched_jobs = [];
    }

    private function filterFutureJobs(array $buffered_jobs)
    {
        $filtered_jobs = [];
        foreach ($buffered_jobs as $buffered_job) {
            if ($buffered_job['run_after'] <= date('c')) {
                $filtered_jobs[] = $buffered_job;
            }
        }

        return $filtered_jobs;
    }

    /**
     * @param array $buffered_jobs
     * @return array
     */
    private function sortBufferedJobs(array $buffered_jobs)
    {
        $compare_jobs = function ($field, array $a, array $b) {
            if ($a[$field] == $b[$field]) {
                return 0;
            }

            return ($a[$field] < $b[$field] ? -1 : 1);
        };
        uasort($buffered_jobs, function ($a, $b) use ($compare_jobs) {
            if ($comparison = $compare_jobs('job_rank', $a, $b)) {
                return $comparison;
            }

            return $compare_jobs('buffered_job_id', $a, $b);
        });

        return $buffered_jobs;
    }

    /**
     * @param array $queued_jobs
     * @return array
     */
    private function determineActiveMutexes(array $queued_jobs)
    {
        $active_mutexes = [];
        foreach ($queued_jobs as $queued_job) {
            $active_mutexes[$queued_job['mutex_id']] = true;
        }

        return $active_mutexes;
    }
}
