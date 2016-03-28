<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Message;
use PhpAmqpLib\Channel\AMQPChannel;

class Consumer implements ConsumerInterface
{
    /**
     * @var string
     */
    private $queue_name;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @param string $queue_name
     * @param AMQPChannel $channel
     */
    public function __construct($queue_name, AMQPChannel $channel)
    {
        $this->queue_name = $queue_name;
        $this->channel = $channel;
    }

    /**
     * @param callable $callback
     */
    public function consumeMessage(callable $callback)
    {
        $this->channel->basic_consume(
            $this->queue_name,
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

    /**
     * @param Message $message
     */
    public function acknowledgeMessage(Message $message)
    {
        $message->acknowledge();
    }
}
