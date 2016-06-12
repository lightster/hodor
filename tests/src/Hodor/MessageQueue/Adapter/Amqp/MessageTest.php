<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\MessageTest as BaseMessageTest;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Message
 */
class MessageTest extends BaseMessageTest
{
    /**
     * @param $body
     * @return Message
     */
    protected function getBasicMessage($body)
    {
        return new Message(new AMQPMessage($body));
    }

    /**
     * @return Message
     */
    protected function getAcknowledgeableMessage()
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
