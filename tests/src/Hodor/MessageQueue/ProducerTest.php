<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Producer
 */
class ProducerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    private $adapter_factory;

    /**
     * @var Producer
     */
    private $producer;

    public function setUp()
    {
        parent::setUp();

        $config = new Config([]);
        $config->addQueueConfig('some-queue-name', []);
        $this->adapter_factory = new Factory($config);
        $this->producer = new Producer($this->adapter_factory);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testQueueingAMessageForANonExistentQueueThrowsAnException()
    {
        $this->producer->getQueue('non-existent-queue-name');
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     */
    public function testMessagesCanBeProducedOutsideOfBatch()
    {
        $expected = ['name' => __METHOD__, 'number' => 1];

        $this->producer->getQueue('some-queue-name')->push($expected);

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testMessagesPushedInsideOfBatchAreNotImmediatelyProduced()
    {
        $this->producer->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->producer->getQueue('some-queue-name')->push($i);
        }

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function () {
            $this->fail('A message should not be consumed');
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testStartingABatchWhileABatchIsAlreadyStartedThrowsAnException()
    {
        $this->producer->beginBatch();
        $this->producer->beginBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     */
    public function testMessagesPushedInsideOfBatchAreAvailableAfterBatchIsPublished()
    {
        $this->producer->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->producer->getQueue('some-queue-name')->push($i);
        }
        $this->producer->publishBatch();

        $count = 0;
        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function (IncomingMessage $message) use (&$count) {
            ++$count;
            $message->acknowledge();
        });
        $consumer->consumeMessage(function (IncomingMessage $message) use (&$count) {
            ++$count;
            $message->acknowledge();
        });

        $this->assertSame(2, $count);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testNoMessagesAreLeftToProduceAfterBatchIsPublished()
    {
        $this->producer->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->producer->getQueue('some-queue-name')->push($i);
        }
        $this->producer->publishBatch();

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function (IncomingMessage $message) {
            $message->acknowledge();
        });
        $consumer->consumeMessage(function (IncomingMessage $message) {
            $message->acknowledge();
        });

        $consumer->consumeMessage(function () {
        });
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $this->producer->beginBatch();
        $this->producer->publishBatch();
        $this->producer->publishBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testANewBatchCanBeStartedAfterABatchHasAlreadyBeenPublished()
    {
        $this->producer->beginBatch();
        $this->producer->publishBatch();
        $this->producer->beginBatch();

        $this->producer->getQueue('some-queue-name')->push(1);

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function () {
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testBatchedMessagesCanBeDiscarded()
    {
        $this->producer->beginBatch();
        $this->producer->getQueue('some-queue-name')->push(1);
        $this->producer->discardBatch();

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function () {
            $this->fail('Discarded messages should not be available for consumption.');
        });
    }

    /**
     * @covers ::__construct
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testDiscardingABatchThatWasNeverStartedThrowsAnException()
    {
        $this->producer->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $this->producer->beginBatch();
        $this->producer->discardBatch();
        $this->producer->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $this->producer->beginBatch();
        $this->producer->publishBatch();
        $this->producer->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $this->producer->beginBatch();
        $this->producer->publishBatch();
        $this->producer->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testANewBatchCanBeStartedAfterABatchHasAlreadyBeenDiscarded()
    {
        $this->producer->beginBatch();
        $this->producer->discardBatch();
        $this->producer->beginBatch();

        $this->producer->getQueue('some-queue-name')->push(1);

        $consumer = $this->adapter_factory->getConsumer('some-queue-name');
        $consumer->consumeMessage(function () {
        });
    }
}
