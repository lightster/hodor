<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Message
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testMessageCanBeInstantiated()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Amqp\Message',
            new Message(new AMQPMessage())
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentCanBeRetrieved()
    {
        $expected_value = 'some_string';
        $message = $this->getBasicMessage($expected_value);

        $this->assertEquals($expected_value, $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentCanBeRetrievedMultipleTimes()
    {
        $message = $this->getBasicMessage('some_other_string');

        $this->assertEquals($message->getContent(), $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::acknowledge
     */
    public function testAmqpMessageIsAcknowledgedWhenMessageIsAcknowledged()
    {
        $message = $this->getAcknowledgeableMessage();

        $message->acknowledge();
    }

    /**
     * @param $body
     * @return Message
     */
    private function getBasicMessage($body)
    {
        return new Message(new AMQPMessage($body));
    }

    /**
     * @return Message
     */
    private function getAcknowledgeableMessage()
    {
        $delivery_tag = 'hey_there!';

        $channel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->setMethods(['basic_ack'])
            ->getMock();
        $channel->expects($this->once())
            ->method('basic_ack')
            ->with($delivery_tag);

        $amqp_message = new AMQPMessage();
        $amqp_message->delivery_info = [
            'channel'      => $channel,
            'delivery_tag' => $delivery_tag,
        ];
        return new Message($amqp_message);
    }
}
