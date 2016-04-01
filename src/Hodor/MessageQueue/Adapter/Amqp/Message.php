<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\MessageInterface;
use PhpAmqpLib\Message\AMQPMessage;

class Message implements MessageInterface
{
    /**
     * @var AMQPMessage $amqp_message
     */
    private $amqp_message;

    /**
     * @param AMQPMessage $amqp_message
     */
    public function __construct(AMQPMessage $amqp_message)
    {
        $this->amqp_message = $amqp_message;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->amqp_message->body;
    }

    public function acknowledge()
    {
        $this->amqp_message->delivery_info['channel']
            ->basic_ack($this->amqp_message->delivery_info['delivery_tag']);
    }
}
