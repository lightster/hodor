<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Channel
 */
class ChannelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::<private>
     * @dataProvider provideQueueConfigMissingARequiredField
     * @expectedException \LogicException
     * @param array $queue_config
     */
    public function testExceptionIsThrownIfARequiredFieldIsMissing(array $queue_config)
    {
        new Channel($this->getMockConnection(), $queue_config);
    }

    /**
     * @covers ::__construct
     * @covers ::<private>
     */
    public function testConnectionCanBeInstantiatedWithoutError()
    {
        $connection = $this->getMockConnection();
        $channel = new Channel($connection, ['queue_name' => uniqid()]);

        $this->assertInstanceOf('Hodor\MessageQueue\Adapter\Amqp\Channel', $channel);
    }

    /**
     * @covers ::__construct
     * @covers ::getAmqpChannel
     * @dataProvider provideQueueList
     * @param array $queues
     */
    public function testAmqpChannelsCanBeRetrieved(array $queues)
    {
        foreach ($queues as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertInstanceOf('PhpAmqpLib\Channel\AMQPChannel', $channel->getAmqpChannel());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getAmqpChannel
     * @dataProvider provideQueueList
     * @param array $queues
     */
    public function testAmqpChannelsCanBeReused(array $queues)
    {
        foreach ($queues as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertSame($channel->getAmqpChannel(), $channel->getAmqpChannel());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueName
     */
    public function testQueueNamePassedToConstructorIsTheSameRetrieved()
    {
        $queue_name = uniqid();

        $connection = $this->getMockConnection();
        $channel = new Channel($connection, ['queue_name' => $queue_name]);

        $this->assertEquals($queue_name, $channel->getQueueName());
    }

    /**
     * @covers ::__construct
     * @covers ::getMaxMessagesPerConsume
     */
    public function testMaxMessagesPerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_messages = rand(1, 100);

        $connection = $this->getMockConnection();
        $channel = new Channel($connection, [
            'queue_name'               => uniqid(),
            'max_messages_per_consume' => $max_messages,
        ]);

        $this->assertEquals($max_messages, $channel->getMaxMessagesPerConsume());
    }

    /**
     * @covers ::__construct
     * @covers ::getMaxTimePerConsume
     */
    public function testMaxTimePerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_time = rand(1, 100);

        $connection = $this->getMockConnection();
        $channel = new Channel($connection, [
            'queue_name'           => uniqid(),
            'max_time_per_consume' => $max_time
        ]);

        $this->assertEquals($max_time, $channel->getMaxTimePerConsume());
    }

    /**
     * @return array
     */
    public function provideQueueConfigMissingARequiredField()
    {
        $required_fields = [
            'queue_name' => uniqid(),
        ];

        $queue_configs = [];
        foreach (array_keys($required_fields) as $field_to_remove) {
            $queue_config = $required_fields;
            unset($queue_config[$field_to_remove]);

            $queue_configs[] = [$queue_config];
        }

        return $queue_configs;
    }

    /**
     * @return array
     */
    public function provideQueueList()
    {
        $config_provider = new ConfigProvider();

        return [
            [
                [
                    'fast_jobs' => $config_provider->getQueueConfig(),
                    'slow_jobs' => $config_provider->getQueueConfig(),
                ]
            ]
        ];
    }

    /**
     * @return Connection
     */
    private function getMockConnection()
    {
        /**
         * @var Connection $connection
         */
        return $this
            ->getMockBuilder('Hodor\MessageQueue\Adapter\Amqp\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
