<?php

namespace Hodor\Database\Adapter;

use Hodor\Database\Adapter\TestUtil\JobsToRunAsserter;
use Hodor\Database\Adapter\TestUtil\ScenarioCreator;
use Hodor\Database\AdapterInterface;
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
     * @covers ::bufferJob
     * @covers ::<private>
     * @param array $buffered_jobs
     * @param array $expected_jobs
     * @dataProvider provideBufferJobsScenarios
     */
    public function testJobsCanBeBuffered(array $buffered_jobs, array $expected_jobs)
    {
        $superqueuer = $this->getAdapter()->getAdapterFactory()->getSuperqueuer();

        $scenario = $this->scenario_creator->createScenario($this->getAdapter(), $buffered_jobs, []);

        $this->asserter->assertJobsToRun($superqueuer, $scenario['uniqid'], $expected_jobs);
    }

    /**
     * @return array
     */
    public function provideBufferJobsScenarios()
    {
        return require __DIR__ . '/../AbstractAdapter.buffer-jobs.dataset.php';
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
}
