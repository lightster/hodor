<?php

namespace Hodor\MessageQueue;

use PhpAmqpLib\Message\AMQPMessage;

class Message
{
    /**
     * @var AMQPMessage $amqp_message
     */
    private $amqp_message;

    /**
     * @var bool
     */
    private $is_loaded;

    /**
     * @var mixed
     */
    private $content;

    /**
     * @var bool
     */
    private $was_acked = false;

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
        if ($this->is_loaded) {
            return $this->content;
        }

        $this->content = $this->amqp_message->body;

        if ($this->amqp_message->has('content_type')
            && 'application/json' === $this->amqp_message->get('content_type')
        ) {
            $this->content = json_decode($this->content, true);
        }

        $this->is_loaded = true;

        return $this->content;
    }

    public function acknowledge()
    {
        if ($this->was_acked) {
            return;
        }

        $this->amqp_message->delivery_info['channel']
            ->basic_ack($this->amqp_message->delivery_info['delivery_tag']);

        $this->was_acked = true;
    }

    /**
     * This method exists temporarily during gradual refactoring.
     *
     * @return AMQPMessage
     * @deprecated
     */
    public function getAmqpMessage()
    {
        return $this->amqp_message;
    }
}
