<?php

namespace Hodor\MessageQueue;

use Hodor\MessageQueue\Adapter\Amqp\Factory;
use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;

class QueueFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var Queue[]
     */
    private $queues = [];

    /**
     * @var bool
     */
    private $is_in_batch = false;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $queue_name
     * @return Queue
     */
    public function getQueue($queue_name)
    {
        if (isset($this->queues[$queue_name])) {
            return $this->queues[$queue_name];
        }

        $this->queues[$queue_name] = new Queue(
            $this->getAdapterFactory()->getConsumer($queue_name),
            $this->getAdapterFactory()->getProducer($queue_name)
        );
        if ($this->is_in_batch) {
            $this->queues[$queue_name]->beginBatch();
        }

        return $this->queues[$queue_name];
    }

    public function beginBatch()
    {
        array_walk($this->queues, function (Queue $queue) {
            $queue->beginBatch();
        });
        $this->is_in_batch = true;
    }

    public function publishBatch()
    {
        array_walk($this->queues, function (Queue $queue) {
            $queue->publishBatch();
        });
        $this->is_in_batch = false;
    }

    public function discardBatch()
    {
        array_walk($this->queues, function (Queue $queue) {
            $queue->discardBatch();
        });
        $this->is_in_batch = false;
    }

    /**
     * @return Factory
     */
    private function getAdapterFactory()
    {
        if ($this->adapter_factory) {
            return $this->adapter_factory;
        }

        $this->adapter_factory = new Factory($this->config);

        return $this->adapter_factory;
    }
}
