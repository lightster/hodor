<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Exception;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\MessageBank
 */
class MessageBankTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getMaxMessagesPerConsume
     */
    public function testMaxMessagesPerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_messages = rand(1, 100);

        $message_bank = new MessageBank([
            'max_messages_per_consume' => $max_messages,
        ]);

        $this->assertEquals($max_messages, $message_bank->getMaxMessagesPerConsume());
    }

    /**
     * @covers ::__construct
     * @covers ::getMaxMessagesPerConsume
     */
    public function testMaxMessagesPerConsumePassedToConstructorCanBeDefaulted()
    {
        $message_bank = new MessageBank();

        $this->assertEquals(1, $message_bank->getMaxMessagesPerConsume());
    }

    /**
     * @covers ::__construct
     * @covers ::getMaxTimePerConsume
     */
    public function testMaxTimePerConsumePassedToConstructorIsTheSameRetrieved()
    {
        $max_time = rand(1, 100);

        $message_bank = new MessageBank([
            'max_time_per_consume' => $max_time,
        ]);

        $this->assertEquals($max_time, $message_bank->getMaxTimePerConsume());
    }

    /**
     * @covers ::__construct
     * @covers ::getMaxTimePerConsume
     */
    public function testMaxTimePerConsumePassedToConstructorCanBeDefaulted()
    {
        $message_bank = new MessageBank();

        $this->assertEquals(600, $message_bank->getMaxTimePerConsume());
    }

    /**
     * @covers ::consumeMessage
     * @expectedException \Hodor\MessageQueue\Adapter\Testing\Exception\EmptyQueueException
     */
    public function testConsumingWhileNoMessagesAreQueuedThrowsAnException()
    {
        $message_bank = new MessageBank();

        $message_bank->consumeMessage();
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     */
    public function testProducedMessageCanBeConsumed()
    {
        $message_bank = new MessageBank();

        $message = uniqid();

        $message_bank->produceMessage($message);
        $this->assertSame($message, $message_bank->consumeMessage()->getContent());
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     */
    public function testMultipleProducedMessagesCanBeConsumed()
    {
        $message_bank = new MessageBank();

        $messages = [
            'a-' . uniqid(),
            'b-' . uniqid(),
        ];
        foreach ($messages as $message) {
            $message_bank->produceMessage($message);
        }

        foreach ($messages as $message) {
            $this->assertSame($message, $message_bank->consumeMessage()->getContent());
        }
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     * @expectedException \Hodor\MessageQueue\Adapter\Testing\Exception\EmptyQueueException
     */
    public function testConsumingWhileNoUnreceivedMessagesAreQueuedThrowsAnException()
    {
        $message_bank = new MessageBank();

        $message_bank->produceMessage('does-not-matter');
        $message_bank->consumeMessage();
        $message_bank->consumeMessage();
    }

    /**
     * @covers ::acknowledgeMessage
     * @expectedException Exception
     */
    public function testAnUnknownMessageCannotBeAcknowledged()
    {
        $message_bank = new MessageBank();

        $message_bank->acknowledgeMessage('unknown');
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     * @covers ::acknowledgeMessage
     * @covers ::emulateReconnect
     */
    public function testOnlyAckedMessagesComeBackOnReconnect()
    {
        $message_bank = new MessageBank();

        $acked_message = 'a-' . uniqid();
        $unacked_message = 'b-' . uniqid();

        $message_bank->produceMessage($acked_message);
        $message_bank->produceMessage($unacked_message);

        $consumed_ack_message = $message_bank->consumeMessage();
        $this->assertSame($acked_message, $consumed_ack_message->getContent());
        $consumed_ack_message->acknowledge();

        $this->assertSame($unacked_message, $message_bank->consumeMessage()->getContent());

        $message_bank->emulateReconnect();

        $this->assertSame($unacked_message, $message_bank->consumeMessage()->getContent());
    }
}
