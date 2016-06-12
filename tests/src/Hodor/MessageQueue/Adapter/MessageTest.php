<?php

namespace Hodor\MessageQueue\Adapter;

use PHPUnit_Framework_TestCase;

abstract class MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentCanBeRetrieved()
    {
        $expected_value = 'some_string';
        $message = $this->getBasicMessage($expected_value);

        $this->assertEquals($expected_value, $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testMessageContentCanBeRetrievedMultipleTimes()
    {
        $message = $this->getBasicMessage('some_other_string');

        $this->assertEquals($message->getContent(), $message->getContent());
    }

    /**
     * @covers ::__construct
     * @covers ::acknowledge
     */
    public function testMessageIsAcknowledgedWhenMessageAdapterIsAcknowledged()
    {
        $message = $this->getAcknowledgeableMessage();

        $message->acknowledge();
    }

    /**
     * @param string $body
     * @return MessageInterface
     */
    abstract protected function getBasicMessage($body);

    /**
     * @return MessageInterface
     */
    abstract protected function getAcknowledgeableMessage();
}
