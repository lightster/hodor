<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\Database\Exception\BufferedJobNotFoundException;

class Dequeuer implements DequeuerInterface
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
     * @param array $meta
     */
    public function markJobAsSuccessful(array $meta)
    {
        $this->markJobAsFinished($meta);
    }

    /**
     * @param array $meta
     */
    public function markJobAsFailed(array $meta)
    {
        $this->markJobAsFinished($meta);
    }

    /**
     * @param array $meta
     * @throws BufferedJobNotFoundException
     */
    private function markJobAsFinished(array $meta)
    {
        if (!$this->database->has('queued_jobs', $meta['buffered_job_id'])) {
            throw new BufferedJobNotFoundException("", $meta['buffered_job_id'], $meta);
        }

        $this->database->delete('queued_jobs', $meta['buffered_job_id']);
    }
}
