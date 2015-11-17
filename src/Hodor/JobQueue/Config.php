<?php

namespace Hodor\JobQueue;

use Exception;

class Config
{
    /**
     * @var string
     */
    private $config_path;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $queue_types = [
        'worker' => [
            'queue_key'         => 'worker_queues',
            'defaults_key'      => 'worker_queue_defaults',
            'process_count_key' => 'workers_per_server',
        ],
        'bufferer' => [
            'queue_key'         => 'buffer_queues',
            'defaults_key'      => 'buffer_queue_defaults',
            'process_count_key' => 'bufferers_per_server',
        ],
    ];

    /**
     * @param array config_path
     * @param array $config
     */
    public function __construct($config_path, array $config)
    {
        $this->config_path = $config_path;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->config_path;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getDatabaseConfig()
    {
        $superqueuer_config = $this->getOption('superqueue');
        if (!isset($superqueuer_config['database'])) {
            throw new Exception(
                "The 'database' config was not found in the 'superqueue' config."
            );
        }

        return $superqueuer_config['database'];
    }

    /**
     * @return array
     */
    public function getSuperqueueConfig()
    {
        return $this->getOption('superqueue');
    }

    /**
     * @param  string $queue_name
     * @return array
     */
    public function getWorkerQueueConfig($queue_name)
    {
        return $this->getQueueConfig($queue_name, 'worker');
    }

    /**
     * @param  string $queue_name
     * @return array
     */
    public function getBufferQueueConfig($queue_name)
    {
        return $this->getQueueConfig($queue_name, 'bufferer');
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getJobRunnerFactory()
    {
        $job_runner = $this->getOption('job_runner');

        if (empty($job_runner)) {
            throw new Exception("The 'job_runner' config parameter is required.");
        } elseif (!is_callable($job_runner)) {
            throw new Exception(
                "The provided 'job_runner' config value is not a callable."
            );
        }

        return $job_runner;
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getWorkerQueueNameFactory()
    {
        $worker_queue_name_factory = $this->getOption('worker_queue_name_factory');

        if (empty($worker_queue_name_factory)) {
            $worker_queue_name_factory = function ($name, $params, $options) {
                if (empty($options['queue_name'])) {
                    throw new Exception(
                        "Job option 'queue_name' is required when using the "
                        . "default queue name factory."
                    );
                }
                return $options['queue_name'];
            };
        } elseif (!is_callable($worker_queue_name_factory)) {
            throw new Exception(
                "The provided 'worker_queue_name_factory' config value is not a callable."
            );
        }

        return $worker_queue_name_factory;
    }

    /**
     * @return callable
     * @throws Exception
     */
    public function getBufferQueueNameFactory()
    {
        $buffer_queue_name_factory = $this->getOption('buffer_queue_name_factory');

        if (empty($buffer_queue_name_factory)) {
            $buffer_queue_name_factory = function ($name, $params, $options) {
                return 'default';
            };
        } elseif (!is_callable($buffer_queue_name_factory)) {
            throw new Exception(
                "The provided 'buffer_queue_name_factory' config value is not a callable."
            );
        }

        return $buffer_queue_name_factory;
    }

    /**
     * @return array
     */
    public function getDaemonConfig()
    {
        return $this->getOption('daemon');
    }

    /**
     * @return array
     */
    public function getWorkerQueueNames()
    {
        return array_keys($this->getOption('worker_queues'));
    }

    /**
     * @return array
     */
    public function getBufferQueueNames()
    {
        return array_keys($this->getOption('buffer_queues'));
    }

    /**
     * @param  string $queue_name
     * @param  string $queue_type
     * @return array
     * @throws Exception
     */
    private function getQueueConfig($queue_name, $queue_type)
    {
        $queue_type_keys = $this->queue_types[$queue_type];

        $queues_option = $queue_type_keys['queue_key'];

        $queues = $this->getOption($queues_option);
        if (!isset($queues[$queue_name])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in {$queues_option} config."
            );
        }

        $config = array_merge(
            $this->getOption('queue_defaults', [
                'host'         => null,
                'port'         => 5672,
                'username'     => null,
                'password'     => null,
                'queue_prefix' => 'hodor-'
            ]),
            $this->getOption($queue_type_keys['defaults_key'], [])
        );
        $config = array_merge(
            $config,
            [
                'queue_name' => "{$config['queue_prefix']}{$queue_name}",
            ],
            $queues[$queue_name]
        );
        $config['key_name'] = $queue_name;
        $config['fetch_count'] = 1;
        $config['queue_type'] = $queue_type;
        $config['process_count'] = $config[$queue_type_keys['process_count_key']];

        return $config;
    }

    /**
     * @param  string $option
     * @param  mixed $default
     * @return mixed
     */
    private function getOption($option, $default = null)
    {
        if (!array_key_exists($option, $this->config)) {
            $this->config[$option] = $default;
        }

        return $this->config[$option];
    }
}
