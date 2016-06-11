<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\OutgoingMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

class Producer implements ProducerInterface
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
     * @param OutgoingMessage $message
     */
    public function produceMessage(OutgoingMessage $message)
    {
        $this->getChannel()->getAmqpChannel()->basic_publish(
            $this->generateAmqpMessage($message),
            '',
            $this->getChannel()->getQueueName()
        );
    }

    /**
     * @param string[] $messages
     */
    public function produceMessageBatch(array $messages)
    {
        $amqp_channel = $this->getChannel()->getAmqpChannel();

        foreach ($messages as $message) {
            $amqp_channel->batch_basic_publish(
                $this->generateAmqpMessage($message),
                '',
                $this->getChannel()->getQueueName()
            );
        }
        $amqp_channel->publish_batch();
    }

    /**
     * @param OutgoingMessage $message
     * @return AMQPMessage
     * @throws RuntimeException
     */
    private function generateAmqpMessage(OutgoingMessage $message)
    {
        return new AMQPMessage(
            $message->getEncodedContent(),
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );
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
