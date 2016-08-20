<?php

namespace Hodor\JobQueue;

use Exception;
use Hodor\JobQueue\Config\JobQueueConfig;
use Hodor\JobQueue\Config\MessageQueueConfig;

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
     * @var JobQueueConfig
     */
    private $job_queue_config;

    /**
     * @var MessageQueueConfig
     */
    private $message_queue_config;

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

    public function getJobQueueConfig()
    {
        if ($this->job_queue_config) {
            return $this->job_queue_config;
        }

        $this->job_queue_config = new JobQueueConfig([
            'job_runner'                => $this->getOption('job_runner'),
            'worker_queue_name_factory' => $this->getOption('worker_queue_name_factory'),
            'buffer_queue_name_factory' => $this->getOption('buffer_queue_name_factory'),
        ]);

        return $this->job_queue_config;
    }

    /**
     * @return MessageQueueConfig
     */
    public function getMessageQueueConfig()
    {
        if ($this->message_queue_config) {
            return $this->message_queue_config;
        }

        $this->message_queue_config = new MessageQueueConfig([
            'adapter_factory'       => $this->getOption('adapter_factory'),
            'queue_defaults'        => $this->getOption('queue_defaults', []),
            'worker_queues'         => $this->getOption('worker_queues', []),
            'worker_queue_defaults' => $this->getOption('worker_queue_defaults', []),
            'buffer_queues'         => $this->getOption('buffer_queues', []),
            'buffer_queue_defaults' => $this->getOption('buffer_queue_defaults', []),
        ]);

        return $this->message_queue_config;
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
     * @param  string $queue_name
     * @return array
     */
    public function getWorkerQueueConfig($queue_name)
    {
        return $this->getMessageQueueConfig()->getQueueConfig("worker-{$queue_name}");
    }

    /**
     * @param  string $queue_name
     * @return array
     */
    public function getBufferQueueConfig($queue_name)
    {
        return $this->getMessageQueueConfig()->getQueueConfig("bufferer-{$queue_name}");
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
