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
            'name' => $name,
            'params' => $params,
        ]);
    }

    /**
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     */
    public function runNext()
    {
        $this->queue->consume(function ($message) {
            var_dump($message->getContent());
            $message->acknowledge();
            exit(0);
        });
    }
}
