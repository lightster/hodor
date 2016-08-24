<?php

namespace Hodor\JobQueue\Config;

use Exception;

class QueueConfig
{
    /**
     * @var array
     */
    private $worker_types = [
        'superqueuer' => [],
        'bufferer'    => [
            'defaults_key'      => 'buffer_queue_defaults',
            'queues_key'        => 'buffer_queues',
            'process_count_key' => 'bufferers_per_server',
        ],
        'worker'      => [
            'defaults_key'      => 'worker_queue_defaults',
            'queues_key'        => 'worker_queues',
            'process_count_key' => 'workers_per_server',
        ],
    ];

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $worker_type_defaults = [];

    /**
     * @var array
     */
    private $queue_names;

    /**
     * @var array
     */
    private $queue_configs = [
        'superqueuer-default' => [
            'worker_type'   => 'superqueuer',
            'worker_name'   => 'default',
            'process_count' => 1,
        ],
    ];

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
            'worker_type'   => $queue_config['worker_type'],
            'worker_name'   => $queue_config['worker_name'],
            'process_count' => $queue_config['process_count'],
        ];
    }

    /**
     * @param string $worker_type
     * @param string $worker_name
     * @return bool
     * @throws Exception
     */
    public function hasWorkerConfig($worker_type, $worker_name)
    {
        if (!array_key_exists($worker_type, $this->worker_types)) {
            throw new Exception("Worker config for unknown worker type '{$worker_type}' requested.");
        }

        $type_options = $this->worker_types[$worker_type];

        return array_key_exists($worker_name, $this->config[$type_options['queues_key']]);
    }

    /**
     * @return array
     */
    public function getQueueNames()
    {
        if ($this->queue_names) {
            return $this->queue_names;
        }

        $buffer_names = array_keys($this->config[$this->worker_types['bufferer']['queues_key']]);
        $worker_names = array_keys($this->config[$this->worker_types['worker']['queues_key']]);

        $queue_names = array_merge(
            ['superqueuer-default'],
            array_map(function ($buffer_name) {
                return "bufferer-{$buffer_name}";
            }, $buffer_names),
            array_map(function ($buffer_name) {
                return "worker-{$buffer_name}";
            }, $worker_names)
        );

        $this->queue_names = array_combine($queue_names, $queue_names);

        return $this->queue_names;
    }

    /**
     * @param  string $queue_name
     * @return array
     * @throws Exception
     */
    private function getQueueConfig($queue_name)
    {
        if (array_key_exists($queue_name, $this->queue_configs)) {
            return $this->queue_configs[$queue_name];
        }

        $name_parts = explode('-', $queue_name, 2);
        if (!isset($name_parts[1]) || !$this->hasWorkerConfig($name_parts[0], $name_parts[1])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in queues config."
            );
        }

        $this->initQueueConfig($name_parts[0], $name_parts[1]);

        return $this->queue_configs[$queue_name];
    }

    /**
     * @param string $worker_type
     * @param string $worker_name
     */
    private function initQueueConfig($worker_type, $worker_name)
    {
        $type_options = $this->worker_types[$worker_type];

        $queue_config = array_merge(
            $this->getQueueTypeDefaults($worker_type),
            $this->config[$type_options['queues_key']][$worker_name]
        );
        $queue_config['queue_name'] = "{$queue_config['queue_prefix']}{$worker_name}";
        $queue_config['worker_name'] = $worker_name;
        $queue_config['fetch_count'] = 1;
        $queue_config['worker_type'] = $worker_type;
        $queue_config['process_count'] = $queue_config[$type_options['process_count_key']];

        $this->queue_configs["{$worker_type}-{$worker_name}"] = $queue_config;
    }

    /**
     * @param string $worker_type
     * @return array
     */
    private function getQueueTypeDefaults($worker_type)
    {
        if (array_key_exists($worker_type, $this->worker_type_defaults)) {
            return $this->worker_type_defaults[$worker_type];
        }

        $this->worker_type_defaults[$worker_type] = array_merge(
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
            $this->config[$this->worker_types[$worker_type]['defaults_key']]
        );

        return $this->worker_type_defaults[$worker_type];
    }
}
