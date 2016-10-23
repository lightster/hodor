<?php

namespace Hodor\JobQueue;

use Exception;
use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Exception\BufferedJobNotFoundException;
use Hodor\JobQueue\TestUtil\TestingWorkerQueueFactory;
use Hodor\MessageQueue\Adapter\Testing\Config as TestingConfig;
use Hodor\MessageQueue\Adapter\Testing\MessageBank;
use Hodor\MessageQueue\ConsumerQueue;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\ProducerQueue;
use PHPUnit_Framework_TestCase;
use UnexpectedValueException;

/**
 * @coversDefaultClass Hodor\JobQueue\WorkerQueue
 */
class WorkerQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBank
     */
    private $message_bank;

    /**
     * @var ConsumerQueue
     */
    private $consumer;

    /**
     * @var ProducerQueue
     */
    private $producer;

    /**
     * @var WorkerQueueFactory
     */
    private $worker_queue_factory;

    /**
     * @var WorkerQueue
     */
    private $worker_queue;

    /**
     * @var Database
     */
    private $database;

    public function setUp()
    {
        parent::setUp();

        $config = new TestingConfig([]);
        $config->addQueueConfig('worker-default-worker', ['workers_per_server' => 5]);

        $testing_worker_queue_factory = new TestingWorkerQueueFactory($config);

        $this->message_bank = $testing_worker_queue_factory->getMessageBank('default-worker');
        $this->consumer = $testing_worker_queue_factory->getConsumerQueue('default-worker');
        $this->producer = $testing_worker_queue_factory->getProducerQueue('default-worker');
        $this->message_bank = $testing_worker_queue_factory->getMessageBank('default-worker');
        $this->database = $testing_worker_queue_factory->getDatabase();
        $this->worker_queue_factory = $testing_worker_queue_factory->getWorkerQueueFactory();
        $this->worker_queue = $this->worker_queue_factory->getWorkerQueue('default-worker');
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testJobCanBeQueued()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'   => "some-job-{$uniqid}",
            'params' => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);
        $this->consumer->consume(function (IncomingMessage $message) use ($expected_job) {
            $received_job = $message->getContent();
            $this->assertEquals($expected_job, [
                'name'   => $received_job['name'],
                'params' => $received_job['params'],
                'meta'   => $received_job['meta'],
            ]);
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testBatchedJobIsPublishedIfAndOnlyIfBatchIsPublished()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'   => "some-job-{$uniqid}",
            'params' => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->worker_queue_factory->beginBatch();
        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);

        try {
            $this->consumer->consume(function () use ($expected_job) {
                $this->fail('A message should not be available for consuming until after batch is published.');
            });
        } catch (Exception $exception) {
            // the exception is expected. do nothing.
        }

        $this->worker_queue_factory->publishBatch();

        $this->consumer->consume(function (IncomingMessage $message) use ($expected_job) {
            $received_job = $message->getContent();
            $this->assertEquals($expected_job, [
                'name'   => $received_job['name'],
                'params' => $received_job['params'],
                'meta'   => $received_job['meta'],
            ]);
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException Exception
     */
    public function testBatchedJobIsDiscardedIfBatchIsDiscarded()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'   => "some-job-{$uniqid}",
            'params' => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->worker_queue_factory->beginBatch();
        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);
        $this->worker_queue_factory->discardBatch();

        $this->consumer->consume(function () {});
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testJobNameAndParamsArePassedToJobRunner()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->database->insert('queued_jobs', $expected_job['meta']['buffered_job_id'], []);
        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);

        $this->worker_queue->runNext(function ($name, array $params) use ($expected_job) {
            $this->assertSame(
                [
                    'name'   => $expected_job['name'],
                    'params' => $expected_job['params'],
                ],
                [
                    'name'   => $name,
                    'params' => $params,
                ]
            );
        });
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testDatabaseRecordForJobMarkedAsSuccessfulIsMovedToSuccessfulJobs()
    {
        $this->checkDatabaseRecordIsMoved(
            'successful_jobs',
            function () {}
        );
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException Exception
     */
    public function testMessageForJobMarkedAsSuccessfulIsAcknowledged()
    {
        $this->checkMessageForJobIsAcknowledged(function () {});
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException \Hodor\MessageQueue\Adapter\Testing\Exception\EmptyQueueException
     */
    public function testJobMarkedAsSuccessfulButNotAcknowledgedCanBeAcknowledgedSecondTime()
    {
        $this->checkUnacknowledgedJobMissingFromBufferCanBeAcknowledge(function () {});
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException UnexpectedValueException
     */
    public function testDatabaseRecordForJobMarkedAsFailedIsMovedToFailedJobs()
    {
        $this->checkDatabaseRecordIsMoved(
            'failed_jobs',
            function () {
                throw new UnexpectedValueException("Failed job");
            }
        );
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException UnexpectedValueException
     */
    public function testMessageForJobMarkedAsFailedIsAcknowledged()
    {
        $this->checkMessageForJobIsAcknowledged(function () {
            throw new UnexpectedValueException("Failed job");
        });
    }

    /**
     * @covers ::__construct
     * @covers ::runNext
     * @covers ::<private>
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException \Hodor\MessageQueue\Adapter\Testing\Exception\EmptyQueueException
     */
    public function testJobMarkedAsFailedButNotAcknowledgedCanBeAcknowledgedSecondTime()
    {
        $this->checkUnacknowledgedJobMissingFromBufferCanBeAcknowledge(function () {
            throw new Exception("Failed job");
        });
    }

    /**
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testWorkerQueueIsReused()
    {
        $this->assertSame(
            $this->worker_queue,
            $this->worker_queue_factory->getWorkerQueue('default-worker')
        );
    }

    /**
     * @param string $finished_jobs_table
     * @param callable $job_runner
     */
    private function checkDatabaseRecordIsMoved($finished_jobs_table, callable $job_runner)
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->database->insert(
            'queued_jobs',
            $expected_job['meta']['buffered_job_id'],
            [
                'job_name'   => $expected_job['name'],
                'job_params' => json_encode($expected_job['params']),
            ]
        );

        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);

        try {
            $this->worker_queue->runNext($job_runner);
        } finally {
            $job = current($this->database->getAll($finished_jobs_table));
            $this->assertEquals(
                [
                    'job_name' => $expected_job['name'],
                    'param'    => $expected_job['params']['value'],
                    'meta'     => $expected_job['meta']['buffered_job_id'],
                ],
                [
                    'job_name' => $job['job_name'],
                    'param'    => json_decode($job['job_params'], true)['value'],
                    'meta'     => $expected_job['meta']['buffered_job_id'],
                ]
            );
        }
    }

    /**
     * @param callable $job_runner
     */
    private function checkMessageForJobIsAcknowledged(callable $job_runner)
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->database->insert('queued_jobs', $expected_job['meta']['buffered_job_id'], []);
        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);

        $this->worker_queue->runNext($job_runner);
        $this->message_bank->emulateReconnect();
        $this->worker_queue->runNext(function () {});
    }

    /**
     * @param callable $job_runner
     */
    private function checkUnacknowledgedJobMissingFromBufferCanBeAcknowledge(callable $job_runner)
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'meta'   => ['buffered_job_id' => rand(1, 10)],
        ];

        $this->worker_queue->push($expected_job['name'], $expected_job['params'], $expected_job['meta']);

        try {
            $this->worker_queue->runNext($job_runner);
        } catch (BufferedJobNotFoundException $exception) {
            $this->worker_queue->runNext($job_runner);
        }
    }
}
