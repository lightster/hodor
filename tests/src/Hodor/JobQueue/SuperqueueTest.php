<?php

namespace Hodor\JobQueue;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\JobQueue\TestUtil\TestingQueueProvisioner;
use Hodor\MessageQueue\Adapter\Testing\Config as TestingConfig;
use Hodor\MessageQueue\Adapter\Testing\MessageBank;
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

    /**
     * @var MessageBank
     */
    private $message_bank;

    public function setUp()
    {
        parent::setUp();

        $config = new TestingConfig([]);
        $config->addQueueConfig('worker-default-worker', ['workers_per_server' => 5]);

        $test_util = new TestingQueueProvisioner($config);

        $this->database = $test_util->getDatabase();
        $this->superqueue = $test_util->getSuperqueue();
        $this->message_bank = $test_util->getMessageBank('worker-default-worker');
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

    /**
     * @covers ::__construct
     * @covers ::queueJobsFromDatabaseToWorkerQueue
     * @covers ::<private>
     */
    public function testEmptyBatchOfJobsCanBeQueued()
    {
        $this->assertJobsAreQueued(0);
    }

    /**
     * @covers ::__construct
     * @covers ::queueJobsFromDatabaseToWorkerQueue
     * @covers ::<private>
     */
    public function testPartialBatchOfJobsCanBeQueued()
    {
        $this->assertJobsAreQueued(2);
    }

    /**
     * @covers ::__construct
     * @covers ::queueJobsFromDatabaseToWorkerQueue
     * @covers ::<private>
     */
    public function testFullBatchOfJobsCanBeQueued()
    {
        $this->assertJobsAreQueued(253);
    }

    /**
     * @covers ::__construct
     * @covers ::queueJobsFromDatabaseToWorkerQueue
     * @covers ::<private>
     */
    public function testMultipleBatchesOfJobsCanBeQueued()
    {
        $this->assertJobsAreQueued(505);
    }

    /**
     * @param int $count
     */
    private function assertJobsAreQueued($count)
    {
        $uniqid = uniqid();

        for ($i = 1; $i <= $count; $i++) {
            $row = [
                'buffered_job_id' => "{$uniqid}-{$i}-id",
                'queue_name'      => 'default-worker',
                'job_name'        => "{$uniqid}-{$i}-name",
                'job_params'      => ["{$uniqid}-{$i}-params"],
                'run_after'       => date('c'),
                'job_rank'        => $i,
                'mutex_id'        => "{$uniqid}-{$i}-mutex",
            ];

            $this->database->insert('buffered_jobs', "{$uniqid}-{$i}-id", $row);
        }

        $this->superqueue->queueJobsFromDatabaseToWorkerQueue();

        $confirmed_messages = 0;
        for ($i = 1; $i <= $count; $i++) {
            $mq_message = json_decode($this->message_bank->consumeMessage()->getContent(), true);
            if ("{$uniqid}-{$i}-name" === $mq_message['name']
                && $this->database->has('queued_jobs', "{$uniqid}-{$i}-id")
            ) {
                ++$confirmed_messages;
            }
        }

        $this->assertSame($count, $confirmed_messages);
    }
}
