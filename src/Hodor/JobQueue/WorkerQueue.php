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
     * @param array $meta meta-information about the job
     */
    public function push($name, array $params = [], array $meta = [])
    {
        $this->queue->push([
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
        $this->queue->consume(function ($message) use ($job_runner) {
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
