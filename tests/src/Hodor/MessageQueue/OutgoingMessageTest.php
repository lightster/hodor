<?php

namespace Hodor\MessageQueue;

use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @coversDefaultClass Hodor\MessageQueue\OutgoingMessage
 */
class OutgoingMessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getEncodedContent
     */
    public function testEncodedContentCanBeRetrieved()
    {
        $content = ['name' => 'Hey', 'id' => 123, 'active' => true];

        $message = new OutgoingMessage($content);
        $this->assertSame(
            json_encode($content),
            $message->getEncodedContent()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getEncodedContent
     */
    public function testEncodedContentCanBeRetrievedMultipleTimes()
    {
        $content = ['name' => 'Hey', 'id' => 123, 'active' => true];

        $message = new OutgoingMessage($content);
        $this->assertSame(
            $message->getEncodedContent(),
            $message->getEncodedContent()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getEncodedContent
     * @expectedException RuntimeException
     */
    public function testOver100LevelsOfNestingThrowsAnException()
    {
        $message = 123;
        for ($i = 1; $i <= 101; ++$i) {
            $message = ['message' => $message];
        }

        $message = new OutgoingMessage($message);
        $message->getEncodedContent();
    }
}
