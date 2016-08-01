<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Lstr\YoPdo\YoPdo;

class Dequeuer implements DequeuerInterface
{
    /**
     * @var Connection
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

    /**
     * @param string $status
     * @param array $meta
     * @throws BufferedJobNotFoundException
     */
    private function markJobAsFinished($status, array $meta)
    {
        $sql = <<<SQL
SELECT *
FROM queued_jobs
WHERE buffered_job_id = :buffered_job_id
SQL;

        $this->getYoPdo()->transaction()->begin('dequeue-job');

        $job = $this->getYoPdo()->query(
            $sql,
            ['buffered_job_id' => $meta['buffered_job_id']]
        )->fetch();

        if (!$job) {
            throw new BufferedJobNotFoundException(
                "Could not mark buffered_job_id={$meta['buffered_job_id']} as finished. Job not found.",
                $meta['buffered_job_id'],
                $meta
            );
        }

        $job['started_running_at'] = $meta['started_running_at'];
        $job['ran_from'] = gethostname();
        $job['dequeued_from'] = gethostname();
        unset($job['queued_job_id']);

        $this->getYoPdo()->delete(
            'queued_jobs',
            'buffered_job_id = :buffered_job_id',
            ['buffered_job_id' => $job['buffered_job_id']]
        );
        $this->getYoPdo()->insert("{$status}_jobs", $job);

        $this->getYoPdo()->transaction()->accept('dequeue-job');
    }

    /**
     * @return YoPdo
     */
    private function getYoPdo()
    {
        return $this->connection->getYoPdo();
    }
}
