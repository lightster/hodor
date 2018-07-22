<?php

namespace Hodor\JobQueue;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\QueueManager
 */
class QueueManagerTest extends TestCase
{
    /**
     * @var QueueManager
     */
    private $queue_manager;

    public function setUp()
    {
        parent::setUp();

        $this->queue_manager = new QueueManager(new Config(__FILE__, [
            'superqueue' => ['database' => ['type' => 'testing']],
        ]));
    }

    /**
     * @covers ::__construct
     * @covers ::getSuperqueue
     * @covers ::<private>
     */
    public function testSuperqueueIsReused()
    {
        $this->assertSame(
            $this->queue_manager->getSuperqueue(),
            $this->queue_manager->getSuperqueue()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferQueueFactory
     * @covers ::<private>
     */
    public function testBufferQueueFactoryIsReused()
    {
        $this->assertSame(
            $this->queue_manager->getBufferQueueFactory(),
            $this->queue_manager->getBufferQueueFactory()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getWorkerQueueFactory
     * @covers ::<private>
     */
    public function testWorkerQueueFactoryIsReused()
    {
        $this->assertSame(
            $this->queue_manager->getWorkerQueueFactory(),
            $this->queue_manager->getWorkerQueueFactory()
        );
    }
}
