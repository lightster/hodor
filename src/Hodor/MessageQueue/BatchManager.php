<?php

namespace Hodor\MessageQueue;

use Exception;

class BatchManager
{
    /**
     * @var QueueFactory
     */
    private $queue_factory;

    /**
     * @var bool
     */
    private $is_in_batch = false;

    /**
     * @var array
     */
    private $batch_queues = [];

    /**
     * @var array
     */
    private $batches = [];

    /**
     * @param QueueFactory $queue_factory
     */
    public function __construct(QueueFactory $queue_factory)
    {
        $this->queue_factory = $queue_factory;
    }

    /**
     * @param string $queue_name
     * @return BatchQueue
     */
    public function getQueue($queue_name)
    {
        if (isset($this->batch_queues[$queue_name])) {
            return $this->batch_queues[$queue_name];
        }

        $this->checkQueueName($queue_name);

        $this->batch_queues[$queue_name] = new BatchQueue(function ($message) use ($queue_name) {
            $this->push($queue_name, $message);
        });

        return $this->batch_queues[$queue_name];
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
            $this->queue_factory->getQueue($queue_name)->publishMessageBatch($batch);
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
            $this->batches[$queue_name][] = $message;
            return;
        }

        $this->queue_factory->getQueue($queue_name)->publishMessage($message);
    }

    /**
     * @param string $queue_name
     */
    private function checkQueueName($queue_name)
    {
        $this->queue_factory->getQueue($queue_name);
    }
}
