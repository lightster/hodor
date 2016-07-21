<?php

namespace Hodor\Database;

use Exception;

use Hodor\Database\Phpmig\CommandWrapper;
use Hodor\Database\Phpmig\Container;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Traversable;

/**
 * @coversDefaultClass Hodor\Database\PgsqlAdapter
 */
class PgsqlAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PgsqlAdapter
     */
    private $pgsql_adapter;

    public function setUp()
    {
        $this->pgsql_adapter = $this->getPgsqlAdapter();

        $phpmig_container = new Container();
        $phpmig_container->addDefaultServices('no-config-file');
        $phpmig_container['hodor.database'] = $this->pgsql_adapter;

        $command_wrapper = new CommandWrapper($phpmig_container, new NullOutput());
        $command_wrapper->rollbackMigrations();
        $command_wrapper->runMigrations();

        while (iterator_to_array($this->pgsql_adapter->getJobsToRunGenerator())) {
            $this->queueJobs(null, []);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::bufferJob
     * @covers ::markJobAsQueued
     * @covers ::getJobsToRunGenerator
     * @covers ::<private>
     * @param array $buffered_jobs
     * @param array $queued_jobs
     * @param array $expected_jobs
     * @dataProvider provideSuperqueueScenarios
     */
    public function testJobsCanBeQueuedAndBuffered(array $buffered_jobs, array $queued_jobs, array $expected_jobs)
    {
        $uniqid = uniqid();
        $this->queueJobs($uniqid, $queued_jobs);
        $this->bufferJobs($uniqid, $buffered_jobs);

        $this->assertJobsToRun($uniqid, $expected_jobs);
    }

    /**
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     */
    public function testJobCanBeMarkedAsSuccessful()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->pgsql_adapter->markJobAsSuccessful($meta);
        });
    }

    /**
     * @covers ::markJobAsFailed
     * @covers ::<private>
     */
    public function testJobCanBeMarkedAsFailed()
    {
        $this->markJobsAsCompleted(function ($meta) {
            $this->pgsql_adapter->markJobAsFailed($meta);
        });
    }

    /**
     * @covers ::markJobAsSuccessful
     * @covers ::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsSuccessfulTriggersAnException()
    {
        $this->pgsql_adapter->markJobAsSuccessful(['buffered_job_id' => -1]);
    }

    /**
     * @covers ::markJobAsFailed
     * @covers ::<private>
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testMarkingUnrecognizedJobAsFailedTriggersAnException()
    {
        $this->pgsql_adapter->markJobAsFailed(['buffered_job_id' => -1]);
    }

    /**
     * @covers ::getPhpmigAdapter
     */
    public function testPhpmigAdapterCanBeRetrieved()
    {
        $this->assertInstanceOf(
            'Hodor\Database\Phpmig\PgsqlPhpmigAdapter',
            $this->pgsql_adapter->getPhpmigAdapter()
        );
    }

    /**
     * @covers ::beginTransaction
     * @covers ::rollbackTransaction
     * @covers ::queryMultiple
     */
    public function testTransactionCanBeRolledback()
    {
        $this->pgsql_adapter->beginTransaction();

        $uniqid = uniqid();
        $this->bufferJobs($uniqid, [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ]);

        $this->assertJobsToRun($uniqid, ['1']);

        $this->pgsql_adapter->rollbackTransaction();

        $this->assertJobsToRun($uniqid, []);
    }

    /**
     * @covers ::beginTransaction
     * @covers ::commitTransaction
     * @covers ::queryMultiple
     */
    public function testTransactionCanBeCommitted()
    {
        $this->pgsql_adapter->beginTransaction();

        $uniqid = uniqid();
        $this->bufferJobs($uniqid, [
            ['name' => 1, 'mutex_id' => 'a'],
            ['name' => 2, 'mutex_id' => 'a'],
        ]);

        $this->assertJobsToRun($uniqid, ['1']);

        $this->pgsql_adapter->commitTransaction();

        $this->assertJobsToRun($uniqid, ['1']);
    }

    /**
     * @covers ::requestAdvisoryLock
     */
    public function testAdvisoryLockCanBeAcquired()
    {
        $connections = [
            $this->getPgsqlAdapter(),
            $this->getPgsqlAdapter(),
            $this->getPgsqlAdapter(),
        ];

        $this->assertTrue($connections[0]->requestAdvisoryLock('test', 'lock'));
        $this->assertFalse($connections[1]->requestAdvisoryLock('test', 'lock'));

        unset($connections[0]);
        $this->assertTrue($connections[2]->requestAdvisoryLock('test', 'lock'));
    }

    /**
     * @return array
     */
    public function provideSuperqueueScenarios()
    {
        return require __DIR__ . '/PgsqlAdapter.superqueue-query.dataset.php';
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

        $jobs_to_complete = $this->markJobsAsQueued($this->pgsql_adapter->getJobsToRunGenerator());

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

            $this->pgsql_adapter->bufferJob(
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
        return $this->markJobsAsQueued($this->pgsql_adapter->getJobsToRunGenerator());
    }

    /**
     * @param Traversable $jobs
     * @return array
     */
    private function markJobsAsQueued($jobs)
    {
        $jobs_queued = [];

        foreach ($jobs as $job) {
            $this->pgsql_adapter->markJobAsQueued($job);
            $jobs_queued[] = $job;
        }

        return $jobs_queued;
    }

    /**
     * @param string $uniqid
     * @param array $expected_jobs
     */
    private function assertJobsToRun($uniqid, array $expected_jobs)
    {
        $actual_jobs = [];
        foreach ($this->pgsql_adapter->getJobsToRunGenerator() as $actual_job) {
            $actual_jobs[] = $actual_job;
        }

        if (empty($expected_jobs)) {
            $this->assertEmpty($actual_jobs);
            return;
        }

        foreach ($actual_jobs as $actual_job) {
            $expected_job = array_shift($expected_jobs);

            $this->assertSame("job-{$uniqid}-{$expected_job}", $actual_job['job_name']);
        }
        $this->assertEmpty($expected_jobs);
    }

    /**
     * @return PgsqlAdapter
     * @throws Exception
     */
    private function getPgsqlAdapter()
    {
        $config_path = __DIR__ . '/../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return new PgsqlAdapter($config['test']['db']['yo-pdo-pgsql']);
    }
}
