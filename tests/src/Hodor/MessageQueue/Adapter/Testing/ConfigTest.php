<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\FactoryInterface;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Hodor\MessageQueue\Adapter\Testing\Config::__construct
     * @covers Hodor\MessageQueue\Adapter\Testing\Config::getAdapterFactory
     */
    public function testAdapterFactoryReturnedIsSameProvidedToConstructor()
    {
        $adapter_factory = $this->getAdapterFactoryMock();
        $config = new Config($adapter_factory);

        $this->assertSame($adapter_factory, $config->getAdapterFactory());
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Testing\Config::getQueueConfig
     * @expectedException \OutOfBoundsException
     * @dataProvider queueConfigProvider
     * @param string $queue_key
     */
    public function testRetrievingUndefinedQueueThrowsAnException($queue_key)
    {
        $config = new Config($this->getAdapterFactoryMock());

        $config->getQueueConfig($queue_key);
    }

    /**
     * @covers Hodor\MessageQueue\Adapter\Testing\Config::getQueueConfig
     * @dataProvider queueConfigProvider
     * @param string $queue_key
     * @param array $queue_config
     */
    public function testAddedQueueConfigCanBeRetrieved($queue_key, array $queue_config)
    {
        $config = new Config($this->getAdapterFactoryMock());

        $config->addQueueConfig($queue_key, $queue_config);
        $this->assertEquals($queue_config, $config->getQueueConfig($queue_key));
    }

    public function queueConfigProvider()
    {
        return [
            ['default-queue-key', ['queue_name' => 'default-queue-name', 'host' => 'localhost']],
            [uniqid(), ['queue_name' => uniqid(), 'host' => uniqid()]],
        ];
    }

    /**
     * @return FactoryInterface
     */
    private function getAdapterFactoryMock()
    {
        return $this->getMock('Hodor\MessageQueue\Adapter\FactoryInterface');
    }
}
