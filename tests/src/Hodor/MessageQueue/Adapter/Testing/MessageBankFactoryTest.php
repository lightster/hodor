<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\MessageBankFactory
 */
class MessageBankFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBankFactory
     */
    private $message_bank_factory;

    public function setUp()
    {
        $config = new Config(function () {});
        $config->addQueueConfig('test-queue', ['workers_per_server' => 5]);

        $this->message_bank_factory = new MessageBankFactory();
        $this->message_bank_factory->setConfig($config);
    }

    /**
     * @covers ::getConfig
     * @covers ::setConfig
     * @covers ::getMessageBank
     */
    public function testMessageBankCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Testing\MessageBank',
            $this->message_bank_factory->getMessageBank('test-queue')
        );
    }

    /**
     * @covers ::getConfig
     * @covers ::setConfig
     * @covers ::getMessageBank
     */
    public function testMessageBankIsReused()
    {
        $this->assertSame(
            $this->message_bank_factory->getMessageBank('test-queue'),
            $this->message_bank_factory->getMessageBank('test-queue')
        );
    }

    /**
     * @covers ::getConfig
     * @expectedException Exception
     */
    public function testConfigCannotBeRetrievedIfItIsNotSet()
    {
        $message_bank_factory = new MessageBankFactory();
        $message_bank_factory->getConfig();
    }
}
