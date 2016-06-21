<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use Hodor\MessageQueue\Adapter\Testing\Config;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\QueueFactory
 */
class QueueFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     */
    public function testQueueCanBeGenerated()
    {
        $this->assertInstanceOf(
            '\Hodor\MessageQueue\Queue',
            $this->generateQueueFactory()->getQueue('fast_jobs')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::<private>
     */
    public function testQueueIsReusedIfReferredToMultipleTimes()
    {
        $queue_factory = $this->generateQueueFactory();

        $this->assertSame(
            $queue_factory->getQueue('fast_jobs'),
            $queue_factory->getQueue('fast_jobs')
        );
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
        $queue_factory = $this->generateQueueFactory();

        $queue_factory->beginBatch();
        $queue = $queue_factory->getQueue('fast_jobs');

        $queue->push('');
        $queue->consume(function () {
            $this->fail('No messages should have been pushed.');
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     */
    public function testPushingMessagesToAQueueRetrievedBeforeStartingABatchAreAvailableAfterPublishingTheBatch()
    {
        $expected = __METHOD__;

        $queue_factory = $this->generateQueueFactory();

        $queue = $queue_factory->getQueue('fast_jobs');
        $queue_factory->beginBatch();
        $queue->push($expected);
        $queue_factory->publishBatch();

        $queue->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::<private>
     */
    public function testPushingMessagesToAQueueRetrievedAfterStartingABatchAreAvailableAfterPublishingTheBatch()
    {
        $expected = __METHOD__;

        $queue_factory = $this->generateQueueFactory();

        $queue_factory->beginBatch();
        $queue = $queue_factory->getQueue('fast_jobs');
        $queue->push($expected);
        $queue_factory->publishBatch();

        $queue->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
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
        $expected = __METHOD__;

        $queue_factory = $this->generateQueueFactory();

        $queue_factory->beginBatch();
        $queue = $queue_factory->getQueue('fast_jobs');
        $queue->push($expected);
        $queue_factory->discardBatch();

        $queue->consume(function () {
            $this->fail('All messages should have been discarded.');
        });
    }

    /**
     * @return QueueFactory
     */
    private function generateQueueFactory()
    {
        $config_provider = new ConfigProvider($this);

        $config = new Config(function (Config $config) {
            return new Factory($config);
        });
        $config->addQueueConfig('fast_jobs', $config_provider->getQueueConfig());
        $config->addQueueConfig('slow_jobs', $config_provider->getQueueConfig());

        return new QueueFactory($config);
    }
}
