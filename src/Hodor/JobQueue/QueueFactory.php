<?php

namespace Hodor\JobQueue;

use Hodor\Config;
use Hodor\WorkerQueue;
use Hodor\MessageQueue\QueueFactory as MqFactory;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;

class QueueFactory
{
    /**
     * @var array
     */
    private $worker_queues = [];

    /**
     * @var \Hodor\MessageQueue\QueueFactory
     */
    private $mq_factory;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string $queue_name [description]
     * @return \Hodor\WorkerQueue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->worker_queues[$queue_name])) {
            return $this->worker_queues[$queue_name];
        }

        $this->worker_queues[$queue_name] = new WorkerQueue(
            $this->getMessageQueue($queue_name)
        );

        return $this->worker_queues[$queue_name];
    }

    /**
     * @param  string $queue_name
     * @return \Hodor\MessageQueue\Queue
     */
    private function getMessageQueue($queue_name)
    {
        return $this->getMessageQueueFactory()->getWorkerQueue($queue_name);
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
