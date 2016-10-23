<?php

namespace Hodor\JobQueue\TestUtil;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Dequeuer;
use Hodor\JobQueue\WorkerQueue;
use Hodor\JobQueue\WorkerQueueFactory;
use Hodor\MessageQueue\Adapter\Testing\Config as TestingConfig;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use Hodor\MessageQueue\Adapter\Testing\MessageBank;
use Hodor\MessageQueue\Adapter\Testing\MessageBankFactory;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\ConsumerQueue;
use Hodor\MessageQueue\Producer;
use Hodor\MessageQueue\ProducerQueue;

class TestingWorkerQueueFactory
{
    /**
     * @var MessageBankFactory
     */
    private $message_bank_factory;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var WorkerQueueFactory
     */
    private $worker_queue_factory;

    public function __construct(TestingConfig $config)
    {
        $this->message_bank_factory = new MessageBankFactory();
        $this->database = new Database();

        $adapter_factory = new Factory($config, $this->message_bank_factory);
        $dequeuer = new Dequeuer($this->database);

        $this->consumer = new Consumer($adapter_factory);
        $this->producer = new Producer($adapter_factory);
        $this->worker_queue_factory = new WorkerQueueFactory(
            $this->producer,
            $this->consumer,
            $dequeuer
        );
    }

    /**
     * @param string $queue_name
     * @return MessageBank
     */
    public function getMessageBank($queue_name)
    {
        return $this->message_bank_factory->getMessageBank("worker-{$queue_name}");
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $queue_name
     * @return ConsumerQueue
     */
    public function getConsumerQueue($queue_name)
    {
        return $this->consumer->getQueue("worker-{$queue_name}");
    }

    /**
     * @param string $queue_name
     * @return ProducerQueue
     */
    public function getProducerQueue($queue_name)
    {
        return $this->producer->getQueue("worker-{$queue_name}");
    }

    /**
     * @return WorkerQueueFactory
     */
    public function getWorkerQueueFactory()
    {
        return $this->worker_queue_factory;
    }

    /**
     * @param string $queue_name
     * @return WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        return $this->worker_queue_factory->getWorkerQueue($queue_name);
    }
}
