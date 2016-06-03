<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ConsumerTest as BaseConsumerTest;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\Message;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Consumer
 */
class ConsumerTest extends BaseConsumerTest
{
    /**
     * @var ChannelFactory[]
     */
    private $channel_factories;

    /**
     * @var Config
     */
    private $config;

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->channel_factories as $channel_factory) {
            $channel_factory->disconnectAll();
        }
    }

    /**
     * @param array $config_overrides
     * @return ConsumerInterface
     */
    protected function getTestConsumer(array $config_overrides = [])
    {
        $channel_factory = $this->generateChannelFactory($this->getTestConfig($config_overrides));
        $test_consumer = new Consumer('fast_jobs', $channel_factory);

        return $test_consumer;
    }

    /**
     * @param string $message
     */
    protected function produceMessage($message)
    {
        $channel_factory = $this->generateChannelFactory($this->getTestConfig());
        $producer = new Producer('fast_jobs', $channel_factory);

        $producer->produceMessage($message);
    }

    /**
     * @param Config $config
     * @return ChannelFactory
     */
    private function generateChannelFactory(Config $config)
    {
        $channel_factory = new ChannelFactory($config);

        $this->channel_factories[] = $channel_factory;

        return $channel_factory;
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

        $config = new Config($this->getMock('Hodor\MessageQueue\Adapter\FactoryInterface'));
        foreach ($this->getTestQueues() as $queue_key => $queue_config) {
            $config->addQueueConfig($queue_key, array_merge($queue_config, $config_overrides));
        }

        $this->config = $config;

        return $this->config;
    }

    private function getTestQueues()
    {
        $rabbit_credentials = $this->getRabbitCredentials();

        return [
            'fast_jobs' => [
                'host'       => $rabbit_credentials['host'],
                'port'       => $rabbit_credentials['port'],
                'username'   => $rabbit_credentials['username'],
                'password'   => $rabbit_credentials['password'],
                'queue_name' => $rabbit_credentials['queue_prefix'] . uniqid(),
            ],
        ];
    }

    /**
     * @return array
     */
    private function getRabbitCredentials()
    {
        $config = require __DIR__ . '/../../../../../../config/config.test.php';

        return $config['test']['rabbitmq'];
    }
}
