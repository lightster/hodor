<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\BufferWorkerInterface as Database;
use Hodor\JobQueue\JobOptions\Validator as JobOptionsValidator;
use Hodor\MessageQueue\ConsumerQueue;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\ProducerQueue;

class BufferQueue
{
    /**
     * @var ProducerQueue
     */
    private $producer_q;

    /**
     * @var ConsumerQueue
     */
    private $consumer_q;

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
     * @param ProducerQueue $producer_q
     * @param ConsumerQueue $consumer_q
     * @param Database $database
     * @param Config $config
     */
    public function __construct(
        ProducerQueue $producer_q,
        ConsumerQueue $consumer_q,
        Database $database,
        Config $config
    ) {
        $this->producer_q = $producer_q;
        $this->consumer_q = $consumer_q;
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

        $this->producer_q->push([
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
        $this->consumer_q->consume(function (IncomingMessage $message) {
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
