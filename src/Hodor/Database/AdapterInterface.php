<?php

namespace Hodor\Database;

use Generator;

interface AdapterInterface
{
    /**
     * @param string $queue_name
     * @param array $job
     */
    public function bufferJob($queue_name, array $job);

    /**
     * @return Generator
     */
    public function getJobsToRunGenerator();

    /**
     * @param array $job
     * @return array
     */
    public function markJobAsQueued(array $job);

    /**
     * @param array $meta
     */
    public function markJobAsSuccessful(array $meta);

    /**
     * @param array $meta
     */
    public function markJobAsFailed(array $meta);

    /**
     * @return PgsqlPhpmigAdapter
     */
    public function getPhpmigAdapter();

    public function beginTransaction();
    public function commitTransaction();

    /**
     * @param $category
     * @param $name
     * @return bool
     */
    public function requestAdvisoryLock($category, $name);
}
