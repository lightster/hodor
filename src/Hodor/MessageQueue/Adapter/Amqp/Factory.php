<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class Factory implements FactoryInterface
{
    /**
     * @var AbstractConnection[]
     */
    private $connections = [];

    /**
     * @var AMQPChannel[]
     */
    private $channels = [];

    /**
     * @var Consumer[]
     */
    private $consumers = [];

    /**
     * @var Producer[]
     */
    private $producers = [];

    /**
     * @param array $queue_config
     * @return ConsumerInterface
     */
    public function getConsumer(array $queue_config)
    {
        $queue_name = $queue_config['queue_name'];

        if (array_key_exists($queue_name, $this->consumers)) {
            return $this->consumers[$queue_name];
        }

        $channel = $this->getAmqpChannel($queue_config);
        $this->consumers[$queue_name] = new Consumer($queue_name, $channel);

        return $this->consumers[$queue_name];
    }

    /**
     * @param array $queue_config
     * @return ProducerInterface
     */
    public function getProducer(array $queue_config)
    {
        $queue_name = $queue_config['queue_name'];

        if (array_key_exists($queue_name, $this->producers)) {
            return $this->producers[$queue_name];
        }

        $channel = $this->getAmqpChannel($queue_config);
        $this->producers[$queue_name] = new Producer($queue_name, $channel);

        return $this->producers[$queue_name];
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
     * @return AbstractConnection
     */
    private function getAmqpConnection(array $queue_config)
    {
        $connection_key = $this->getConnectionKey($queue_config);

        if (isset($this->connections[$connection_key])) {
            return $this->connections[$connection_key];
        }

        $connection_class = '\PhpAmqpLib\Connection\AMQPConnection';
        if (isset($queue_config['connection_type'])
            && 'socket' === $queue_config['connection_type']
        ) {
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
