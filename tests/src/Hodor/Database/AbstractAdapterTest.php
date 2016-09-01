<?php

namespace Hodor\Database;

use Hodor\Database\Adapter\TestUtil\JobsToRunAsserter;
use PHPUnit_Framework_TestCase;
use Traversable;

abstract class AbstractAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    public function tearDown()
    {
        $this->adapter = null;
    }

    /**
     * @covers ::__construct
     * @covers ::bufferJob
     * @covers ::getJobsToRunGenerator
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\BufferWorker::__construct
     * @covers Hodor\Database\Adapter\Postgres\BufferWorker::__construct
     * @covers Hodor\Database\Adapter\Testing\BufferWorker::bufferJob
     * @covers Hodor\Database\Adapter\Postgres\BufferWorker::bufferJob
     * @covers Hodor\Database\Adapter\Testing\BufferWorker::<private>
     * @covers Hodor\Database\Adapter\Postgres\BufferWorker::<private>
     * @param array $buffered_jobs
     * @param array $expected_jobs
     * @dataProvider provideBufferJobsScenarios
     */
    public function testJobsCanBeBuffered(array $buffered_jobs, array $expected_jobs)
    {
        $uniqid = uniqid();
        $this->bufferJobs($uniqid, $buffered_jobs);

        $this->assertJobsToRun($uniqid, $expected_jobs);
    }

    /**
     * @covers ::__construct
     * @covers ::markJobAsQueued
     * @covers ::getJobsToRunGenerator
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::getJobsToRunGenerator
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::getJobsToRunGenerator
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::markJobAsQueued
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::markJobAsQueued
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::<private>
     * @param array $buffered_jobs
     * @param array $queued_jobs
     * @param array $expected_jobs
     * @dataProvider provideQueueJobsScenarios
     */
    public function testJobsCanBeQueued(array $buffered_jobs, array $queued_jobs, array $expected_jobs)
    {
        $uniqid = uniqid();
        $this->queueJobs($uniqid, $queued_jobs);
        $this->bufferJobs($uniqid, $buffered_jobs);

        $this->assertJobsToRun($uniqid, $expected_jobs);
    }

    /**
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::markJobAsSuccessful
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::markJobAsSuccessful
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::<private>
     */
    public function testJobCanBeMarkedAsSuccessful()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->getAdapter()->markJobAsSuccessful($meta);
        });
    }

    /**
     * @covers ::markJobAsFailed
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::markJobAsFailed
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::markJobAsFailed
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::<private>
     */
    public function testJobCanBeMarkedAsFailed()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->getAdapter()->markJobAsFailed($meta);
        });
    }

    /**
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::markJobAsSuccessful
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::markJobAsSuccessful
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsSuccessfulTriggersAnException()
    {
        $this->getAdapter()->markJobAsSuccessful(['buffered_job_id' => -1]);
    }

    /**
     * @covers ::markJobAsFailed
     * @covers ::<private>
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::markJobAsFailed
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::markJobAsFailed
     * @covers Hodor\Database\Adapter\Testing\Dequeuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Dequeuer::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsFailedTriggersAnException()
    {
        $this->getAdapter()->markJobAsFailed(['buffered_job_id' => -1]);
    }

    /**
     * @covers ::beginTransaction
     * @covers ::commitTransaction
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::getJobsToRunGenerator
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::getJobsToRunGenerator
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::beginBatch
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::beginBatch
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::markJobAsQueued
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::markJobAsQueued
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::publishBatch
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::publishBatch
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::<private>
     * @covers Hodor\Database\PgsqlAdapter::queryMultiple
     * @covers Hodor\Database\PgsqlAdapter::beginTransaction
     * @covers Hodor\Database\PgsqlAdapter::commitTransaction
     */
    public function testQueueingJobsCanBeBatched()
    {
        $uniqid = uniqid();
        $this->bufferJobs($uniqid, [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ]);

        $current_connection = $this->getAdapter();
        $other_connection = $this->generateAdapter();

        $current_connection->beginTransaction();

        $jobs_to_run = $this->assertJobsToRun($uniqid, ['1'], $current_connection);
        $this->assertJobsToRun($uniqid, ['1'], $other_connection);

        $this->markJobsAsQueued($jobs_to_run);

        $this->assertJobsToRun($uniqid, [], $current_connection);
        $this->assertJobsToRun($uniqid, ['1'], $other_connection);

        $current_connection->commitTransaction();

        $this->assertJobsToRun($uniqid, [], $current_connection);
        $this->assertJobsToRun($uniqid, [], $other_connection);
    }

    /**
     * @covers ::requestAdvisoryLock
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::__construct
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::__destruct
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::requestAdvisoryLock
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::requestAdvisoryLock
     * @covers Hodor\Database\Adapter\Testing\Superqueuer::<private>
     * @covers Hodor\Database\Adapter\Postgres\Superqueuer::<private>
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
    public function provideBufferJobsScenarios()
    {
        return require __DIR__ . '/AbstractAdapter.buffer-jobs.dataset.php';
    }

    /**
     * @return array
     */
    public function provideQueueJobsScenarios()
    {
        return require __DIR__ . '/AbstractAdapter.queue-jobs.dataset.php';
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
        $uniqid = uniqid();
        $this->bufferJobs($uniqid, [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ]);

        $this->assertJobsToRun($uniqid, ['1']);

        $jobs_to_complete = $this->markJobsAsQueued($this->getAdapter()->getJobsToRunGenerator());

        $this->assertJobsToRun($uniqid, []);

        foreach ($jobs_to_complete as $job) {
            call_user_func($mark_job_completed, [
                'buffered_job_id'    => $job['buffered_job_id'],
                'started_running_at' => date('c'),
            ]);
        }

        $this->assertJobsToRun($uniqid, ['2']);
    }

    /**
     * @param string $uniqid
     * @param array $jobs
     */
    private function bufferJobs($uniqid, array $jobs)
    {
        $buffered_at = date('c', time() - 3600);

        foreach ($jobs as $job) {
            $options = [];
            if (isset($job['run_after'])) {
                $options['run_after'] = date('c', time() + $job['run_after']);
            }
            if (isset($job['job_rank'])) {
                $options['job_rank'] = $job['job_rank'];
            }
            if (isset($job['mutex_id'])) {
                $options['mutex_id'] = "mutex-{$uniqid}-{$job['mutex_id']}";
            }

            $this->getAdapter()->bufferJob(
                'fast-jobs',
                [
                    'name'   => "job-{$uniqid}-{$job['name']}",
                    'params' => [
                        'value' => $uniqid,
                    ],
                    'options' => $options,
                    'meta'   => [
                        'buffered_at'   => $buffered_at,
                        'buffered_from' => "host-{$uniqid}-{$job['name']}",
                    ],
                ]
            );
        }
    }

    /**
     * @param string $uniqid
     * @param array $jobs
     * @return array
     */
    private function queueJobs($uniqid, array $jobs)
    {
        $this->bufferJobs($uniqid, $jobs);
        return $this->markJobsAsQueued($this->getAdapter()->getJobsToRunGenerator());
    }

    /**
     * @param Traversable $jobs
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

    /**
     * @param string $uniqid
     * @param array $expected_jobs
     * @param AdapterInterface $db_adapter
     * @return array
     */
    private function assertJobsToRun($uniqid, array $expected_jobs, AdapterInterface $db_adapter = null)
    {
        if (!$db_adapter) {
            $db_adapter = $this->getAdapter();
        }

        $superqueuer_db = $db_adapter->getAdapterFactory()->getSuperqueuer();

        $asserter = new JobsToRunAsserter($this);

        return $asserter->assertJobsToRun($superqueuer_db, $uniqid, $expected_jobs);
    }
}
