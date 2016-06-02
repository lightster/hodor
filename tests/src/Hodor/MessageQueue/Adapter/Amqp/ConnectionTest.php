<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::<private>
     * @dataProvider provideConnectionConfigMissingARequiredField
     * @expectedException \LogicException
     * @param array $connection_config
     */
    public function testExceptionIsThrownIfARequiredFieldIsMissing(array $connection_config)
    {
        new Connection($connection_config);
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::<private>
     */
    public function testConnectionCanBeInstantiatedWithoutError()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Amqp\Connection',
            new Connection($this->getRabbitCredentials())
        );
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__destruct
     * @dataProvider provideConnectionsOfDifferentTypes
     * @param Connection
     */
    public function testConnectionCanBeDestroyedWithoutUsingAmqpConnection(Connection $connection)
    {
        $connection = new Connection($this->getRabbitCredentials());
        unset($connection);

        $this->assertTrue(true);
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::getAmqpConnection
     */
    public function testAmqpStreamConnectionIsUsedByDefault()
    {
        $connection = new Connection($this->getRabbitCredentials());
        $this->assertInstanceOf(
            'PhpAmqpLib\Connection\AMQPStreamConnection',
            $connection->getAmqpConnection()
        );
        $this->assertTrue($connection->getAmqpConnection()->isConnected());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__construct
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::getAmqpConnection
     */
    public function testAmqpSocketConnectionCanBeRequested()
    {
        $connection_config = $this->getRabbitCredentials();
        $connection_config['connection_type'] = 'socket';

        $connection = new Connection($connection_config);
        $this->assertInstanceOf(
            'PhpAmqpLib\Connection\AMQPSocketConnection',
            $connection->getAmqpConnection()
        );
        $this->assertTrue($connection->getAmqpConnection()->isConnected());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__destruct
     */
    public function testStreamConnectionIsClosedAfterDestroyingConnection()
    {
        $connection = new Connection($this->getRabbitCredentials());

        $amqp_connection = $connection->getAmqpConnection();

        $this->assertTrue($amqp_connection->isConnected());
        unset($connection);
        $this->assertFalse($amqp_connection->isConnected());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::__destruct
     */
    public function testSocketConnectionIsClosedAfterDestroyingConnection()
    {
        $connection_config = $this->getRabbitCredentials();
        $connection_config['connection_type'] = 'socket';
        $connection = new Connection($connection_config);

        $amqp_connection = $connection->getAmqpConnection();

        $this->assertTrue($amqp_connection->isConnected());
        unset($connection);
        $this->assertFalse($amqp_connection->isConnected());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Amqp\Connection::disconnect
     */
    public function testConnectionIsClosedAfterExplicitlyDisconnecting()
    {
        $connection = new Connection($this->getRabbitCredentials());

        $amqp_connection = $connection->getAmqpConnection();

        $this->assertTrue($amqp_connection->isConnected());
        $connection->disconnect();
        $this->assertFalse($amqp_connection->isConnected());
    }

    /**
     * @return array
     */
    public function provideConnectionConfigMissingARequiredField()
    {
        $rabbit_credentials = $this->getRabbitCredentials();

        $required_fields = [
            'host'     => $rabbit_credentials['host'],
            'port'     => $rabbit_credentials['port'],
            'username' => $rabbit_credentials['username'],
            'password' => $rabbit_credentials['password'],
        ];

        $connection_configs = [];
        foreach ($required_fields as $field_to_remove => $value) {
            $connection_config = $required_fields;
            unset($connection_config[$field_to_remove]);

            $connection_configs[] = [$connection_config];
        }

        return $connection_configs;
    }

    public function provideConnectionsOfDifferentTypes()
    {
        $rabbit_credentials = $this->getRabbitCredentials();

        $stream_config = $rabbit_credentials;

        $socket_config = $rabbit_credentials;
        $socket_config['connection_type'] = 'socket';

        return [
            [new Connection($stream_config)],
            [new Connection($socket_config)],
        ];
    }

    /**
     * @return array
     */
    private function getRabbitCredentials()
    {
        $config = require __DIR__ . '/../../../../../../config/config.test.php';
        $rabbit_credentials = $config['test']['rabbitmq'];

        return [
            'host'     => $rabbit_credentials['host'],
            'port'     => $rabbit_credentials['port'],
            'username' => $rabbit_credentials['username'],
            'password' => $rabbit_credentials['password'],
        ];
    }
}
