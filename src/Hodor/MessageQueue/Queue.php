<?php

namespace Hodor\MessageQueue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Queue
{
    /**
     * @var array
     */
    private $queue_config;

    /**
     * @var AMQPChannel $channel
     */
    private $channel;

    /**
     * @param AMQPChannel $channel
     */
    public function __construct(array $queue_config, AMQPChannel $channel)
    {
        $this->queue_config = $queue_config;
        $this->channel = $channel;
    }

    /**
     * @param  mixed $message
     */
    public function push($message)
    {
        $amqp_message = new AMQPMessage(
            json_encode($message),
            [
                'content_type' => 'text/plain',
                'delivery_mode' => 2
            ]
        );
        $this->channel->basic_publish(
            $amqp_message,
            '',
            $this->queue_config['queue_name']
        );
    }

    public function consume()
    {
        $this->channel->basic_consume(
            $this->queue_config['queue_name'],
            '',
            false,
            ($auto_ack = false),
            false,
            false,
            function ($amqp_message) {
                $message = new Message($amqp_message);
                var_dump($message->getContent());
                $message->acknowledge();
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
