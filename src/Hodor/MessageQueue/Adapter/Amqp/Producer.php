<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Message;
use PhpAmqpLib\Channel\AMQPChannel;

class Producer implements ProducerInterface
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
     * @param Message $message
     */
    public function produceMessage(Message $message)
    {
        $this->channel->basic_publish(
            $message->getAmqpMessage(),
            '',
            $this->queue_name
        );
    }

    /**
     * @param Message[] $messages
     */
    public function produceMessageBatch(array $messages)
    {
        if (count($messages) == 0) {
            return;
        }

        foreach ($messages as $message) {
            $this->channel->batch_basic_publish(
                $message->getAmqpMessage(),
                '',
                $this->queue_name
            );
        }
        $this->channel->publish_batch();
    }
}
