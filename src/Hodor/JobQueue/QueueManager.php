<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\MessageQueue\Adapter\FactoryInterface as MqFactoryInterface;
use Hodor\MessageQueue\AdapterFactory;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;
use Hodor\MessageQueue\QueueFactory as MqFactory;

class QueueManager
{
    /**
     * @param Config
     */
    private $config;

    /**
     * @var array
     */
    private $buffer_queues = [];

    /**
     * @var WorkerQueueFactory
     */
    private $worker_queue_factory;

    /**
     * @var MqFactory
     */
    private $mq_factory;

    /**
     * @var MqFactoryInterface
     */
    private $mq_adapter_factory;

    /**
     * @var Producer
     */
    private $mq_producer;

    /**
     * @var Consumer
     */
    private $mq_consumer;

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
        if (isset($this->buffer_queues[$queue_name])) {
            return $this->buffer_queues[$queue_name];
        }

        $this->buffer_queues[$queue_name] = new BufferQueue(
            $this->getMqProducer()->getQueue("bufferer-{$queue_name}"),
            $this->getMqConsumer()->getQueue("bufferer-{$queue_name}"),
            $this->getDatabase()->getBufferWorker(),
            $this->config
        );

        return $this->buffer_queues[$queue_name];
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
            $this->getMqProducer(),
            $this->getMqConsumer(),
            $this->getDatabase()->getDequeuer()
        );

        return $this->worker_queue_factory;
    }

    public function beginBatch()
    {
        $this->getMqProducer()->beginBatch();
    }

    public function publishBatch()
    {
        $this->getMqProducer()->publishBatch();
    }

    public function discardBatch()
    {
        $this->getMqProducer()->discardBatch();
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
     * @return Producer
     */
    private function getMqProducer()
    {
        if ($this->mq_producer) {
            return $this->mq_producer;
        }

        $this->mq_producer = new Producer($this->getMessageQueueAdapterFactory());

        return $this->mq_producer;
    }

    /**
     * @return Consumer
     */
    private function getMqConsumer()
    {
        if ($this->mq_consumer) {
            return $this->mq_consumer;
        }

        $this->mq_consumer = new Consumer($this->getMessageQueueAdapterFactory());

        return $this->mq_consumer;
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
