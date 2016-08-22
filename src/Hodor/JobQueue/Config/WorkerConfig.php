<?php

namespace Hodor\JobQueue\Config;

use Exception;

class WorkerConfig
{
    /**
     * @var array
     */
    private $worker_commands = [
        'superqueuer' => 'superqueuer.php',
        'bufferer'    => 'buffer-worker.php',
        'worker'      => 'job-worker.php',
    ];

    /**
     * @var QueueConfig
     */
    private $queue_config;

    /**
     * @var array
     */
    private $worker_configs;

    /**
     * @param QueueConfig $queue_config
     */
    public function __construct(QueueConfig $queue_config)
    {
        $this->queue_config = $queue_config;
    }

    /**
     * @param string $worker_type
     * @param string $worker_name
     * @return true
     * @throws Exception
     */
    public function hasWorkerConfig($worker_type, $worker_name)
    {
        return $this->queue_config->hasWorkerConfig($worker_type, $worker_name);
    }

    /**
     * @return array
     */
    public function getWorkerConfigs()
    {
        if ($this->worker_configs) {
            return $this->worker_configs;
        }

        $this->worker_configs = [];
        foreach ($this->queue_config->getQueueNames() as $queue_name) {
            $queue_config = $this->queue_config->getWorkerConfig($queue_name);

            $key_name = "{$queue_config['queue_type']}-{$queue_config['key_name']}";
            $this->worker_configs[$key_name] = [
                'queue_type'    => $queue_config['queue_type'],
                'key_name'      => $queue_config['key_name'],
                'process_count' => $queue_config['process_count'],
                'command'       => $this->getBinFilePath($this->worker_commands[$queue_config['queue_type']]),
            ];
        }

        return $this->worker_configs;
    }

    /**
     * @param $bin_file
     * @return string
     */
    private function getBinFilePath($bin_file)
    {
        return __DIR__ . '/../../../../bin/' . $bin_file;
    }
}
