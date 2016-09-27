<?php

namespace Hodor\JobQueue\Config;

use Exception;
use Hodor\MessageQueue\Adapter\ConfigInterface;

class MessageQueueConfig implements ConfigInterface
{
    /**
     * @var string
     */
    private $adapter_factory_config;

    /**
     * @var QueueConfig
     */
    private $queue_config;

    /**
     * @param QueueConfig $queue_config
     * @param array $adapter_factory_config
     */
    public function __construct(QueueConfig $queue_config, array $adapter_factory_config = [])
    {
        $this->adapter_factory_config = array_merge(
            ['type' => 'amqp'],
            $adapter_factory_config
        );
        $this->queue_config = $queue_config;
    }

    /**
     * @return array
     */
    public function getAdapterFactoryConfig()
    {
        return $this->adapter_factory_config;
    }

    /**
     * @param  string $queue_name
     * @return array
     * @throws Exception
     */
    public function getQueueConfig($queue_name)
    {
        return $this->queue_config->getMessageQueueConfig($queue_name);
    }
}
