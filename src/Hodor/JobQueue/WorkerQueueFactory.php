<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;

class WorkerQueueFactory
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
     * @var DequeuerInterface
     */
    private $dequeuer;

    /**
     * @var array
     */
    private $worker_queues = [];

    /**
     * @param Producer $producer
     * @param Consumer $consumer
     * @param DequeuerInterface $dequeuer
     */
    public function __construct(Producer $producer, Consumer $consumer, DequeuerInterface $dequeuer)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
        $this->dequeuer = $dequeuer;
    }

    /**
     * @param  string $queue_name
     * @return WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->worker_queues[$queue_name])) {
            return $this->worker_queues[$queue_name];
        }

        $this->worker_queues[$queue_name] = new WorkerQueue(
            $this->producer->getQueue("worker-{$queue_name}"),
            $this->consumer->getQueue("worker-{$queue_name}"),
            $this->dequeuer
        );

        return $this->worker_queues[$queue_name];
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
}
