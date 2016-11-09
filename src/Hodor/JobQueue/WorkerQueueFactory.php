<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\MessageQueue\Consumer;
use Hodor\MessageQueue\Producer;

/**
 * @method WorkerQueue getQueue(string $queue_name)
 */
class WorkerQueueFactory extends AbstractQueueFactory
{
    /**
     * @var DequeuerInterface
     */
    private $dequeuer;

    /**
     * @param Producer $producer
     * @param Consumer $consumer
     * @param DequeuerInterface $dequeuer
     */
    public function __construct(Producer $producer, Consumer $consumer, DequeuerInterface $dequeuer)
    {
        parent::__construct($producer, $consumer);

        $this->dequeuer = $dequeuer;
    }

    /**
     * @param string $queue_name
     * @return WorkerQueue
     */
    protected function generateQueue($queue_name)
    {
        return new WorkerQueue(
            $this->getProducer()->getQueue("worker-{$queue_name}"),
            $this->getConsumer()->getQueue("worker-{$queue_name}"),
            $this->dequeuer
        );
    }
}
