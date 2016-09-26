<?php

namespace Hodor\JobQueue;

use Exception;
use Hodor\JobQueue\Config\JobQueueConfig;
use Hodor\JobQueue\Config\MessageQueueConfig;
use Hodor\JobQueue\Config\QueueConfig;
use Hodor\JobQueue\Config\WorkerConfig;

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
     * @var WorkerConfig
     */
    private $worker_config;

    /**
     * @var QueueConfig
     */
    private $queue_config;

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
     * @return JobQueueConfig
     */
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

        $this->message_queue_config = new MessageQueueConfig(
            $this->getQueueConfig(),
            $this->getOption('message_queue_factory', [])
        );

        return $this->message_queue_config;
    }

    /**
     * @return WorkerConfig
     */
    public function getWorkerConfig()
    {
        if ($this->worker_config) {
            return $this->worker_config;
        }

        $this->worker_config = new WorkerConfig($this->getQueueConfig());

        return $this->worker_config;
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
    public function getDaemonConfig()
    {
        return $this->getOption('daemon');
    }

    /**
     * @return QueueConfig
     */
    private function getQueueConfig()
    {
        if ($this->queue_config) {
            return $this->queue_config;
        }

        $this->queue_config = new QueueConfig([
            'queue_defaults'        => $this->getOption('queue_defaults', []),
            'worker_queues'         => $this->getOption('worker_queues', []),
            'worker_queue_defaults' => $this->getOption('worker_queue_defaults', []),
            'buffer_queues'         => $this->getOption('buffer_queues', []),
            'buffer_queue_defaults' => $this->getOption('buffer_queue_defaults', []),
        ]);

        return $this->queue_config;
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
