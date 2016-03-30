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

    /**
     * @return string
     */
    public function getContentType()
    {
        if (!$this->amqp_message->has('content_type')) {
            return null;
        }

        return $this->amqp_message->get('content_type');
    }

    public function acknowledge()
    {
        $this->amqp_message->delivery_info['channel']
            ->basic_ack($this->amqp_message->delivery_info['delivery_tag']);
    }

    /**
     * @return AMQPMessage
     */
    public function getAmqpMessage()
    {
        return $this->amqp_message;
    }
}
