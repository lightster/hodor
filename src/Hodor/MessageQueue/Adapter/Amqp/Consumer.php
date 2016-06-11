<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\IncomingMessage as MqMessage;
use PhpAmqpLib\Channel\AMQPChannel;

class Consumer implements ConsumerInterface
{
    /**
     * @var string
     */
    private $queue_key;

    /**
     * @var ChannelFactory
     */
    private $channel_factory;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @param string $queue_key
     * @param ChannelFactory $channel_factory
     */
    public function __construct($queue_key, ChannelFactory $channel_factory)
    {
        $this->queue_key = $queue_key;
        $this->channel_factory = $channel_factory;
    }

    /**
     * @param callable $callback
     */
    public function consumeMessage(callable $callback)
    {
        $amqp_channel = $this->getChannel()->getAmqpChannel();

        $amqp_channel->basic_consume(
            $this->getChannel()->getQueueName(),
            '',
            false,
            ($auto_ack = false),
            false,
            false,
            function ($amqp_message) use ($callback) {
                $message = new MqMessage(new Message($amqp_message));
                $callback($message);
            }
        );

        $amqp_channel->wait();
    }

    /**
     * @return int
     */
    public function getMaxMessagesPerConsume()
    {
        return $this->getChannel()->getMaxMessagesPerConsume();
    }

    /**
     * @return int
     */
    public function getMaxTimePerConsume()
    {
        return $this->getChannel()->getMaxTimePerConsume();
    }

    /**
     * @return Channel
     */
    private function getChannel()
    {
        if ($this->channel) {
            return $this->channel;
        }

        $this->channel = $this->channel_factory->getChannel($this->queue_key);

        return $this->channel;
    }
}
