<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use OutOfBoundsException;

class Config implements ConfigInterface
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var array
     */
    private $queues = [];

    /**
     * @param FactoryInterface $adapter_factory
     */
    public function __construct(FactoryInterface $adapter_factory)
    {
        $this->adapter_factory = $adapter_factory;
    }

    /**
     * @return FactoryInterface
     */
    public function getAdapterFactory()
    {
        return $this->adapter_factory;
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
        if (empty($this->queues[$queue_key])) {
            throw new OutOfBoundsException("Queue with '{$queue_key}' not found.");
        }

        return $this->queues[$queue_key];
    }
}
