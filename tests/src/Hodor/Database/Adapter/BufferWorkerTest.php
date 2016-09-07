<?php

namespace Hodor\Database\Adapter;

use Hodor\Database\Adapter\TestUtil\AbstractProvisioner;
use Hodor\Database\Adapter\TestUtil\JobsToRunAsserter;
use Hodor\Database\Adapter\TestUtil\ScenarioCreator;
use PHPUnit_Framework_TestCase;

abstract class BufferWorkerTest extends PHPUnit_Framework_TestCase
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
     * @covers ::bufferJob
     * @covers ::<private>
     * @param array $buffered_jobs
     * @param array $expected_jobs
     * @dataProvider provideBufferJobsScenarios
     */
    public function testJobsCanBeBuffered(array $buffered_jobs, array $expected_jobs)
    {
        $adapter_factory = $this->getProvisioner()->getAdapterFactory();
        $superqueuer = $adapter_factory->getSuperqueuer();

        $scenario = $this->scenario_creator->createScenario($adapter_factory, $buffered_jobs, []);

        $this->asserter->assertJobsToRun($superqueuer, $scenario['uniqid'], $expected_jobs);
    }

    /**
     * @return array
     */
    public function provideBufferJobsScenarios()
    {
        return require __DIR__ . '/AbstractAdapter.buffer-jobs.dataset.php';
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
}
