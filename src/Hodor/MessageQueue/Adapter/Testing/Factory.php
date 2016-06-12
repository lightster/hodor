<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;

class Factory implements FactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Consumer[]
     */
    private $consumers = [];

    /**
     * @var Producer[]
     */
    private $producers = [];

    /**
     * @var MessageBank[]
     */
    private $message_banks = [];

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

        $this->consumers[$queue_key] = new Consumer($this->getMessageBank($queue_key));

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

        $this->producers[$queue_key] = new Producer($this->getMessageBank($queue_key));

        return $this->producers[$queue_key];
    }

    /**
     * @param string $queue_key
     * @return MessageBank
     */
    private function getMessageBank($queue_key)
    {
        if (!empty($this->message_banks[$queue_key])) {
            return $this->message_banks[$queue_key];
        }

        $this->message_banks[$queue_key] = new MessageBank($this->config->getQueueConfig($queue_key));

        return $this->message_banks[$queue_key];
    }
}
