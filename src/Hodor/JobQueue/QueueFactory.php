<?php

namespace Hodor\JobQueue;

use Hodor\JobQueue\Config;
use Hodor\MessageQueue\QueueFactory as MqFactory;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;

class QueueFactory
{
    /**
     * @param \Hodor\JobQueue\Config
     */
    private $config;

    /**
     * @var array
     */
    private $worker_queues = [];

    /**
     * @var \Hodor\MessageQueue\QueueFactory
     */
    private $mq_factory;

    /**
     * @param \Hodor\JobQueue\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string $queue_name [description]
     * @return \Hodor\JobQueue\WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->worker_queues[$queue_name])) {
            return $this->worker_queues[$queue_name];
        }

        $queue_config = $this->config->getWorkerQueueConfig($queue_name);
        $this->worker_queues[$queue_name] = new WorkerQueue(
            $this->getMessageQueue($queue_config)
        );

        return $this->worker_queues[$queue_name];
    }

    /**
     * @param  string $name
     * @param  array  $params
     * @param  array  $options
     * @return \Hodor\JobQueue\WorkerQueue
     */
    public function getWorkerQueueForJob($name, array $params, array $options)
    {
        $queue_name = call_user_func(
            $this->config->getQueueNameFactory(),
            $name,
            $params,
            $options
        );

        return $this->getWorkerQueue($queue_name);
    }

    /**
     * @param  array  $queue_config
     * @return \Hodor\MessageQueue\Queue
     */
    private function getMessageQueue(array $queue_config)
    {
        return $this->getMessageQueueFactory()->getQueue($queue_config);
    }

    /**
     * @return \Hodor\MessageQueue\QueueFactory
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
