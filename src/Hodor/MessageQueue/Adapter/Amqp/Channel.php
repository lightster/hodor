<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;

class Channel
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $queue_config;

    /**
     * @var AMQPChannel
     */
    private $amqp_channel;

    /**
     * @param Connection $connection
     * @param array $queue_config
     */
    public function __construct(Connection $connection, array $queue_config)
    {
        $this->connection = $connection;
        $this->queue_config = array_merge(
            [
                'fetch_count'              => 1,
                'max_messages_per_consume' => 1,
                'max_time_per_consume'     => 600,
            ],
            $queue_config
        );

        $this->validateConfig();
    }

    /**
     * @return AMQPChannel
     */
    public function getAmqpChannel()
    {
        if ($this->amqp_channel) {
            return $this->amqp_channel;
        }

        $this->amqp_channel = $this->connection->getAmqpConnection()->channel();

        $this->amqp_channel->queue_declare(
            $this->queue_config['queue_name'],
            false,
            ($is_durable = true),
            false,
            false
        );
        $this->amqp_channel->basic_qos(
            null,
            $this->queue_config['fetch_count'],
            null
        );

        return $this->amqp_channel;
    }

    /**
     * @return mixed
     */
    public function getQueueName()
    {
        return $this->queue_config['queue_name'];
    }

    /**
     * @return int
     */
    public function getMaxMessagesPerConsume()
    {
        return $this->queue_config['max_messages_per_consume'];
    }

    /**
     * @return int
     */
    public function getMaxTimePerConsume()
    {
        return $this->queue_config['max_time_per_consume'];
    }

    /**
     * @throws LogicException
     */
    private function validateConfig()
    {
        foreach (['queue_name'] as $key) {
            if (empty($this->queue_config[$key])) {
                throw new LogicException("The connection config must contain a '{$key}' config.");
            }
        }
    }
}
