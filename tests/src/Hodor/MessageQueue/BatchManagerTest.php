<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\BatchManager
 */
class BatchManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    private $adapter_factory;

    /**
     * @var QueueFactory
     */
    private $queue_factory;

    /**
     * @var BatchManager
     */
    private $batch_manager;

    public function setUp()
    {
        parent::setUp();

        $config = new Config([]);
        $config->addQueueConfig('some-queue-name', []);
        $this->adapter_factory = new Factory($config);
        $this->queue_factory = new QueueFactory($this->adapter_factory);
        $this->batch_manager = new BatchManager($this->queue_factory);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testQueueingAMessageForANonExistentQueueThrowsAnException()
    {
        $this->batch_manager->getQueue('non-existent-queue-name');
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     */
    public function testMessagesCanBeProducedOutsideOfBatch()
    {
        $expected = ['name' => __METHOD__, 'number' => 1];

        $this->batch_manager->getQueue('some-queue-name')->push($expected);

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function (IncomingMessage $message) use ($expected) {
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
        $this->batch_manager->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->batch_manager->getQueue('some-queue-name')->push($i);
        }

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function () {
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
        $this->batch_manager->beginBatch();
        $this->batch_manager->beginBatch();
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
        $this->batch_manager->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->batch_manager->getQueue('some-queue-name')->push($i);
        }
        $this->batch_manager->publishBatch();

        $count = 0;
        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function (IncomingMessage $message) use (&$count) {
            ++$count;
            $message->acknowledge();
        });
        $queue->consume(function (IncomingMessage $message) use (&$count) {
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
        $this->batch_manager->beginBatch();
        for ($i = 1; $i <= 2; $i++) {
            $this->batch_manager->getQueue('some-queue-name')->push($i);
        }
        $this->batch_manager->publishBatch();

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function (IncomingMessage $message) {
            $message->acknowledge();
        });
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
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $this->batch_manager->beginBatch();
        $this->batch_manager->publishBatch();
        $this->batch_manager->publishBatch();
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
        $this->batch_manager->beginBatch();
        $this->batch_manager->publishBatch();
        $this->batch_manager->beginBatch();

        $this->batch_manager->getQueue('some-queue-name')->push(1);

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function () {
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
        $this->batch_manager->beginBatch();
        $this->batch_manager->getQueue('some-queue-name')->push(1);
        $this->batch_manager->discardBatch();

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function () {
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
        $this->batch_manager->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $this->batch_manager->beginBatch();
        $this->batch_manager->discardBatch();
        $this->batch_manager->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testDiscardingABatchAfterItWasAlreadyPublishedThrowsAnException()
    {
        $this->batch_manager->beginBatch();
        $this->batch_manager->publishBatch();
        $this->batch_manager->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testPublishingABatchAfterItWasAlreadyDiscardedThrowsAnException()
    {
        $this->batch_manager->beginBatch();
        $this->batch_manager->publishBatch();
        $this->batch_manager->discardBatch();
    }

    /**
     * @covers ::__construct
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testANewBatchCanBeStartedAfterABatchHasAlreadyBeenDiscarded()
    {
        $this->batch_manager->beginBatch();
        $this->batch_manager->discardBatch();
        $this->batch_manager->beginBatch();

        $this->batch_manager->getQueue('some-queue-name')->push(1);

        $queue = $this->queue_factory->getQueue('some-queue-name');
        $queue->consume(function () {
        });
    }
}
