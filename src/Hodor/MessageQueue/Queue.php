<?php

namespace Hodor\MessageQueue;

use PhpAmqpLib\Channel\AMQPChannel;

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
                var_dump($amqp_message->body);
                $amqp_message->delivery_info['channel']
                    ->basic_ack($amqp_message->delivery_info['delivery_tag']);
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
