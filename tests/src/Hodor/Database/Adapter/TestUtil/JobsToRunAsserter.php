<?php

namespace Hodor\Database\Adapter\TestUtil;

use Generator;
use Hodor\Database\Adapter\SuperqueuerInterface;
use PHPUnit_Framework_TestCase;

class JobsToRunAsserter
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $test_case;

    /**
     * @param PHPUnit_Framework_TestCase $test_case
     */
    public function __construct(PHPUnit_Framework_TestCase $test_case)
    {
        $this->test_case = $test_case;
    }

    /**
     * @param SuperqueuerInterface $database
     * @param string $uniqid
     * @param $expected_jobs
     * @return array
     */
    public function assertJobsToRun(SuperqueuerInterface $database, $uniqid, $expected_jobs)
    {
        if (empty($expected_jobs)) {
            foreach ($database->getJobsToRunGenerator() as $actual_job) {
                $this->test_case->fail();
            }
            $this->test_case->assertSame([], []);
            return [];
        }

        $actual_jobs = [];
        foreach ($database->getJobsToRunGenerator() as $actual_job) {
            $expected_job = $this->getNextExpectedJob($expected_jobs, $actual_jobs, $actual_job);

            $this->test_case->assertSame("job-{$uniqid}-{$expected_job}", $actual_job['job_name']);
        }
        $this->assertNoMoreExpectedJobs($expected_jobs);

        return $actual_jobs;
    }

    /**
     * @param $expected_jobs
     * @param array $actual_jobs
     * @param $actual_job
     * @return mixed
     */
    private function getNextExpectedJob($expected_jobs, array & $actual_jobs, $actual_job)
    {
        if ($expected_jobs instanceof Generator) {
            $expected_job = $expected_jobs->current();
            $expected_jobs->next();
            return $expected_job;
        }

        $expected_job = array_shift($expected_jobs);
        $actual_jobs[] = $actual_job;
        return $expected_job;
    }

    /**
     * @param $expected_jobs
     */
    private function assertNoMoreExpectedJobs($expected_jobs)
    {
        if ($expected_jobs instanceof Generator) {
            $this->test_case->assertNull($expected_jobs->current());
            return;
        }

        $this->test_case->assertEmpty($expected_jobs);
    }
}
