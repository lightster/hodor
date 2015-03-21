<?php

namespace Hodor\JobQueue;

use Hodor\MessageQueue\Queue;

class WorkerQueue
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     */
    public function push($name, array $params = [])
    {
        $this->queue->push([
            'name'   => $name,
            'params' => $params,
        ]);
    }

    /**
     * @param  callable $job_runner
     */
    public function runNext(callable $job_runner)
    {
        $this->queue->consume(function ($message) use ($job_runner) {
            $content = $message->getContent();
            $name = $content['name'];
            $params = $content['params'];
            try {
                call_user_func($job_runner, $name, $params);
            } finally {
                $message->acknowledge();
            }

            exit(0);
        });
    }
}
