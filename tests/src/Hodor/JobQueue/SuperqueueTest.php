<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\JobQueue\TestUtil\TestingQueueProvisioner;
use Hodor\MessageQueue\Adapter\Testing\Config as TestingConfig;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\Superqueue
 */
class SuperqueueTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Superqueue
     */
    private $superqueue;

    public function setUp()
    {
        parent::setUp();

        $config = new TestingConfig([]);
        $config->addQueueConfig('worker-default-worker', ['workers_per_server' => 5]);

        $test_util = new TestingQueueProvisioner($config);

        $this->database = $test_util->getDatabase();
        $this->superqueue = $test_util->getSuperqueue();
    }

    /**
     * @covers ::__construct
     * @covers ::requestProcessLock
     * @covers ::<private>
     */
    public function testAdvisoryLockCanBeAcquired()
    {
        $this->assertTrue($this->superqueue->requestProcessLock());
        $this->assertFalse($this->database->requestAdvisoryLock(2, 'superqueuer', 'default'));
        $this->assertTrue($this->superqueue->requestProcessLock());
    }
}
