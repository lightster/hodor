<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\MessageBankFactory
 */
class MessageBankFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = new Config(function () {});
        $this->config->addQueueConfig('test-queue', ['workers_per_server' => 5]);
    }

    /**
     * @covers ::__construct
     * @covers ::getMessageBank
     */
    public function testMessageBankCanBeRetrieved()
    {
        $message_bank_factory = new MessageBankFactory($this->config);
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Testing\MessageBank',
            $message_bank_factory->getMessageBank('test-queue')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getMessageBank
     */
    public function testMessageBankIsReused()
    {
        $message_bank_factory = new MessageBankFactory($this->config);
        $this->assertSame(
            $message_bank_factory->getMessageBank('test-queue'),
            $message_bank_factory->getMessageBank('test-queue')
        );
    }
}
