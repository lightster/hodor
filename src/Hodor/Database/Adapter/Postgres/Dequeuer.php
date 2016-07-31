<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Lstr\YoPdo\YoPdo;

class Dequeuer implements DequeuerInterface
{
    /**
     * @var YoPdo
     */
    private $yo_pdo;

    /**
     * @param YoPdo $yo_pdo
     */
    public function __construct(YoPdo $yo_pdo)
    {
        $this->yo_pdo = $yo_pdo;
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

        $this->yo_pdo->transaction()->begin('dequeue-job');

        $job = $this->yo_pdo->query(
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

        $this->yo_pdo->delete(
            'queued_jobs',
            'buffered_job_id = :buffered_job_id',
            ['buffered_job_id' => $job['buffered_job_id']]
        );
        $this->yo_pdo->insert("{$status}_jobs", $job);

        $this->yo_pdo->transaction()->accept('dequeue-job');
    }
}
