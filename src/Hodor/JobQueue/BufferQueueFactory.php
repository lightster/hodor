<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\BufferWorkerInterface as Database;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;

/**
 * @method BufferQueue getQueue(string $queue_name)
 */
class BufferQueueFactory extends AbstractQueueFactory
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Producer $producer
     * @param Consumer $consumer
     * @param Database $database
     * @param Config $config
     */
    public function __construct(Producer $producer, Consumer $consumer, Database $database, Config $config)
    {
        parent::__construct($producer, $consumer);

        $this->database = $database;
        $this->config = $config;
    }

    /**
     * @param  string $name
     * @param  array  $params
     * @param  array  $options
     * @return BufferQueue
     */
    public function getBufferQueueForJob($name, array $params, array $options)
    {
        $queue_name = $this->config->getJobQueueConfig()->getBufferQueueName(
            $name,
            $params,
            $options
        );

        return $this->getQueue($queue_name);
    }

    /**
     * @param string $queue_name
     * @return BufferQueue
     */
    protected function generateQueue($queue_name)
    {
        return new BufferQueue(
            $this->getProducer()->getQueue("bufferer-{$queue_name}"),
            $this->getConsumer()->getQueue("bufferer-{$queue_name}"),
            $this->database,
            $this->config
        );
    }
}
