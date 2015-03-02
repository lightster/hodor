<?php

namespace Hodor;

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
        $worker_queues = $this->getOption('worker_queues');
        if (!isset($worker_queues[$queue_name])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in 'worker_queues' config."
            );
        }

        return array_merge(
            $this->getOption('queue_defaults', [
                'host'         => null,
                'port'         => 5432,
                'username'     => null,
                'password'     => null,
                'queue_prefix' => 'hodor-'
            ]),
            $this->getOption('worker_queue_defaults', []),
            $worker_queues[$queue_name]
        );
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
