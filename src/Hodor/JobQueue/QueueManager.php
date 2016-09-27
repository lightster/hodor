<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\MessageQueue\AdapterFactory;
use Hodor\MessageQueue\Queue as MessageQueue;
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
     * @var array
     */
    private $worker_queues = [];

    /**
     * @var MqFactory
     */
    private $mq_factory;

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

        $this->superqueue = new Superqueue($this->getDatabase()->getSuperqueuer(), $this);

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
            $this->getMessageQueue("bufferer-{$queue_name}"),
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
     * @param  string $queue_name [description]
     * @return WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->worker_queues[$queue_name])) {
            return $this->worker_queues[$queue_name];
        }

        $this->worker_queues[$queue_name] = new WorkerQueue(
            $this->getMessageQueue("worker-{$queue_name}"),
            $this->getDatabase()->getDequeuer()
        );

        return $this->worker_queues[$queue_name];
    }

    public function beginBatch()
    {
        $this->getMessageQueueFactory()->beginBatch();
    }

    public function publishBatch()
    {
        $this->getMessageQueueFactory()->publishBatch();
    }

    public function discardBatch()
    {
        $this->getMessageQueueFactory()->discardBatch();
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
     * @param  string $queue_name
     * @return MessageQueue
     */
    private function getMessageQueue($queue_name)
    {
        return $this->getMessageQueueFactory()->getQueue($queue_name);
    }

    /**
     * @return MqFactory
     */
    private function getMessageQueueFactory()
    {
        if ($this->mq_factory) {
            return $this->mq_factory;
        }

        $mq_adapter_factory = new AdapterFactory();
        $mq_adapter = $mq_adapter_factory->getAdapter($this->config->getMessageQueueConfig());
        $this->mq_factory = new MqFactory($mq_adapter);

        return $this->mq_factory;
    }
}
