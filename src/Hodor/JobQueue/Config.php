<?php

namespace Hodor\JobQueue;

use Exception;
use Hodor\JobQueue\Config\JobQueueConfig;
use Hodor\MessageQueue\Adapter\Amqp\Factory as AmqpFactory;
use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\Testing\Factory as TestingFactory;

class Config implements ConfigInterface
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

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
     * @var JobQueueConfig
     */
    private $job_queue_config;

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
        return $this->getQueueConfig("worker-{$queue_name}");
    }

    /**
     * @param  string $queue_name
     * @return array
     */
    public function getBufferQueueConfig($queue_name)
    {
        return $this->getQueueConfig("bufferer-{$queue_name}");
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
     * @return array
     */
    public function getBufferQueueNames()
    {
        return array_keys($this->getOption('buffer_queues'));
    }

    /**
     * @param  string $fully_qualified_queue_name
     * @return array
     * @throws Exception
     */
    public function getQueueConfig($fully_qualified_queue_name)
    {
        list($queue_type, $queue_name) = explode('-', $fully_qualified_queue_name, 2);
        $queue_type_keys = $this->queue_types[$queue_type];

        $queues_option = $queue_type_keys['queue_key'];

        $queues = $this->getOption($queues_option);
        if (!isset($queues[$queue_name])) {
            throw new Exception(
                "Queue name '{$queue_name}' not found in {$queues_option} config."
            );
        }

        $config = array_merge(
            [
                'host'         => null,
                'port'         => 5672,
                'username'     => null,
                'password'     => null,
                'queue_prefix' => 'hodor-',
            ],
            $this->getOption('queue_defaults', []),
            $this->getOption($queue_type_keys['defaults_key'], []),
            $queues[$queue_name]
        );
        $config = array_merge(
            $config,
            ['queue_name' => "{$config['queue_prefix']}{$queue_name}"]
        );
        $config['key_name'] = $queue_name;
        $config['fetch_count'] = 1;
        $config['queue_type'] = $queue_type;
        $config['process_count'] = $config[$queue_type_keys['process_count_key']];

        return $config;
    }

    /**
     * @return FactoryInterface
     */
    private function generateAdapterFactory()
    {
        if ('testing' === $this->getOption('adapter_factory')) {
            return new TestingFactory($this);
        }

        return new AmqpFactory($this);
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
