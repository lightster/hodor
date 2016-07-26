<?php

namespace Hodor\JobQueue;

use DateTime;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\Queue;

class WorkerQueue
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
        $this->message_queue->consume(function (IncomingMessage $message) use ($job_runner) {
            $start_time = new DateTime;

            register_shutdown_function(
                function (IncomingMessage $message, DateTime $start_time, QueueManager $queue_manager) {
                    if (error_get_last()) {
                        $queue_manager->getSuperqueue()->markJobAsFailed(
                            $message,
                            $start_time
                        );
                        exit(1);
                    }
                },
                $message,
                $start_time,
                $this->queue_manager
            );

            $content = $message->getContent();
            $name = $content['name'];
            $params = $content['params'];
            $meta = $content['meta'];

            $title = implode(" ", $_SERVER['argv']) . " ({$meta['buffered_job_id']}:{$name})";
            cli_set_process_title($title);

            call_user_func($job_runner, $name, $params);

            $superqueue = $this->queue_manager->getSuperqueue();
            $superqueue->markJobAsSuccessful($message, $start_time);
        });
    }
}
