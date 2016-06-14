<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Amqp\Factory;
use Hodor\MessageQueue\Adapter\Config;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\QueueFactory
 */
class QueueFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::getAdapterFactory
     */
    public function testQueueCanBeGenerated()
    {
        $this->assertInstanceOf(
            '\Hodor\MessageQueue\Queue',
            $this->generateQueueFactory()->getQueue('worker-default')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::getAdapterFactory
     */
    public function testQueueIsReusedIfReferredToMultipleTimes()
    {
        $queue_factory = $this->generateQueueFactory();

        $this->assertSame(
            $queue_factory->getQueue('worker-default'),
            $queue_factory->getQueue('worker-default')
        );
    }

    public function queueConfigProvider()
    {
        $config_path = __DIR__ . '/../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;
        $config_template = $config['test']['rabbitmq'];

        return [
            'host'        => $config_template['host'],
            'port'        => $config_template['port'],
            'username'    => $config_template['username'],
            'password'    => $config_template['password'],
            'queue_name'  => $config_template['queue_prefix'] . uniqid(),
            'fetch_count' => 1,
        ];
    }

    private function generateQueueFactory()
    {
        $config_adapter = $this->getMock('\Hodor\MessageQueue\Adapter\ConfigInterface');
        $config_adapter->method('getAdapterFactory')
            ->willReturn(new Factory($config_adapter));
        $config_adapter->method('getQueueConfig')
            ->willReturn($this->queueConfigProvider());

        return new QueueFactory($config_adapter);
    }
}
