<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\MessageQueue\Adapter\FactoryInterface as MqFactoryInterface;
use Hodor\MessageQueue\AdapterFactory;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;

class QueueManager
{
    /**
     * @param Config
     */
    private $config;

    /**
     * @var WorkerQueueFactory
     */
    private $worker_queue_factory;

    /**
     * @var BufferQueueFactory
     */
    private $buffer_queue_factory;

    /**
     * @var MqFactoryInterface
     */
    private $mq_adapter_factory;

    /**
     * @var FactoryInterface
     */
    private $database;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Superqueue
     */
    public function getSuperqueue()
    {
        if (isset($this->superqueue)) {
            return $this->superqueue;
        }

        $this->superqueue = new Superqueue(
            $this->getDatabase()->getSuperqueuer(),
            $this->getWorkerQueueFactory()
        );

        return $this->superqueue;
    }

    /**
     * @param  string $queue_name
     * @return BufferQueue
     */
    public function getBufferQueue($queue_name)
    {
        return $this->getBufferQueueFactory()->getQueue($queue_name);
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

        return $this->getBufferQueue($queue_name);
    }

    /**
     * @return WorkerQueueFactory
     */
    public function getWorkerQueueFactory()
    {
        if ($this->worker_queue_factory) {
            return $this->worker_queue_factory;
        }

        $this->worker_queue_factory = new WorkerQueueFactory(
            new Producer($this->getMessageQueueAdapterFactory()),
            new Consumer($this->getMessageQueueAdapterFactory()),
            $this->getDatabase()->getDequeuer()
        );

        return $this->worker_queue_factory;
    }

    /**
     * @return BufferQueueFactory
     */
    public function getBufferQueueFactory()
    {
        if ($this->buffer_queue_factory) {
            return $this->buffer_queue_factory;
        }

        $this->buffer_queue_factory = new BufferQueueFactory(
            new Producer($this->getMessageQueueAdapterFactory()),
            new Consumer($this->getMessageQueueAdapterFactory()),
            $this->getDatabase()->getBufferWorker(),
            $this->config
        );

        return $this->buffer_queue_factory;
    }

    public function beginBatch()
    {
        $this->getBufferQueueFactory()->beginBatch();
    }

    public function publishBatch()
    {
        $this->getBufferQueueFactory()->publishBatch();
    }

    public function discardBatch()
    {
        $this->getBufferQueueFactory()->discardBatch();
    }

    /**
     * @return FactoryInterface
     */
    private function getDatabase()
    {
        if ($this->database) {
            return $this->database;
        }

        $config = $this->config->getDatabaseConfig();
        $db_adapter_factory = new DbAdapterFactory();

        $this->database = $db_adapter_factory->getAdapter($config);

        return $this->database;
    }

    /**
     * @return MqFactoryInterface
     */
    private function getMessageQueueAdapterFactory()
    {
        if ($this->mq_adapter_factory) {
            return $this->mq_adapter_factory;
        }

        $mq_adapter_factory = new AdapterFactory();
        $this->mq_adapter_factory = $mq_adapter_factory->getAdapter(
            $this->config->getMessageQueueConfig()
        );

        return $this->mq_adapter_factory;
    }
}
