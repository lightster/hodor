<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\BufferWorkerInterface;
use Lstr\YoPdo\YoPdo;

class BufferWorker implements BufferWorkerInterface
{
    /**
     * @var YoPdo
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
            'job_params'    => json_encode($job['params'], JSON_FORCE_OBJECT),
            'buffered_at'   => $job['meta']['buffered_at'],
            'buffered_from' => $job['meta']['buffered_from'],
            'inserted_from' => gethostname(),
        ];

        if (isset($job['options']['run_after'])) {
            $row['run_after'] = $job['options']['run_after'];
        }
        if (isset($job['options']['job_rank'])) {
            $row['job_rank'] = $job['options']['job_rank'];
        }
        if (isset($job['options']['mutex_id'])) {
            $row['mutex_id'] = $job['options']['mutex_id'];
        }

        $this->getYoPdo()->transaction()->begin('buffer-job');
        $this->getYoPdo()->insert('buffered_jobs', $row);
        $this->getYoPdo()->transaction()->accept('buffer-job');
    }

    /**
     * @return YoPdo
     */
    private function getYoPdo()
    {
        return $this->connection->getYoPdo();
    }
}
