<?php

namespace Hodor\Database\Adapter;

use Hodor\Database\Adapter\TestUtil\JobsToRunAsserter;
use Hodor\Database\Adapter\TestUtil\ScenarioCreator;
use Hodor\Database\AdapterInterface;
use PHPUnit_Framework_TestCase;

abstract class SuperqueuerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JobsToRunAsserter
     */
    private $asserter;

    /**
     * @var ScenarioCreator
     */
    private $scenario_creator;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function setUp()
    {
        $this->asserter = new JobsToRunAsserter($this);
        $this->scenario_creator = new ScenarioCreator();
    }

    public function tearDown()
    {
        $this->adapter = null;
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsQueued
     * @covers ::getJobsToRunGenerator
     * @covers ::<private>
     * @param array $buffered_jobs
     * @param array $queued_jobs
     * @param array $expected_jobs
     * @dataProvider provideQueueJobsScenarios
     */
    public function testJobsCanBeQueued(array $buffered_jobs, array $queued_jobs, array $expected_jobs)
    {
        $superqueuer = $this->getAdapter()->getAdapterFactory()->getSuperqueuer();

        $scenario = $this->scenario_creator->createScenario($this->getAdapter(), $buffered_jobs, $queued_jobs);

        $this->asserter->assertJobsToRun($superqueuer, $scenario['uniqid'], $expected_jobs);
    }

    /**
     * @covers ::beginBatch
     * @covers ::publishBatch
     * @covers ::getJobsToRunGenerator
     */
    public function testQueueingJobsCanBeBatched()
    {
        $scenario = $this->scenario_creator->createScenario($this->getAdapter(),  [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ], []);
        $uniqid = $scenario['uniqid'];

        $current_connection = $this->getAdapter()->getAdapterFactory()->getSuperqueuer();
        $other_connection = $this->generateAdapter()->getAdapterFactory()->getSuperqueuer();

        $current_connection->beginBatch();

        $jobs_to_run = $this->asserter->assertJobsToRun($current_connection, $uniqid, ['1']);
        $this->asserter->assertJobsToRun($other_connection, $uniqid, ['1']);

        $this->markJobsAsQueued($jobs_to_run);

        $this->asserter->assertJobsToRun($current_connection, $uniqid, []);
        $this->asserter->assertJobsToRun($other_connection, $uniqid, ['1']);

        $current_connection->publishBatch();

        $this->asserter->assertJobsToRun($current_connection, $uniqid, []);
        $this->asserter->assertJobsToRun($other_connection, $uniqid, []);
    }

    /**
     * @covers ::requestAdvisoryLock
     */
    public function testAdvisoryLockCanBeAcquired()
    {
        $connections = [
            $this->generateAdapter(),
            $this->generateAdapter(),
            $this->generateAdapter(),
        ];

        $this->assertTrue($connections[0]->requestAdvisoryLock('test', 'lock'));
        $this->assertFalse($connections[1]->requestAdvisoryLock('test', 'lock'));

        unset($connections[0]);

        // without forcing garbage collection, the DB connections
        // are not guaranteed to be disconnected; force GC
        gc_collect_cycles();

        $this->assertTrue($connections[2]->requestAdvisoryLock('test', 'lock'));
    }

    /**
     * @return array
     */
    public function provideQueueJobsScenarios()
    {
        return require __DIR__ . '/../AbstractAdapter.queue-jobs.dataset.php';
    }

    /**
     * @return AdapterInterface
     */
    abstract protected function generateAdapter();

    /**
     * @return AdapterInterface
     */
    protected function getAdapter()
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        $this->adapter = $this->generateAdapter();

        return $this->adapter;
    }

    /**
     * @param array|Traversable $jobs
     * @return array
     */
    private function markJobsAsQueued($jobs)
    {
        $jobs_queued = [];

        foreach ($jobs as $job) {
            $meta = $this->getAdapter()->markJobAsQueued($job);
            $jobs_queued[] = $meta;
        }

        return $jobs_queued;
    }
}
