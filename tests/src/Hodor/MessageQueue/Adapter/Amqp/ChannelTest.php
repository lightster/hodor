<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

class ChannelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::getAmqpChannel
     */
    public function testAmqpChannelPassedToConstructorIsTheSameRetrieved()
    {
        $amqp_channel = $this
            ->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel = new Channel($amqp_channel, []);

        $this->assertSame($amqp_channel, $channel->getAmqpChannel());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::getQueueName
     */
    public function testQueueNamePassedToConstructorIsTheSameRetrieved()
    {
        $queue_name = uniqid();

        $amqp_channel = $this
            ->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel = new Channel($amqp_channel, ['queue_name' => $queue_name]);

        $this->assertEquals($queue_name, $channel->getQueueName());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::getMaxMessagesPerConsume
     */
    public function testMaxMessagesPerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_messages = rand(1, 100);

        $amqp_channel = $this
            ->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel = new Channel($amqp_channel, ['max_messages_per_consume' => $max_messages]);

        $this->assertEquals($max_messages, $channel->getMaxMessagesPerConsume());
    }

    /**
     * __construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Channel::getMaxTimePerConsume
     */
    public function testMaxTimePerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_time = rand(1, 100);

        $amqp_channel = $this
            ->getMockBuilder('PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel = new Channel($amqp_channel, ['max_time_per_consume' => $max_time]);

        $this->assertEquals($max_time, $channel->getMaxTimePerConsume());
    }
}
