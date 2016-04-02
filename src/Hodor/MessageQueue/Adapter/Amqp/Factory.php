<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class Factory implements FactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ChannelFactory
     */
    private $channel_manager;

    /**
     * @var Consumer[]
     */
    private $consumers = [];

    /**
     * @var Producer[]
     */
    private $producers = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $queue_key
     * @return ConsumerInterface
     */
    public function getConsumer($queue_key)
    {
        if (array_key_exists($queue_key, $this->consumers)) {
            return $this->consumers[$queue_key];
        }

        $this->consumers[$queue_key] = new Consumer($queue_key, $this->getChannelFactory());

        return $this->consumers[$queue_key];
    }

    /**
     * @param string $queue_key
     * @return ProducerInterface
     */
    public function getProducer($queue_key)
    {
        if (array_key_exists($queue_key, $this->producers)) {
            return $this->producers[$queue_key];
        }

        $this->producers[$queue_key] = new Producer($queue_key, $this->getChannelFactory());

        return $this->producers[$queue_key];
    }

    /**
     * @return ChannelFactory
     */
    private function getChannelFactory()
    {
        if ($this->channel_manager) {
            return $this->channel_manager;
        }

        $this->channel_manager = new ChannelFactory($this->config);

        return $this->channel_manager;
    }
}
