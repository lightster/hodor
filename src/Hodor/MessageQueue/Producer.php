<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\FactoryInterface;

class Producer
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var bool
     */
    private $is_in_batch = false;

    /**
     * @var array
     */
    private $producer_queues = [];

    /**
     * @var array
     */
    private $batches = [];

    /**
     * @param FactoryInterface $adapter_factory
     */
    public function __construct(FactoryInterface $adapter_factory)
    {
        $this->adapter_factory = $adapter_factory;
    }

    /**
     * @param string $queue_name
     * @return ProducerQueue
     */
    public function getQueue($queue_name)
    {
        if (isset($this->producer_queues[$queue_name])) {
            return $this->producer_queues[$queue_name];
        }

        $this->checkQueueName($queue_name);

        $this->producer_queues[$queue_name] = new ProducerQueue(function ($message) use ($queue_name) {
            $this->push($queue_name, $message);
        });

        return $this->producer_queues[$queue_name];
    }

    public function beginBatch()
    {
        if ($this->is_in_batch) {
            throw new Exception("The queue is already in transaction.");
        }

        $this->is_in_batch = true;
    }

    public function publishBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        foreach ($this->batches as $queue_name => $batch) {
            $this->adapter_factory->getProducer($queue_name)->produceMessageBatch($batch);
        }

        $this->is_in_batch = false;
        $this->batches = [];
    }

    public function discardBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->is_in_batch = false;
        $this->batches = [];
    }

    /**
     * @param string $queue_name
     * @param mixed $message
     */
    private function push($queue_name, $message)
    {
        if ($this->is_in_batch) {
            $this->batches[$queue_name][] = new OutgoingMessage($message);
            return;
        }

        $this->adapter_factory->getProducer($queue_name)->produceMessage(new OutgoingMessage($message));
    }

    /**
     * @param string $queue_name
     */
    private function checkQueueName($queue_name)
    {
        $this->adapter_factory->getProducer($queue_name);
    }
}
