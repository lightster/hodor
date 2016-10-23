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

class TestingQueueProvisioner
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

        $this->adapter_factory = new Factory($config, $this->message_bank_factory);
    }

    /**
     * @param string $queue_name
     * @return MessageBank
     */
    public function getMessageBank($queue_name)
    {
        return $this->message_bank_factory->getMessageBank($queue_name);
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
        return $this->getConsumer()->getQueue($queue_name);
    }

    /**
     * @param string $queue_name
     * @return ProducerQueue
     */
    public function getProducerQueue($queue_name)
    {
        return $this->getProducer()->getQueue($queue_name);
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
            $this->getProducer(),
            $this->getConsumer(),
            new Dequeuer($this->getDatabase())
        );

        return $this->worker_queue_factory;
    }

    /**
     * @param string $queue_name
     * @return WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        return $this->getWorkerQueueFactory()->getWorkerQueue($queue_name);
    }

    /**
     * @return Consumer
     */
    private function getConsumer()
    {
        if ($this->consumer) {
            return $this->consumer;
        }

        $this->consumer = new Consumer($this->adapter_factory);

        return $this->consumer;
    }

    /**
     * @return Producer
     */
    private function getProducer()
    {
        if ($this->producer) {
            return $this->producer;
        }

        $this->producer = new Producer($this->adapter_factory);

        return $this->producer;
    }
}
