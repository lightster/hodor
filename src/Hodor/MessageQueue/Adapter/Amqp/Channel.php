<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

class Channel
{
    /**
     * @var AMQPChannel
     */
    private $amqp_channel;

    /**
     * @var array
     */
    private $queue_config;

    /**
     * @param AMQPChannel $amqp_channel
     * @param array $queue_config
     */
    public function __construct(AMQPChannel $amqp_channel, array $queue_config)
    {
        $this->amqp_channel = $amqp_channel;
        $this->queue_config = $queue_config;
    }

    /**
     * @return AMQPChannel
     */
    public function getAmqpChannel()
    {
        return $this->amqp_channel;
    }

    /**
     * @return mixed
     */
    public function getQueueName()
    {
        return $this->queue_config['queue_name'];
    }
}
