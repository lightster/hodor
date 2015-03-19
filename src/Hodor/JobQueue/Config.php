<?php

namespace Hodor\JobQueue;

use Exception;

class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        if (!isset($this->config['database'])) {
            throw new Exception("Required config option 'database' not provided.");
        }
    }

    /**
     * @return array
     */
    public function getDatabaseConfig()
    {
        return $this->getOption('database');
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
        $queue_name_factory = $this->getOption('queue_name_factory');

        if (empty($queue_name_factory)) {
            $queue_name_factory = function ($name, $params, $options) {
                if (empty($options['queue_name'])) {
                    throw new Exception(
                        "Job option 'queue_name' is required when using the "
                        . "default queue name factory."
                    );
                }
                return $options['queue_name'];
            };
        } elseif (!is_callable($queue_name_factory)) {
            throw new Exception(
                "The provided 'queue_name_factory' config value is not a callable."
            );
        }

        return $queue_name_factory;
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
        if (null === $this->config) {
            $this->processConfig();
        }

        if (!array_key_exists($option, $this->config)) {
            $this->config[$option] = $default;
        }

        return $this->config[$option];
    }
}
