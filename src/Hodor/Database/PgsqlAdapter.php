<?php

namespace Hodor\Database;

use Hodor\Database\Phpmig\PgsqlPhpmigAdapter;
use Hodor\Database\Driver\YoPdoDriver;

use Exception;

class PgsqlAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var YoPdoDriver
     */
    private $driver;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $queue_name
     * @param array $job
     */
    public function bufferJob($queue_name, array $job)
    {
        $row = [
            'queue_name'    => $queue_name,
            'job_name'      => $job['name'],
            'job_params'    => json_encode($job['params']),
            'buffered_at'   => $job['meta']['buffered_at'],
            'buffered_from' => $job['meta']['buffered_from'],
            'inserted_from' => gethostname(),
        ];

        if (isset($job['run_after'])) {
            $row['run_after'] = $job['run_after'];
        }

        $this->getDriver()->insert('buffered_jobs', $row);
    }

    /**
     * @return callable
     */
    public function getJobsToRunGenerator()
    {
        return function () {
            $sql = <<<SQL
SELECT *
FROM buffered_jobs
WHERE run_after <= NOW()
ORDER BY
    job_rank,
    buffered_at
SQL;

            $row_generator = $this->getDriver()->selectRowGenerator($sql);
            foreach ($row_generator() as $job) {
                $job['job_params'] = json_decode($job['job_params'], true);
                yield $job;
            }
        };
    }

    /**
     * @param array $job
     * @return array
     */
    public function markJobAsQueued(array $job)
    {
        $this->getDriver()->delete(
            'buffered_jobs',
            ['buffered_job_id' => $job['buffered_job_id']]
        );
        $job['job_params'] = json_encode($job['job_params']);
        $job['superqueued_from'] = gethostname();
        $this->getDriver()->insert(
            'queued_jobs',
            $job
        );

        return ['buffered_job_id' => $job['buffered_job_id']];
    }

    /**
     * @param array $meta
     */
    public function markJobAsSuccessful(array $meta)
    {
        return $this->markJobAsFinished('successful', $meta);
    }

    /**
     * @param array $meta
     */
    public function markJobAsFailed(array $meta)
    {
        return $this->markJobAsFinished('failed', $meta);
    }

    public function getPhpmigAdapter()
    {
        return new PgsqlPhpmigAdapter($this->getDriver());
    }

    public function beginTransaction()
    {
    }

    public function commitTransaction()
    {
    }

    public function rollbackTransaction()
    {
    }

    /**
     * @param string $sql
     * @return void
     */
    public function queryMultiple($sql)
    {
        return $this->getDriver()->queryMultiple($sql);
    }

    /**
     * @param $status
     * @param array $meta
     */
    private function markJobAsFinished($status, array $meta)
    {
        $sql = <<<SQL
SELECT *
FROM queued_jobs
WHERE buffered_job_id = :buffered_job_id
SQL;

        $job = $this->getDriver()->selectOne(
            $sql,
            ['buffered_job_id' => $meta['buffered_job_id']]
        );
        $job['started_running_at'] = $meta['started_running_at'];
        $job['ran_from'] = gethostname();
        $job['dequeued_from'] = gethostname();
        unset($job['queued_job_id']);

        $this->getDriver()->delete(
            'queued_jobs',
            ['buffered_job_id' => $job['buffered_job_id']]
        );
        $this->getDriver()->insert("{$status}_jobs", $job);
    }

    /**
     * @return YoPdoDriver
     */
    private function getDriver()
    {
        if ($this->driver) {
            return $this->driver;
        }

        $this->driver = new YoPdoDriver($this->config);

        return $this->driver;
    }
}
