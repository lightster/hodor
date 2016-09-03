<?php

namespace Hodor\Database\Adapter;

use Hodor\Database\Adapter\TestUtil\AbstractProvisioner;
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
     * @var AbstractProvisioner
     */
    private $provisioner;

    public function setUp()
    {
        $this->asserter = new JobsToRunAsserter($this);
        $this->scenario_creator = new ScenarioCreator();

        $this->getProvisioner()->setUp();
    }

    public function tearDown()
    {
        $this->getProvisioner()->tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     */
    public function testJobCanBeMarkedAsSuccessful()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $adapter = $this->getProvisioner()->getAdapter();
            $adapter->getAdapterFactory()->getDequeuer()->markJobAsSuccessful($meta);
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
            $adapter = $this->getProvisioner()->getAdapter();
            $adapter->getAdapterFactory()->getDequeuer()->markJobAsFailed($meta);
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
        $adapter = $this->getProvisioner()->getAdapter();
        $adapter->getAdapterFactory()->getDequeuer()->markJobAsSuccessful([
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
        $adapter = $this->getProvisioner()->getAdapter();
        $adapter->getAdapterFactory()->getDequeuer()->markJobAsFailed([
            'buffered_job_id' => -1,
        ]);
    }

    /**
     * @return AbstractProvisioner
     */
    abstract protected function generateProvisioner();

    /**
     * @return AbstractProvisioner
     */
    protected function getProvisioner()
    {
        if ($this->provisioner) {
            return $this->provisioner;
        }

        $this->provisioner = $this->generateProvisioner();

        return $this->provisioner;
    }

    /**
     * @param callable $mark_job_completed
     */
    private function markJobsAsCompleted(callable $mark_job_completed)
    {
        $adapter = $this->getProvisioner()->getAdapter();
        $superqueuer = $adapter->getAdapterFactory()->getSuperqueuer();

        $scenario = $this->scenario_creator->createScenario($adapter,  [
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
        $adapter = $this->getProvisioner()->getAdapter();

        $jobs_queued = [];

        foreach ($jobs as $job) {
            $meta = $adapter->getAdapterFactory()->getSuperqueuer()->markJobAsQueued($job);
            $jobs_queued[] = $meta;
        }

        return $jobs_queued;
    }
}
