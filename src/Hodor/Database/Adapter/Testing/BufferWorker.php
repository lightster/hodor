<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\BufferWorkerInterface;

class BufferWorker implements BufferWorkerInterface
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $queue_name
     * @param array $job
     */
    public function bufferJob($queue_name, array $job)
    {
        $job_id = $this->database->allocateId();

        $job = array_replace_recursive([
            'name' => "job-name-{$job_id}",
            'params' => [],
            'meta' => [
                'buffered_at'   => date('c'),
                'buffered_from' => gethostname(),
            ],
            'options' => [
                'run_after' => date('c'),
                'job_rank'  => 5,
                'mutex_id'  => "hodor-{$job_id}"
            ],
        ], $job);

        $row = [
            'buffered_job_id' => $job_id,
            'queue_name'      => $queue_name,
            'job_name'        => $job['name'],
            'job_params'      => json_encode($job['params'], JSON_FORCE_OBJECT),
            'buffered_at'     => $job['meta']['buffered_at'],
            'buffered_from'   => $job['meta']['buffered_from'],
            'inserted_from'   => gethostname(),
            'run_after'       => $job['options']['run_after'],
            'job_rank'        => $job['options']['job_rank'],
            'mutex_id'        => $job['options']['mutex_id'],
        ];

        $this->database->insert('buffered_jobs', $job_id, $row);
    }
}
