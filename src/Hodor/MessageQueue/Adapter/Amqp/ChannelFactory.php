<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class ChannelFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var AbstractConnection[]
     */
    private $connections = [];

    /**
     * @var AMQPChannel[]
     */
    private $channels = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string $queue_key
     * @return AMQPChannel
     */
    public function getChannel($queue_key)
    {
        if (isset($this->channels[$queue_key])) {
            return $this->channels[$queue_key];
        }

        $queue_config = $this->getQueueConfig($queue_key);
        $connection = $this->getAmqpConnection($queue_config);

        $amqp_channel = $connection->channel();

        $amqp_channel->queue_declare(
            $queue_config['queue_name'],
            false,
            ($is_durable = true),
            false,
            false
        );
        $amqp_channel->basic_qos(
            null,
            $queue_config['fetch_count'],
            null
        );

        $this->channels[$queue_key] = new Channel($amqp_channel, $queue_config);

        return $this->channels[$queue_key];
    }

    /**
     * @param $queue_key
     * @return array
     */
    private function getQueueConfig($queue_key)
    {
        $queue_config = array_merge(
            [
                'fetch_count'     => 1,
                'connection_type' => 'stream',
            ],
            $this->config->getQueueConfig($queue_key)
        );

        foreach (['host', 'port', 'username', 'password', 'queue_name'] as $key) {
            if (empty($queue_config[$key])) {
                throw new LogicException("The queue config must contain a '{$key}' config.");
            }
        }

        return $queue_config;
    }

    /**
     * @param  array  $queue_config
     * @return AbstractConnection
     */
    private function getAmqpConnection(array $queue_config)
    {
        $connection_key = $this->getConnectionKey($queue_config);

        if (isset($this->connections[$connection_key])) {
            return $this->connections[$connection_key];
        }

        $connection_class = '\PhpAmqpLib\Connection\AMQPConnection';
        if ('socket' === $queue_config['connection_type']) {
            $connection_class = '\PhpAmqpLib\Connection\AMQPSocketConnection';
        }

        $this->connections[$connection_key] = new $connection_class(
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
