<?php

namespace Hodor\Database;

interface AdapterInterface
{
    public function createJob($job);
    public function markJobAsStarted($job);
    public function markJobAsCompleted($job);
    public function markJobAsFailed($job);
    public function getPhpmigAdapter();
}
