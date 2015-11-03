<?php

namespace Hodor\Database;

interface AdapterInterface
{
    public function bufferJob($queue_name, array $job);

    public function getJobsToRunGenerator();

    public function markJobAsQueued(array $job);

    public function markJobAsCompleted($job);
    public function markJobAsFailed($job);

    public function getPhpmigAdapter();

    public function beginTransaction();
    public function commitTransaction();
    public function rollbackTransaction();
}
