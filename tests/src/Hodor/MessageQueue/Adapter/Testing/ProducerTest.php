<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Adapter\ProducerTest as BaseProducerTest;
use Hodor\MessageQueue\IncomingMessage;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Producer
 */
class ProducerTest extends BaseProducerTest
{
    /**
     * @var MessageBank[]
     */
    private $message_banks = [];

    /**
     * @var Config
     */
    private $config;


    /**
     * @param array $config_overrides
     * @return ProducerInterface
     */
    protected function getTestProducer(array $config_overrides = [])
    {
        $message_bank = $this->getMessageBank('fast_jobs', $this->getTestConfig($config_overrides));
        $test_producer = new Producer($message_bank);

        return $test_producer;
    }

    /**
     * @return string
     */
    protected function consumeMessage()
    {
        $message_bank = $this->getMessageBank('fast_jobs', $this->getTestConfig());
        $consumer = new Consumer($message_bank);

        $consumer->consumeMessage(function (IncomingMessage $message) use (&$return) {
            $return = $message->getContent();
            $message->acknowledge();
        });

        return $return;
    }

    /**
     * @param string $queue_key
     * @param Config $config
     * @return MessageBank
     */
    private function getMessageBank($queue_key, Config $config)
    {
        if (!empty($this->message_banks[$queue_key])) {
            return $this->message_banks[$queue_key];
        }

        $this->message_banks[$queue_key] = new MessageBank($config->getQueueConfig($queue_key));

        return $this->message_banks[$queue_key];
    }

    /**
     * @param array $config_overrides
     * @return Config
     */
    private function getTestConfig(array $config_overrides = [])
    {
        if ($this->config) {
            return $this->config;
        }

        $config_provider = new ConfigProvider($this);
        $test_queues = $this->getTestQueues($config_provider);
        $this->config = $config_provider->getConfigAdapter($test_queues, $config_overrides);

        return $this->config;
    }

    /**
     * @param ConfigProvider $config_provider
     * @return array
     */
    private function getTestQueues(ConfigProvider $config_provider)
    {
        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
