<?php

namespace Hodor\JobQueue\Config;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config\MessageQueueConfig
 */
class MessageQueueConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getQueueConfig
     * @covers ::<private>
     * @dataProvider provideQueueConfigs
     * @param array $expected_config
     * @param $queue_name
     * @param array $hodor_config
     * @throws Exception
     */
    public function testQueueConfigCanBeGenerated(array $expected_config, $queue_name, array $hodor_config)
    {
        $config = new MessageQueueConfig($hodor_config);

        $this->assertEquals($expected_config, $config->getQueueConfig($queue_name));
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testQueueConfigForUnknownConfigThrowsAnException()
    {
        $config = new MessageQueueConfig(['worker_queues' => []]);
        $config->getQueueConfig('worker-missing');
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     * @covers ::<private>
     */
    public function testAdapterFactoryCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Amqp\Factory',
            (new MessageQueueConfig([]))->getAdapterFactory()
        );
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     * @covers ::<private>
     */
    public function testAdapterFactoryIsReused()
    {
        $config = new MessageQueueConfig([]);

        $this->assertSame(
            $config->getAdapterFactory(),
            $config->getAdapterFactory()
        );
    }

    /**
     * @covers ::generateAdapterFactory
     * @covers ::getAdapterFactory
     * @covers ::<private>
     */
    public function testTestingAdapterFactoryCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Testing\Factory',
            (new MessageQueueConfig(['adapter_factory' => 'testing']))->getAdapterFactory()
        );
    }

    /**
     * @return array
     */
    public function provideQueueConfigs()
    {
        return require __DIR__ . '/../ConfigTest.queue-config.dataset.php';
    }
}
