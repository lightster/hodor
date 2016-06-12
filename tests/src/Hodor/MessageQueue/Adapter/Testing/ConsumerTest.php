<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ConsumerTest as BaseConsumerTest;
use Hodor\MessageQueue\OutgoingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Consumer
 */
class ConsumerTest extends BaseConsumerTest
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
     * @return ConsumerInterface
     */
    protected function getTestConsumer(array $config_overrides = [])
    {
        $message_bank = $this->getMessageBank('fast_jobs', $this->getTestConfig($config_overrides));
        $test_consumer = new Consumer($message_bank);

        return $test_consumer;
    }

    /**
     * @param OutgoingMessage $message
     */
    protected function produceMessage(OutgoingMessage $message)
    {
        $message_bank = $this->getMessageBank('fast_jobs', $this->getTestConfig());
        $producer = new Producer($message_bank);

        $producer->produceMessage($message);
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
