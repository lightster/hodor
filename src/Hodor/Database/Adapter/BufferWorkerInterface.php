<?php

namespace Hodor\Database\Adapter;

interface BufferWorkerInterface
{
    /**
     * @param string $queue_name
     * @param array $job
     */
    public function bufferJob($queue_name, array $job);
}
