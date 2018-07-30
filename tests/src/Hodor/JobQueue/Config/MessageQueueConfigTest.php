<?php

namespace Hodor\JobQueue\Config;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\MessageBankFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Config\MessageQueueConfig
 */
class MessageQueueConfigTest extends TestCase
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
        $config = new MessageQueueConfig(new QueueConfig($hodor_config));

        $this->assertEquals(
            [
                'host'        => $expected_config['host'],
                'port'        => $expected_config['port'],
                'username'    => $expected_config['username'],
                'password'    => $expected_config['password'],
                'queue_name'  => $expected_config['queue_name'],
                'fetch_count' => $expected_config['fetch_count'],
            ],
            $config->getQueueConfig($queue_name)
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueConfig
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testQueueConfigForUnknownConfigThrowsAnException()
    {
        $config = new MessageQueueConfig(new QueueConfig(['worker_queues' => []]));
        $config->getQueueConfig('worker-missing');
    }

    /**
     * @covers ::getAdapterFactoryConfig
     * @covers ::<private>
     */
    public function testAdapterFactoryCanBeRetrieved()
    {
        $this->assertEquals(
            ['type' => 'amqp'],
            (new MessageQueueConfig(new QueueConfig([])))->getAdapterFactoryConfig()
        );
    }

    /**
     * @covers ::getAdapterFactoryConfig
     * @covers ::<private>
     */
    public function testTestingAdapterFactoryCanBeRetrieved()
    {
        $message_bank_factory = new MessageBankFactory();

        $adapter_factory_config = [
            'type'                 => 'testing',
            'message_bank_factory' => $message_bank_factory,
        ];
        $message_queue_config = new MessageQueueConfig(new QueueConfig([]), $adapter_factory_config);
        $message_bank_factory->setConfig($message_queue_config);

        $this->assertEquals(
            $adapter_factory_config,
            $message_queue_config->getAdapterFactoryConfig()
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
