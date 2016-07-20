<?php

namespace Hodor\JobQueue;

use Hodor\Database\AdapterFactory as DbAdapterFactory;
use Hodor\Database\AdapterInterface as DbAdapterInterface;
use Hodor\JobQueue\JobOptions\Validator as JobOptionsValidator;
use Hodor\MessageQueue\Queue as MessageQueue;
use Hodor\MessageQueue\QueueFactory as MqFactory;

class QueueManager
{
    /**
     * @param Config
     */
    private $config;

    /**
     * @var array
     */
    private $buffer_queues = [];

    /**
     * @var array
     */
    private $worker_queues = [];

    /**
     * @var QueueManager
     */
    private $mq_factory;

    /**
     * @var JobOptionsValidator
     */
    private $job_options_validator;

    /**
     * @var DbAdapterInterface
     */
    private $database;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Superqueue
     */
    public function getSuperqueue()
    {
        if (isset($this->superqueue)) {
            return $this->superqueue;
        }

        $this->superqueue = new Superqueue($this);

        return $this->superqueue;
    }

    /**
     * @param  string $queue_name
     * @return BufferQueue
     */
    public function getBufferQueue($queue_name)
    {
        if (isset($this->buffer_queues[$queue_name])) {
            return $this->buffer_queues[$queue_name];
        }

        $this->buffer_queues[$queue_name] = new BufferQueue(
            $this->getMessageQueue("bufferer-{$queue_name}"),
            $this
        );

        return $this->buffer_queues[$queue_name];
    }

    /**
     * @param  string $name
     * @param  array  $params
     * @param  array  $options
     * @return BufferQueue
     */
    public function getBufferQueueForJob($name, array $params, array $options)
    {
        $queue_name = call_user_func(
            $this->config->getBufferQueueNameFactory(),
            $name,
            $params,
            $options
        );

        return $this->getBufferQueue($queue_name);
    }

    /**
     * @param  string $queue_name [description]
     * @return WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->worker_queues[$queue_name])) {
            return $this->worker_queues[$queue_name];
        }

        $this->worker_queues[$queue_name] = new WorkerQueue(
            $this->getMessageQueue("worker-{$queue_name}"),
            $this
        );

        return $this->worker_queues[$queue_name];
    }

    /**
     * @param  string $name
     * @param  array  $params
     * @param  array  $options
     * @return WorkerQueue
     */
    public function getWorkerQueueNameForJob($name, array $params, array $options)
    {
        return call_user_func(
            $this->config->getWorkerQueueNameFactory(),
            $name,
            $params,
            $options
        );
    }

    /**
     * @return JobOptionsValidator
     */
    public function getJobOptionsValidator()
    {
        if ($this->job_options_validator) {
            return $this->job_options_validator;
        }

        $this->job_options_validator = new JobOptionsValidator($this->config);

        return $this->job_options_validator;
    }

    public function beginBatch()
    {
        $this->getMessageQueueFactory()->beginBatch();
    }

    public function publishBatch()
    {
        $this->getMessageQueueFactory()->publishBatch();
    }

    public function discardBatch()
    {
        $this->getMessageQueueFactory()->discardBatch();
    }

    /**
     * @return DbAdapterInterface
     */
    public function getDatabase()
    {
        if ($this->database) {
            return $this->database;
        }

        $config = $this->config->getSuperqueueConfig();
        $db_adapter_factory = new DbAdapterFactory($config['database']);

        $this->database = $db_adapter_factory->getAdapter($config['database']['type']);

        return $this->database;
    }

    /**
     * @param  string $queue_name
     * @return MessageQueue
     */
    private function getMessageQueue($queue_name)
    {
        return $this->getMessageQueueFactory()->getQueue($queue_name);
    }

    /**
     * @return QueueManager
     */
    private function getMessageQueueFactory()
    {
        if ($this->mq_factory) {
            return $this->mq_factory;
        }

        $this->mq_factory = new MqFactory($this->config);

        return $this->mq_factory;
    }
}
