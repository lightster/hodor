<?php

namespace Hodor\MessageQueue;

use Hodor\Config;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;

class QueueFactory
{
    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var array
     */
    private $channels = [];

    /**
     * @var array
     */
    private $queues = [];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string $queue_name [description]
     * @return \Hodor\MessageQueue\Queue
     */
    public function getWorkerQueue($queue_name)
    {
        if (isset($this->queues[$queue_name])) {
            return $this->queues[$queue_name];
        }

        $queue_config = $this->config->getWorkerQueueConfig($queue_name);
        $this->queues[$queue_name] = new Queue(
            $queue_config,
            $this->getAmqpChannel($queue_config)
        );

        return $this->queues[$queue_name];
    }

    /**
     * @param  array  $queue_config
     * @return AMQPChannel
     */
    private function getAmqpChannel(array $queue_config)
    {
        $channel_key = $queue_config['queue_name'];

        if (isset($this->channels[$channel_key])) {
            return $this->channels[$channel_key];
        }

        $connection = $this->getAmqpConnection($queue_config);

        $channel = $connection->channel();

        $channel->queue_declare(
            $queue_config['queue_name'],
            false,
            ($is_durable = true),
            false,
            false
        );
        $channel->basic_qos(
            null,
            $queue_config['fetch_count'],
            null
        );

        $this->channels[$channel_key] = $channel;

        return $this->channels[$channel_key];
    }

    /**
     * @param  array  $queue_config
     * @return AMQPConnection
     */
    private function getAmqpConnection(array $queue_config)
    {
        $connection_key = $this->getConnectionKey($queue_config);

        if (isset($this->connections[$connection_key])) {
            return $this->connections[$connection_key];
        }

        $this->connections[$connection_key] = new AMQPConnection(
            $queue_config['host'],
            $queue_config['port'],
            $queue_config['username'],
            $queue_config['password']
        );

        return $this->connections[$connection_key];
    }

    /**
     * @param  array  $queue_config
     * @return string
     */
    private function getConnectionKey(array $queue_config)
    {
        return implode(
            '::',
            [
                $queue_config['host'],
                $queue_config['port'],
                $queue_config['username'],
                $queue_config['queue_name'],
            ]
        );
    }
}
