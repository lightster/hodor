<?php

namespace Hodor\Database\Adapter\TestUtil;

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
     * @param string $uniqid
     * @param array $expected_jobs
     * @param SuperqueuerInterface $database
     * @return array
     */
    public function assertJobsToRun(SuperqueuerInterface $database, $uniqid, array $expected_jobs)
    {
        $actual_jobs = [];
        foreach ($database->getJobsToRunGenerator() as $actual_job) {
            $actual_jobs[] = $actual_job;
        }

        if (empty($expected_jobs)) {
            $this->test_case->assertSame($expected_jobs, $actual_jobs);
            return [];
        }

        foreach ($actual_jobs as $actual_job) {
            $expected_job = array_shift($expected_jobs);

            $this->test_case->assertSame("job-{$uniqid}-{$expected_job}", $actual_job['job_name']);
        }
        $this->test_case->assertEmpty($expected_jobs);

        return $actual_jobs;
    }
}
