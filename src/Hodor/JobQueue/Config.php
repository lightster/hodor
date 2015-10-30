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
     * @param array $string
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
     * @param  string $queue_name
     * @return array
     */
    public function getWorkerQueueConfig($queue_name)
    {
        $config = $this->getQueueConfig(
            $queue_name,
            'worker_queues',
            'worker_queue_defaults'
        );
        $config['key_name'] = $queue_name;
        $config['fetch_count'] = 1;
        $config['queue_type'] = 'worker';
        $config['process_count'] = $config['workers_per_server'];

        return $config;
    }

    /**
     * @param  string $queue_name
     * @return array
     */
    public function getBufferQueueConfig($queue_name)
    {
        $config = $this->getQueueConfig(
            $queue_name,
            'buffer_queues',
            'buffer_queue_defaults'
        );
        $config = array_merge(
            [
                'fetch_count' => 1,
            ],
            $config
        );
        $config['key_name'] = $queue_name;
        $config['queue_type'] = 'bufferer';
        $config['process_count'] = $config['bufferers_per_server'];

        return $config;
    }

    /**
     * @return callable
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
     * @param  string $queues_option
     * @param  string $defaults_option
     * @return array
     */
    private function getQueueConfig($queue_name, $queues_option, $defaults_option)
    {
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
            $this->getOption($defaults_option, [])
        );
        $config = array_merge(
            $config,
            [
                'queue_name' => "{$config['queue_prefix']}{$queue_name}",
            ],
            $queues[$queue_name]
        );

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
