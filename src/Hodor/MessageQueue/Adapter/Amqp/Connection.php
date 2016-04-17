<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use LogicException;
use PhpAmqpLib\Connection\AbstractConnection;

class Connection
{
    /**
     * @var array
     */
    private $connection_config;

    /**
     * @var AbstractConnection
     */
    private $amqp_connection;

    /**
     * @param array $connection_config
     */
    public function __construct(array $connection_config)
    {
        $this->connection_config = array_merge(
            ['connection_type' => 'stream'],
            $connection_config
        );

        $this->validateConfig();
    }

    public function __destruct()
    {
        if (!$this->amqp_connection || !$this->amqp_connection->isConnected()) {
            return;
        }

        $this->amqp_connection->close();
    }

    /**
     * @return AbstractConnection
     */
    public function getAmqpConnection()
    {
        if ($this->amqp_connection) {
            return $this->amqp_connection;
        }

        $connection_class = '\PhpAmqpLib\Connection\AMQPStreamConnection';
        if ('socket' === $this->connection_config['connection_type']) {
            $connection_class = '\PhpAmqpLib\Connection\AMQPSocketConnection';
        }

        $this->amqp_connection = new $connection_class(
            $this->connection_config['host'],
            $this->connection_config['port'],
            $this->connection_config['username'],
            $this->connection_config['password']
        );

        return $this->amqp_connection;
    }

    /**
     * @throws LogicException
     */
    private function validateConfig()
    {
        foreach (['host', 'port', 'username', 'password'] as $key) {
            if (empty($this->connection_config[$key])) {
                throw new LogicException("The connection config must contain a '{$key}' config.");
            }
        }
    }
}
