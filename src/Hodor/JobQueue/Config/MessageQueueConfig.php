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
     * @var array
     */
    private $config;

    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var array
     */
    private $queue_configs;

    public function __construct($config)
    {
        $this->config = array_merge(
            [
                'adapter_factory'       => null,
                'queue_defaults'        => [],
                'worker_queues'         => [],
                'worker_queue_defaults' => [],
                'workers_per_server'    => null,
                'buffer_queues'         => [],
                'buffer_queue_defaults' => [],
                'bufferers_per_server'  => null,
            ],
            $config
        );
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
        if (!$this->queue_configs) {
            $this->initQueues();
        }

        if (!isset($this->queue_configs[$queue_name])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in queues config."
            );
        }

        return $this->queue_configs[$queue_name];
    }

    /**
     * @return FactoryInterface
     */
    private function generateAdapterFactory()
    {
        if ('testing' === $this->config['adapter_factory']) {
            return new TestingFactory($this);
        }

        return new AmqpFactory($this);
    }

    /**
     * @return array
     */
    private function initQueues()
    {
        $this->initQueuesForType(
            'worker',
            'worker_queues',
            'worker_queue_defaults',
            'workers_per_server'
        );
        $this->initQueuesForType(
            'bufferer',
            'buffer_queues',
            'buffer_queue_defaults',
            'bufferers_per_server'
        );
    }

    /**
     * @param string $type
     * @param string $queue_key
     * @param string $defaults_key
     * @param string $process_count_key
     */
    private function initQueuesForType($type, $queue_key, $defaults_key, $process_count_key)
    {
        $queues = $this->config[$queue_key];

        $defaults = array_merge(
            [
                'host'         => null,
                'port'         => 5672,
                'username'     => null,
                'password'     => null,
                'queue_prefix' => 'hodor-',
            ],
            $this->config['queue_defaults'],
            $this->config[$defaults_key]
        );

        foreach ($queues as $queue_name => $queue) {
            $queue_config = array_merge($defaults, $queue);
            $queue_config['queue_name'] = "{$queue_config['queue_prefix']}{$queue_name}";
            $queue_config['key_name'] = $queue_name;
            $queue_config['fetch_count'] = 1;
            $queue_config['queue_type'] = $type;
            $queue_config['process_count'] = $queue_config[$process_count_key];

            $this->queue_configs["{$type}-{$queue_name}"] = $queue_config;
        }
    }
}
