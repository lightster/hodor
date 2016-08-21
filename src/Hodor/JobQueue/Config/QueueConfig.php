<?php

namespace Hodor\JobQueue\Config;

use Exception;

class QueueConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $queue_configs;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge(
            [
                'queue_defaults'        => [],
                'worker_queues'         => [],
                'worker_queue_defaults' => [],
                'buffer_queues'         => [],
                'buffer_queue_defaults' => [],
            ],
            $config
        );
    }

    /**
     * @param string $queue_name
     * @return array
     * @throws Exception
     */
    public function getMessageQueueConfig($queue_name)
    {
        $queue_config = $this->getQueueConfig($queue_name);

        return [
            'host'                     => $queue_config['host'],
            'port'                     => $queue_config['port'],
            'username'                 => $queue_config['username'],
            'password'                 => $queue_config['password'],
            'queue_name'               => $queue_config['queue_name'],
            'fetch_count'              => $queue_config['fetch_count'],
        ];
    }

    /**
     * @param string $queue_name
     * @return array
     * @throws Exception
     */
    public function getWorkerConfig($queue_name)
    {
        $queue_config = $this->getQueueConfig($queue_name);

        return [
            'queue_type'    => $queue_config['queue_type'],
            'key_name'      => $queue_config['key_name'],
            'process_count' => $queue_config['process_count'],
        ];
    }

    /**
     * @return array
     */
    public function getQueueNames()
    {
        $queue_names = array_keys($this->getQueueConfigs());

        return array_combine($queue_names, $queue_names);
    }

    /**
     * @param  string $queue_name
     * @return array
     * @throws Exception
     */
    private function getQueueConfig($queue_name)
    {
        $queue_configs = $this->getQueueConfigs();

        if (!isset($queue_configs[$queue_name])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in queues config."
            );
        }

        return $queue_configs[$queue_name];
    }

    /**
     * @return array
     */
    private function getQueueConfigs()
    {
        if ($this->queue_configs) {
            return $this->queue_configs;
        }

        $this->queue_configs = [];
        $this->queue_configs['superqueuer-default'] = [
            'queue_type'    => 'superqueuer',
            'key_name'   => 'default',
            'process_count' => 1,
        ];

        $this->initQueuesForType(
            'bufferer',
            'buffer_queues',
            'buffer_queue_defaults',
            'bufferers_per_server'
        );
        $this->initQueuesForType(
            'worker',
            'worker_queues',
            'worker_queue_defaults',
            'workers_per_server'
        );

        return $this->queue_configs;
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
                'host'                     => null,
                'port'                     => 5672,
                'username'                 => null,
                'password'                 => null,
                'connection_type'          => 'stream',
                'queue_prefix'             => 'hodor-',
                'max_messages_per_consume' => 1,
                'max_time_per_consume'     => 600,
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
