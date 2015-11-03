<?php

namespace Hodor\JobQueue;

use Hodor\MessageQueue\Queue;

class WorkerQueue
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
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $meta meta-information about the job
     */
    public function push($name, array $params = [], array $meta = [])
    {
        $this->message_queue->push([
            'name'   => $name,
            'params' => $params,
            'meta'   => $meta,
        ]);
    }

    /**
     * @param  callable $job_runner
     */
    public function runNext(callable $job_runner)
    {
        $this->message_queue->consume(function ($message) use ($job_runner) {
            register_shutdown_function(function ($message) {
                if (error_get_last()) {
                    $message->acknowledge();
                }
            }, $message);

            $content = $message->getContent();
            $name = $content['name'];
            $params = $content['params'];
            call_user_func($job_runner, $name, $params);

            $message->acknowledge();

            exit(0);
        });
    }
}
