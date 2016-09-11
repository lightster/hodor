<?php

namespace Hodor\JobQueue;

use Closure;
use DateTime;
use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\Queue;

class WorkerQueue
{
    /**
     * @var Queue
     */
    private $message_queue;

    /**
     * @var DequeuerInterface
     */
    private $database;

    /**
     * @param Queue $message_queue
     * @param DequeuerInterface $database
     */
    public function __construct(Queue $message_queue, DequeuerInterface $database)
    {
        $this->message_queue = $message_queue;
        $this->database = $database;
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

            $mark_job_as_failed_if_not_successful = $this->getFailureCallback();

            register_shutdown_function($mark_job_as_failed_if_not_successful, $message, $start_time);

            $content = $message->getContent();
            $name = $content['name'];
            $params = $content['params'];
            $meta = $content['meta'];

            $title = implode(" ", $_SERVER['argv']) . " ({$meta['buffered_job_id']}:{$name})";
            cli_set_process_title($title);

            try {
                call_user_func($job_runner, $name, $params);

                $this->markJobAsSuccessful($message, $start_time);
            } finally {
                $mark_job_as_failed_if_not_successful($message, $start_time);
            }
        });
    }

    /**
     * @param IncomingMessage $message
     * @param DateTime $started_running_at
     */
    private function markJobAsSuccessful(IncomingMessage $message, DateTime $started_running_at)
    {
        $this->markJobAsFinished($message, $started_running_at, function ($meta) {
            $this->database->markJobAsSuccessful($meta);
        });
    }

    /**
     * @return Closure
     */
    private function getFailureCallback()
    {
        return function (IncomingMessage $message, DateTime $started_running_at) {
            if ($message->isAcked()) {
                return;
            }

            $this->markJobAsFinished($message, $started_running_at, function ($meta) {
                $this->database->markJobAsFailed($meta);
            });
        };
    }

    /**
     * @param IncomingMessage $message
     * @param DateTime $started_running_at
     * @param callable $mark_finished
     * @throws BufferedJobNotFoundException
     */
    private function markJobAsFinished(
        IncomingMessage $message,
        DateTime $started_running_at,
        callable $mark_finished
    ) {
        $content = $message->getContent();
        $meta = $content['meta'];
        $meta['started_running_at'] = $started_running_at->format('c');

        try {
            $mark_finished($meta);
            $message->acknowledge();
        } catch (BufferedJobNotFoundException $exception) {
            $message->acknowledge();
            throw $exception;
        }
    }
}
