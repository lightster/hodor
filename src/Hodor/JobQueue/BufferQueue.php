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
     * @var QueueManager
     */
    private $queue_manager;

    /**
     * @param Queue $message_queue
     * @param QueueManager $queue_manager
     */
    public function __construct(Queue $message_queue, QueueManager $queue_manager)
    {
        $this->message_queue = $message_queue;
        $this->queue_manager = $queue_manager;
    }

    /**
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public function push($name, array $params = [], array $options = [])
    {
        $this->queue_manager->getJobOptionsValidator()->validateJobOptions($options);

        if (!empty($options['run_after'])) {
            $options['run_after'] = $options['run_after']->format('c');
        }

        $this->message_queue->push([
            'name'    => $name,
            'params'  => $params,
            'options' => $options,
            'meta'    => [
                'buffered_at'   => gmdate('c'),
                'buffered_from' => gethostname(),
            ],
        ]);
    }

    public function processBuffer()
    {
        $this->message_queue->consume(function ($message) {
            $superqueue = $this->queue_manager->getSuperqueue();
            $superqueue->bufferJobFromBufferQueueToDatabase($message);

            exit(0);
        });
    }
}
