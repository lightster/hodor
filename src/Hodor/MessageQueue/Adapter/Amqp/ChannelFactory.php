<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use LogicException;

class ChannelFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var Channel[]
     */
    private $channels = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function disconnectAll()
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }
    }

    /**
     * @param  string $queue_key
     * @return Channel
     */
    public function getChannel($queue_key)
    {
        if (isset($this->channels[$queue_key])) {
            return $this->channels[$queue_key];
        }

        $queue_config = $this->getQueueConfig($queue_key);
        $connection = $this->getConnection($queue_config);

        $this->channels[$queue_key] = new Channel($connection, $queue_config);

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
                'fetch_count'              => 1,
                'connection_type'          => 'stream',
                'max_messages_per_consume' => 1,
                'max_time_per_consume'     => 600,
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
     * @return Connection
     */
    private function getConnection(array $queue_config)
    {
        $connection_key = $this->getConnectionKey($queue_config);

        if (isset($this->connections[$connection_key])) {
            return $this->connections[$connection_key];
        }

        $this->connections[$connection_key] = new Connection($queue_config);

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
