<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\BufferWorkerInterface as Database;
use Hodor\JobQueue\JobOptions\Validator as JobOptionsValidator;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\Queue;

class BufferQueue
{
    /**
     * @var Queue
     */
    private $message_queue;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JobOptionsValidator
     */
    private $job_options_validator;

    /**
     * @param Queue $message_queue
     * @param Database $database
     * @param Config $config
     */
    public function __construct(Queue $message_queue, Database $database, Config $config)
    {
        $this->message_queue = $message_queue;
        $this->database = $database;
        $this->config = $config;
    }

    /**
     * @param string $name the name of the job to run
     * @param array $params the parameters to pass to the job
     * @param array $options the options to use when running the job
     */
    public function push($name, array $params = [], array $options = [])
    {
        $this->getJobOptionsValidator()->validateJobOptions($options);

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
        $this->message_queue->consume(function (IncomingMessage $message) {
            $content = $message->getContent();

            $queue_name = $this->config->getJobQueueConfig()->getWorkerQueueName(
                $content['name'],
                $content['params'],
                $content['options']
            );

            $this->database->bufferJob($queue_name, [
                'name'    => $content['name'],
                'params'  => $content['params'],
                'options' => $content['options'],
                'meta'    => $content['meta'],
            ]);

            $message->acknowledge();
        });
    }

    /**
     * @return JobOptionsValidator
     */
    private function getJobOptionsValidator()
    {
        if ($this->job_options_validator) {
            return $this->job_options_validator;
        }

        $this->job_options_validator = new JobOptionsValidator($this->config->getWorkerConfig());

        return $this->job_options_validator;
    }
}
