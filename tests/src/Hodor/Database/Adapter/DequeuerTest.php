<?php

namespace Hodor\Database\Adapter;

use Hodor\Database\Adapter\TestUtil\JobsToRunAsserter;
use Hodor\Database\Adapter\TestUtil\ScenarioCreator;
use Hodor\Database\AdapterInterface;
use PHPUnit_Framework_TestCase;
use Traversable;

abstract class DequeuerTest extends PHPUnit_Framework_TestCase
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
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     */
    public function testJobCanBeMarkedAsSuccessful()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->getAdapter()->getAdapterFactory()->getDequeuer()->markJobAsSuccessful($meta);
        });
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsFailed
     * @covers ::<private>
     */
    public function testJobCanBeMarkedAsFailed()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->getAdapter()->getAdapterFactory()->getDequeuer()->markJobAsFailed($meta);
        });
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsSuccessfulTriggersAnException()
    {
        $this->getAdapter()->getAdapterFactory()->getDequeuer()->markJobAsSuccessful([
            'buffered_job_id' => -1,
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsFailed
     * @covers ::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsFailedTriggersAnException()
    {
        $this->getAdapter()->getAdapterFactory()->getDequeuer()->markJobAsFailed([
            'buffered_job_id' => -1,
        ]);
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
     * @param callable $mark_job_completed
     */
    private function markJobsAsCompleted(callable $mark_job_completed)
    {
        $superqueuer = $this->getAdapter()->getAdapterFactory()->getSuperqueuer();

        $scenario = $this->scenario_creator->createScenario($this->getAdapter(),  [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ], []);
        $uniqid = $scenario['uniqid'];

        $this->asserter->assertJobsToRun($superqueuer, $uniqid, ['1']);

        $jobs_to_complete = $this->markJobsAsQueued($superqueuer->getJobsToRunGenerator());

        $this->asserter->assertJobsToRun($superqueuer, $uniqid, []);

        foreach ($jobs_to_complete as $job) {
            call_user_func($mark_job_completed, [
                'buffered_job_id'    => $job['buffered_job_id'],
                'started_running_at' => date('c'),
            ]);
        }

        $this->asserter->assertJobsToRun($superqueuer, $uniqid, ['2']);
    }

    /**
     * @param array|Traversable $jobs
     * @return array
     */
    private function markJobsAsQueued($jobs)
    {
        $jobs_queued = [];

        foreach ($jobs as $job) {
            $meta = $this->getAdapter()->getAdapterFactory()->getSuperqueuer()->markJobAsQueued($job);
            $jobs_queued[] = $meta;
        }

        return $jobs_queued;
    }
}
