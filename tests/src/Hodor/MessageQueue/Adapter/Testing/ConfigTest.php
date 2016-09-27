<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Config
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getAdapterFactoryConfig
     */
    public function testAdapterFactoryReturnedIsSameReturnedByFactoryGenerator()
    {
        $adapter_factory_config = $this->getAdapterFactoryConfig();
        $config = new Config($adapter_factory_config);

        $this->assertSame($adapter_factory_config, $config->getAdapterFactoryConfig());
    }

    /**
     * @covers ::getQueueConfig
     * @expectedException \OutOfBoundsException
     * @dataProvider queueConfigProvider
     * @param string $queue_key
     */
    public function testRetrievingUndefinedQueueThrowsAnException($queue_key)
    {
        $config = new Config($this->getAdapterFactoryConfig());

        $config->getQueueConfig($queue_key);
    }

    /**
     * @covers ::addQueueConfig
     * @covers ::getQueueConfig
     * @dataProvider queueConfigProvider
     * @param string $queue_key
     * @param array $queue_config
     */
    public function testAddedQueueConfigCanBeRetrieved($queue_key, array $queue_config)
    {
        $config = new Config($this->getAdapterFactoryConfig());

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
     * @return array
     */
    private function getAdapterFactoryConfig()
    {
        return [
            'type'                 => 'testing',
            'message_bank_factory' => new MessageBankFactory(),
        ];
    }
}
