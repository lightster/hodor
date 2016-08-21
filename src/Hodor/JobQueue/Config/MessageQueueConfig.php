<?php

namespace Hodor\JobQueue\Config;

use Exception;
use Hodor\MessageQueue\Adapter\Amqp\Factory as AmqpFactory;
use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\Testing\Factory as TestingFactory;

class MessageQueueConfig implements ConfigInterface
{
    /**
     * @var string
     */
    private $adapter_factory_type;

    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var QueueConfig
     */
    private $queue_config;

    /**
     * @param QueueConfig $queue_config
     * @param string $adapter_factory_type
     */
    public function __construct(QueueConfig $queue_config, $adapter_factory_type = null)
    {
        $this->adapter_factory_type = $adapter_factory_type ?: 'amqp';
        $this->queue_config = $queue_config;
    }

    /**
     * @return FactoryInterface
     */
    public function getAdapterFactory()
    {
        if ($this->adapter_factory) {
            return $this->adapter_factory;
        }

        $this->adapter_factory = $this->generateAdapterFactory();

        return $this->adapter_factory;
    }

    /**
     * @param  string $queue_name
     * @return array
     * @throws Exception
     */
    public function getQueueConfig($queue_name)
    {
        return array_merge(
            $this->queue_config->getMessageQueueConfig($queue_name),
            $this->queue_config->getWorkerConfig($queue_name)
        );
    }

    /**
     * @return FactoryInterface
     */
    private function generateAdapterFactory()
    {
        if ('testing' === $this->adapter_factory_type) {
            return new TestingFactory($this);
        }

        return new AmqpFactory($this);
    }
}
