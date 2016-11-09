<?php

namespace Hodor\JobQueue;

use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;

abstract class AbstractQueueFactory
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var array
     */
    private $queues = [];

    /**
     * @param Producer $producer
     * @param Consumer $consumer
     */
    public function __construct(Producer $producer, Consumer $consumer)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
    }

    /**
     * @param string $queue_name
     * @return mixed
     */
    abstract protected function generateQueue($queue_name);

    /**
     * @param  string $queue_name
     * @return WorkerQueue
     */
    public function getQueue($queue_name)
    {
        if (isset($this->queues[$queue_name])) {
            return $this->queues[$queue_name];
        }

        $this->queues[$queue_name] = $this->generateQueue($queue_name);

        return $this->queues[$queue_name];
    }

    public function beginBatch()
    {
        $this->producer->beginBatch();
    }

    public function publishBatch()
    {
        $this->producer->publishBatch();
    }

    public function discardBatch()
    {
        $this->producer->discardBatch();
    }

    /**
     * @return Producer
     */
    protected function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return Consumer
     */
    protected function getConsumer()
    {
        return $this->consumer;
    }
}
