<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Message;

interface ConfigInterface
{
    /**
     * @param string $queue_name
     * @return array
     */
    public function getQueueConfig($queue_name);
}
