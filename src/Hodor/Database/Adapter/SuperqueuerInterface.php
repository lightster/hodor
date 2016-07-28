<?php

namespace Hodor\Database\Adapter;

use Generator;

interface SuperqueuerInterface
{
    /**
     * @param string $category
     * @param string $name
     * @return bool
     */
    public function requestAdvisoryLock($category, $name);

    /**
     * @return Generator
     */
    public function getJobsToRunGenerator();

    public function beginBatch();

    /**
     * @param array $job
     * @return array
     */
    public function markJobAsQueued(array $job);

    public function publishBatch();
}
