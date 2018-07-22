<?php

namespace Hodor\Database\Adapter;

use PHPUnit\Framework\TestCase;

abstract class FactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getBufferWorker
     * @covers ::getSuperqueuer
     * @covers ::getDequeuer
     * @covers ::<private>
     */
    public function testFactoryGeneratesWorkingObjects()
    {
        $factory = $this->getTestFactory();

        $buffer_worker = $factory->getBufferWorker();
        $superqueuer = $factory->getSuperqueuer();
        $dequeuer = $factory->getDequeuer();

        $uniqid = uniqid();

        $buffer_worker->bufferJob('fast_jobs', $this->getJob([
            'name'    => "job-{$uniqid}-1",
            'options' => ['mutex_id' => "mutex-{$uniqid}"],
        ]));
        $buffer_worker->bufferJob('fast_jobs', $this->getJob([
            'name'    => "job-{$uniqid}-2",
            'options' => ['mutex_id' => "mutex-{$uniqid}"],
        ]));

        $job_to_finish = null;
        foreach ($superqueuer->getJobsToRunGenerator() as $job) {
            $this->assertSame("job-{$uniqid}-1", $job['job_name']);
            $superqueuer->markJobAsQueued($job);
            $job_to_finish = $job;
            $job_to_finish['started_running_at'] = date('c');
        }

        foreach ($superqueuer->getJobsToRunGenerator() as $job) {
            $this->fail('There should be no jobs that can be queued since the mutex is in use.');
        }

        $dequeuer->markJobAsSuccessful($job_to_finish);

        foreach ($superqueuer->getJobsToRunGenerator() as $job) {
            $this->assertSame("job-{$uniqid}-2", $job['job_name']);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getBufferWorker
     * @covers ::<private>
     */
    public function testBufferWorkerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame($factory->getBufferWorker(), $factory->getBufferWorker());
    }

    /**
     * @covers ::__construct
     * @covers ::getSuperqueuer
     * @covers ::<private>
     */
    public function testSuperqueuerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame($factory->getSuperqueuer(), $factory->getSuperqueuer());
    }

    /**
     * @covers ::__construct
     * @covers ::getDequeuer
     * @covers ::<private>
     */
    public function testDequeuerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame($factory->getDequeuer(), $factory->getDequeuer());
    }

    /**
     * @return FactoryInterface
     */
    abstract protected function getTestFactory();

    /**
     * @param array $job
     * @return array
     */
    private function getJob(array $job)
    {
        return array_replace_recursive([
            'params' => [],
            'meta' => [
                'buffered_at'   => date('c'),
                'buffered_from' => gethostname(),
            ],
        ], $job);
    }
}
