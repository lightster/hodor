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
     * @param string $message
     */
    public function produceMessage($message)
    {
        $this->channel->basic_publish(
            $this->generateAmqpMessage($message),
            '',
            $this->queue_name
        );
    }

    /**
     * @param string[] $messages
     */
    public function produceMessageBatch(array $messages)
    {
        foreach ($messages as $message) {
            $this->channel->batch_basic_publish(
                $this->generateAmqpMessage($message),
                '',
                $this->queue_name
            );
        }
        $this->channel->publish_batch();
    }

    /**
     * @param string $json_message
     * @return AMQPMessage
     * @throws RuntimeException
     */
    private function generateAmqpMessage($json_message)
    {
        return new AMQPMessage(
            $json_message,
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );
    }
}
