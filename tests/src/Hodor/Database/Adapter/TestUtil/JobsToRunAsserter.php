<?php

namespace Hodor\Database\Adapter\TestUtil;

use Generator;
use Hodor\Database\Adapter\SuperqueuerInterface;
use PHPUnit\Framework\TestCase;

class JobsToRunAsserter
{
    /**
     * @var TestCase
     */
    private $test_case;

    /**
     * @param TestCase $test_case
     */
    public function __construct(TestCase $test_case)
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
            if ($expected_jobs instanceof Generator) {
                $expected_job = $expected_jobs->current();
                $expected_jobs->next();
                $this->test_case->assertSame("job-{$uniqid}-{$expected_job}", $actual_job['job_name']);
                continue;
            }

            $expected_job = array_shift($expected_jobs);
            $actual_jobs[] = $actual_job;
            $this->test_case->assertSame("job-{$uniqid}-{$expected_job}", $actual_job['job_name']);
        }
        $this->assertNoMoreExpectedJobs($expected_jobs);

        return $actual_jobs;
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
