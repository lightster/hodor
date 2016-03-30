<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\MessageInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

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
     * @param MessageInterface $message
     */
    public function produceMessage(MessageInterface $message)
    {
        $this->channel->basic_publish(
            $message->getAmqpMessage(),
            '',
            $this->queue_name
        );
    }

    /**
     * @param MessageInterface[] $messages
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

    /**
     * @param mixed $message
     * @return MessageInterface
     * @throws RuntimeException
     */
    public function generateMessage($message)
    {
        $json_message = json_encode($message, JSON_FORCE_OBJECT, 100);
        if (false === $json_message) {
            throw new RuntimeException("Failed to json_encode message with name '{$message['name']}'.");
        }

        $amqp_message = new AMQPMessage(
            $json_message,
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );

        return new Message($amqp_message);
    }
}
