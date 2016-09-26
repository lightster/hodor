<?php

namespace Hodor\MessageQueue\Adapter;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getAdapterFactoryConfig();

    /**
     * @param string $queue_name
     * @return array
     */
    public function getQueueConfig($queue_name);
}
