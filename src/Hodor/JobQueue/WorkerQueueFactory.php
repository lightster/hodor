<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\MessageQueue\QueueFactory;

class WorkerQueueFactory
{
    /**
     * @var QueueFactory
     */
    private $mq_factory;

    /**
     * @var DequeuerInterface
     */
    private $dequeuer;

    /**
     * @var array
     */
    private $worker_queues = [];

    /**
     * @param QueueFactory $mq_factory
     * @param DequeuerInterface $dequeuer
     */
    public function __construct(QueueFactory $mq_factory, DequeuerInterface $dequeuer)
    {
        $this->mq_factory = $mq_factory;
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
            $this->mq_factory->getQueue("worker-{$queue_name}"),
            $this->dequeuer
        );

        return $this->worker_queues[$queue_name];
    }
}
