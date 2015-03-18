<?php

namespace Hodor\JobQueue;

use Hodor\MessageQueue\Queue;

class BufferQueue
{
    /**
     * @var Queue
     */
    private $message_queue;

    /**
     * @var QueueFactory
     */
    private $queue_factory;

    /**
     * @param Queue $message_queue
     * @param QueueFactory $queue_factory
     */
    public function __construct(Queue $message_queue, QueueFactory $queue_factory)
    {
        $this->message_queue = $message_queue;
        $this->queue_factory = $queue_factory;
    }

    /**
     * @param string $job_name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public function push($name, array $params = [], array $options = [])
    {
        $this->message_queue->push([
            'name'    => $name,
            'params'  => $params,
            'options' => $options,
        ]);
    }

    public function processBuffer()
    {
        $this->message_queue->consume(function ($message) {
            $content = $message->getContent();
            $name    = $content['name'];
            $params  = $content['params'];
            $options = $content['options'];

            $worker_queue = $this->queue_factory->getWorkerQueueForJob(
                $name,
                $params,
                $options
            );

            $worker_queue->push(
                $name,
                $params
            );

            $message->acknowledge();

            exit(0);
        });
    }
}
