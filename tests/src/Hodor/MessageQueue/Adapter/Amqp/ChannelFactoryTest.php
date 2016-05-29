<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\Testing\Config;
use LogicException;
use PHPUnit_Framework_TestCase;

class ChannelFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::getChannel
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::<private>
     */
    public function testChannelsCanBeRetrieved()
    {
        $queues = $this->getTestQueues();
        $config = $this->getTestConfig($queues);

        $channel_factory = new ChannelFactory($config);
        foreach ($queues as $queue_key => $queue_config) {
            $channel = $channel_factory->getChannel($queue_key);
            $this->assertInstanceOf('Hodor\MessageQueue\Adapter\Amqp\Channel', $channel);
            $this->assertEquals($queue_config['queue_name'], $channel->getQueueName());
        }
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::getChannel
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::<private>
     */
    public function testChannelsAreReusedIfSameQueueKeyIsRequested()
    {
        $config = $this->getTestConfig($this->getTestQueues());

        $channel_factory = new ChannelFactory($config);
        $this->assertSame(
            $channel_factory->getChannel('fast_jobs'),
            $channel_factory->getChannel('fast_jobs')
        );
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::getChannel
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::<private>
     */
    public function testConnectionsAreReusedIfSameQueueConfigIsUsed()
    {
        $all_queues = $this->getTestQueues();
        $queues = [
            'original'  => $all_queues['fast_jobs'],
            'duplicate' => $all_queues['fast_jobs'],
        ];
        $config = $this->getTestConfig($queues);

        $channel_factory = new ChannelFactory($config);
        $this->assertSame(
            $channel_factory->getChannel('original')->getAmqpChannel()->getConnection(),
            $channel_factory->getChannel('duplicate')->getAmqpChannel()->getConnection()
        );
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::getChannel
     * @covers Hodor\MessageQueue\Adapter\Amqp\ChannelFactory::<private>
     * @dataProvider provideRequiredQueueConfigOptions
     * @param string $config_key
     * @expectedException LogicException
     */
    public function testAnExceptionIsThrownIfAnyRequiredConfigElementsAreMissing($config_key)
    {
        $all_queues = $this->getTestQueues();
        $queue = $all_queues['fast_jobs'];
        unset($queue[$config_key]);
        $config = $this->getTestConfig(['fast_jobs' => $queue]);

        $channel_factory = new ChannelFactory($config);
        $channel_factory->getChannel('fast_jobs');
    }

    /**
     * @return array
     */
    public function provideRequiredQueueConfigOptions()
    {
        return [['host'], ['port'], ['username'], ['password'], ['queue_name'],];
    }

    /**
     * @param array $queues
     * @return Config
     */
    private function getTestConfig(array $queues)
    {
        $config = new Config($this->getMock('Hodor\MessageQueue\Adapter\FactoryInterface'));
        foreach ($queues as $queue_key => $queue_config) {
            $config->addQueueConfig($queue_key, $queue_config);
        }

        return $config;
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
            'slow_jobs' => [
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
