<?php

namespace Hodor\MessageQueue;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\IncomingMessage
 */
class IncomingMessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentCanBeRetrieved()
    {
        $uniq_id = uniqid();

        $message_interface = $this->getMock('Hodor\MessageQueue\Adapter\MessageInterface');
        $message_interface
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($uniq_id));

        $message = new IncomingMessage($message_interface);
        $this->assertSame($uniq_id, $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentIsLazyLoaded()
    {
        $uniq_id = uniqid();

        $message_interface = $this->getMock('Hodor\MessageQueue\Adapter\MessageInterface');
        $message_interface
            ->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($uniq_id));

        $message = new IncomingMessage($message_interface);
        $this->assertSame($uniq_id, $message->getContent());
        $this->assertSame($uniq_id, $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::acknowledge
     */
    public function testMessageCanBeAcknowledged()
    {
        $message_interface = $this->getMock('Hodor\MessageQueue\Adapter\MessageInterface');
        $message_interface
            ->expects($this->once())
            ->method('acknowledge');

        $message = new IncomingMessage($message_interface);
        $message->acknowledge();
    }

    /**
     * @covers ::__construct
     * @covers ::acknowledge
     */
    public function testMessageIsAcknowledgedAtMostOnce()
    {
        $message_interface = $this->getMock('Hodor\MessageQueue\Adapter\MessageInterface');
        $message_interface
            ->expects($this->once())
            ->method('acknowledge');

        $message = new IncomingMessage($message_interface);
        $message->acknowledge();
        $message->acknowledge();
    }
}
