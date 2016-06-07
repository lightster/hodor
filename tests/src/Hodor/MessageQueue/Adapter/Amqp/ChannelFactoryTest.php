<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\Testing\Config;
use LogicException;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\ChannelFactory
 */
class ChannelFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
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
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
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
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
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
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::disconnectAll
     * @covers ::<private>
     */
    public function testAllConnectionsAreClosedWhenDisconnectAllIsCalled()
    {
        $queues = $this->getTestQueues();
        $config = $this->getTestConfig($queues);

        $channel_factory = new ChannelFactory($config);
        $fast_jobs = $channel_factory->getChannel('fast_jobs')->getAmqpChannel()->getConnection();
        $slow_jobs = $channel_factory->getChannel('slow_jobs')->getAmqpChannel()->getConnection();

        $this->assertTrue($fast_jobs->isConnected());
        $this->assertTrue($slow_jobs->isConnected());

        $channel_factory->disconnectAll();

        $this->assertFalse($fast_jobs->isConnected());
        $this->assertFalse($slow_jobs->isConnected());
    }

    /**
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
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
        $config_provider = new ConfigProvider($this);

        return $config_provider->getConfigAdapter($queues);
    }

    /**
     * @return array
     */
    private function getTestQueues()
    {
        $config_provider = new ConfigProvider($this);

        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
            'slow_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
