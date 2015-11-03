<?php

namespace Hodor\Database;

interface AdapterInterface
{
    public function bufferJob($queue_name, array $job);

    public function getJobsToRunGenerator();

    public function markJobAsQueued(array $job);

    public function markJobAsSuccessful(array $meta);
    public function markJobAsFailed(array $meta);

    public function getPhpmigAdapter();

    public function beginTransaction();
    public function commitTransaction();
    public function rollbackTransaction();
}
