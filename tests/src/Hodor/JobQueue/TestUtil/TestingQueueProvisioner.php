<?php

namespace Hodor\JobQueue\TestUtil;

use Hodor\Database\Adapter\Testing\BufferWorker;
use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Dequeuer;
use Hodor\Database\Adapter\Testing\Superqueuer;
use Hodor\JobQueue\BufferQueue;
use Hodor\JobQueue\Config;
use Hodor\JobQueue\Superqueue;
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
     * @var TestingConfig
     */
    private $config;

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

    /**
     * @var BufferQueue[]
     */
    private $buffer_queues = [];

    /**
     * @var Superqueue
     */
    private $superqueue;

    public function __construct(TestingConfig $config)
    {
        $this->config = $config;
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
     * @param string $queue_name
     * @return BufferQueue
     */
    public function getBufferQueue($queue_name)
    {
        if (array_key_exists($queue_name, $this->buffer_queues)) {
            return $this->buffer_queues[$queue_name];
        }

        $this->buffer_queues[$queue_name] = new BufferQueue(
            $this->getProducerQueue("bufferer-{$queue_name}"),
            $this->getConsumerQueue("bufferer-{$queue_name}"),
            new BufferWorker($this->getDatabase()),
            new Config(__FILE__, [
                'adapter_factory' => 'testing',
                'worker_queues' => ['test-queue' => []],
            ])
        );

        return $this->buffer_queues[$queue_name];
    }

    /**
     * @return Superqueue
     */
    public function getSuperqueue()
    {
        if ($this->superqueue) {
            return $this->superqueue;
        }

        $this->superqueue = new Superqueue(
            new Superqueuer($this->getDatabase(), 1),
            $this->getWorkerQueueFactory()
        );

        return $this->superqueue;
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
