<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use OutOfBoundsException;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $adapter_factory_config;

    /**
     * @var array
     */
    private $queues = [];

    /**
     * @param array $adapter_factory_config
     */
    public function __construct(array $adapter_factory_config)
    {
        $this->adapter_factory_config = $adapter_factory_config;
    }

    /**
     * @return array
     */
    public function getAdapterFactoryConfig()
    {
        return $this->adapter_factory_config;
    }

    /**
     * @param string $queue_key
     * @param array  $config
     */
    public function addQueueConfig($queue_key, array $config)
    {
        $this->queues[$queue_key] = $config;
    }

    /**
     * @param string $queue_key
     * @return array
     */
    public function getQueueConfig($queue_key)
    {
        if (!array_key_exists($queue_key, $this->queues)) {
            throw new OutOfBoundsException("Queue with '{$queue_key}' not found.");
        }

        return $this->queues[$queue_key];
    }
}
