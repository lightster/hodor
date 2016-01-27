<?php

namespace Hodor\MessageQueue;

use Exception;
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
     * @param array $queue_config
     * @param AMQPChannel $channel
     */
    public function __construct(array $queue_config, AMQPChannel $channel)
    {
        $this->queue_config = $queue_config;
        $this->channel = $channel;
    }

    /**
     * @param  mixed $message
     * @throws Exception
     */
    public function push($message)
    {
        $json_message = json_encode($message, JSON_FORCE_OBJECT, 100);
        if (false === $json_message) {
            throw new Exception("Failed to json_encode message with name '{$message['name']}'.");
        }

        $amqp_message = new AMQPMessage(
            $json_message,
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );
        $this->channel->basic_publish(
            $amqp_message,
            '',
            $this->queue_config['queue_name']
        );
    }

    /**
     * @param  callable $callback to use for handling the message
     */
    public function consume(callable $callback)
    {
        $this->channel->basic_consume(
            $this->queue_config['queue_name'],
            '',
            false,
            ($auto_ack = false),
            false,
            false,
            function ($amqp_message) use ($callback) {
                $message = new Message($amqp_message);
                $callback($message);
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
