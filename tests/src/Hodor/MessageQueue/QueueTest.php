<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\Consumer;
use Hodor\MessageQueue\Adapter\Testing\MessageBank;
use Hodor\MessageQueue\Adapter\Testing\Producer;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Queue
 */
class QueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::consume
     */
    public function testMessageCanBeConsumed()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $expected = ['name' => __METHOD__, 'number' => 1];

        $message_bank->produceMessage(json_encode($expected));
        $queue->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::consume
     */
    public function testMaxMessagesPerConsumeIsRespected()
    {
        $message_bank = new MessageBank(['max_messages_per_consume' => 5]);
        $queue = $this->getQueue($message_bank);

        for ($i = 1; $i <= 6; $i++) {
            $message_bank->produceMessage(json_encode($i));
        }

        $sum = 0;
        $queue->consume(
            function (IncomingMessage $message) use (&$sum) {
                $sum += $message->getContent();
            }
        );

        $this->assertSame(1 + 2 + 3 + 4 + 5, $sum);
    }

    /**
     * @covers ::__construct
     * @covers ::consume
     */
    public function testTimePerConsumeIsRespected()
    {
        $message_bank = new MessageBank(['max_time_per_consume' => 1, 'max_messages_per_consume' => 5]);
        $queue = $this->getQueue($message_bank);

        for ($i = 1; $i <= 3; $i++) {
            $message_bank->produceMessage(json_encode($i));
        }

        $sum = 0;
        $queue->consume(
            function (IncomingMessage $message) use (&$sum) {
                sleep(2);
                $sum += $message->getContent();
            }
        );

        $this->assertSame(1, $sum);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @depends testMessageCanBeConsumed
     */
    public function testMessagesCanBeProducedOutsideOfBatch()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $expected = ['name' => __METHOD__, 'number' => 1];

        $queue->push($expected);
        $queue->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::beginBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testMessagesPushedInsideOfBatchAreNotImmediatelyProduced()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $queue->push($i);
        }

        $queue->consume(function () {
            $this->fail('A message should not be consumed');
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::beginBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testStartingABatchWhileABatchIsAlreadyStartedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);
        $queue->beginBatch();
        $queue->beginBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @depends testMessageCanBeConsumed
     */
    public function testMessagesPushedInsideOfBatchAreAvailableAfterBatchIsPublished()
    {
        $message_bank = new MessageBank(['max_messages_per_consume' => 2]);
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $queue->push($i);
        }
        $queue->publishBatch();

        $count = 0;
        $queue->consume(function (IncomingMessage $message) use (&$count) {
            ++$count;
            $message->acknowledge();
        });

        $this->assertSame(2, $count);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testNoMessagesAreLeftToProduceAfterBatchIsPublished()
    {
        $message_bank = new MessageBank(['max_messages_per_consume' => 2]);
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $queue->push($i);
        }
        $queue->publishBatch();

        $queue->consume(function (IncomingMessage $message) {
            $message->acknowledge();
        });

        $queue->consume(function () {
        });
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->publishBatch();
        $queue->publishBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testANewBatchCanBeStartedAfterABatchHasAlreadyBeenPublished()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->publishBatch();
        $queue->beginBatch();

        $queue->push(1);
        $queue->consume(function () {
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testBatchedMessagesCanBeDiscarded()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->push(1);
        $queue->discardBatch();

        $queue->consume(function () {
            $this->fail('Discarded messages should not be available for consumption.');
        });
    }

    /**
     * @covers ::__construct
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testDiscardingABatchThatWasNeverStartedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->discardBatch();
        $queue->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->publishBatch();
        $queue->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->publishBatch();
        $queue->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @depends testMessageCanBeConsumed
     * @expectedException Exception
     */
    public function testANewBatchCanBeStartedAfterABatchHasAlreadyBeenDiscarded()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $queue->beginBatch();
        $queue->discardBatch();
        $queue->beginBatch();

        $queue->push(1);
        $queue->consume(function () {
        });
    }

    /**
     * @covers ::__construct
     * @covers ::publishMessage
     * @depends testMessageCanBeConsumed
     */
    public function testIndividualMessageCanBePublished()
    {
        $message_bank = new MessageBank();
        $queue = $this->getQueue($message_bank);

        $expected = ['name' => __METHOD__, 'number' => 1];

        $queue->publishMessage($expected);
        $queue->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::publishMessageBatch
     * @depends testMessageCanBeConsumed
     */
    public function testMessageBatchesCanBePublished()
    {
        $message_bank = new MessageBank(['max_messages_per_consume' => 2]);
        $queue = $this->getQueue($message_bank);

        $queue->publishMessageBatch([1, 2]);

        $count = 0;
        $queue->consume(function (IncomingMessage $message) use (&$count) {
            ++$count;
            $message->acknowledge();
        });

        $this->assertSame(2, $count);
    }

    /**
     * @param MessageBank $message_bank
     * @return Queue
     */
    private function getQueue(MessageBank $message_bank)
    {
        $consumer = new Consumer($message_bank);
        $producer = new Producer($message_bank);

        return new Queue($consumer, $producer);
    }
}
