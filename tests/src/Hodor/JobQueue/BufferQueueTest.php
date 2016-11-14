<?php

namespace Hodor\JobQueue;

use DateTime;
use Exception;
use Hodor\JobQueue\TestUtil\TestingQueueProvisioner;
use Hodor\MessageQueue\Adapter\Testing\Config as TestingConfig;
use Hodor\MessageQueue\IncomingMessage;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\BufferQueue
 */
class BufferQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TestingQueueProvisioner
     */
    private $test_util;

    /**
     * @var BufferQueueFactory
     */
    private $buffer_queue_factory;

    /**
     * @var BufferQueue
     */
    private $buffer_queue;

    public function setUp()
    {
        parent::setUp();

        $config = new TestingConfig([]);
        $config->addQueueConfig('bufferer-default', ['bufferers_per_server' => 5]);

        $this->test_util = new TestingQueueProvisioner($config);

        $this->buffer_queue_factory = $this->test_util->getBufferQueueFactory();
        $this->buffer_queue = $this->buffer_queue_factory->getQueue('default');
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     */
    public function testJobCanBeBufferQueued()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => [],
        ];

        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);
        $this->assertBufferedJobEquals($expected_job);
    }

    /**
     * @covers ::__construct
     * @covers \Hodor\JobQueue\BufferQueueFactory
     */
    public function testBufferQueueForJobIsAsExpected()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => [],
        ];

        $buffer_queue_for_job = $this->buffer_queue_factory->getBufferQueueForJob(
            $expected_job['name'],
            $expected_job['params'],
            $expected_job['options']
        );

        $this->assertSame(
            $this->buffer_queue_factory->getQueue('default'),
            $buffer_queue_for_job
        );
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     */
    public function testMultipleJobsCanBeBufferQueued()
    {
        $uniqid = uniqid();
        $job_template = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => [],
        ];

        for ($i = 1; $i <= 2; $i++) {
            $expected_job = $job_template;
            $expected_job['params']['count'] = $i;

            $this->buffer_queue->push(
                $expected_job['name'],
                $expected_job['params'],
                $expected_job['options']
            );
            $this->assertBufferedJobEquals($expected_job);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     * @expectedException Exception
     */
    public function testBufferedJobOptionsAreValidated()
    {
        $this->buffer_queue->push('broken-job', [], ['unknown_option' => 'hey']);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     */
    public function testRunAfterIsConvertedToStringIfProvided()
    {
        $now = new DateTime();
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => ['run_after' => $now],
        ];

        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);

        $expected_job['options']['run_after'] = $expected_job['options']['run_after']->format('c');
        $this->assertBufferedJobEquals($expected_job);
    }

    /**
     * @covers ::__construct
     * @covers ::processBuffer
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     */
    public function testProcessingBufferedMessageMovesMessageToDatabase()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => ['queue_name' => 'test-queue'],
        ];

        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);

        $this->buffer_queue->processBuffer();

        $job = current($this->test_util->getDatabase()->getAll('buffered_jobs'));
        $this->assertEquals(
            [
                'job_name' => $expected_job['name'],
                'param'    => $expected_job['params']['value'],
                'option'   => $expected_job['options']['queue_name'],
                'meta'     => gethostname(),
            ],
            [
                'job_name' => $job['job_name'],
                'param'    => json_decode($job['job_params'], true)['value'],
                'option'   => $job['queue_name'],
                'meta'     => $job['buffered_from'],
            ]
        );
    }

    /**
     * @covers ::__construct
     * @covers ::processBuffer
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\BufferQueueFactory
     * @expectedException Exception
     */
    public function testProcessingBufferedMessageAcknowledgesMessage()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => ['queue_name' => 'test-queue'],
        ];

        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);

        $this->buffer_queue->processBuffer();
        $this->test_util->getMessageBank('bufferer-default')->emulateReconnect();
        $this->buffer_queue->processBuffer();
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     */
    public function testBatchedJobIsPublishedIfAndOnlyIfBatchIsPublished()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'   => "some-job-{$uniqid}",
            'params' => ['value' => $uniqid],
            'options' => ['queue_name' => 'test-queue'],
        ];

        $consumer = $this->test_util->getConsumerQueue('bufferer-default');

        $this->buffer_queue_factory->beginBatch();
        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);

        try {
            $consumer->consume(function () use ($expected_job) {
                $this->fail('A message should not be available for consuming until after batch is published.');
            });
        } catch (Exception $exception) {
            // the exception is expected. do nothing.
        }

        $this->buffer_queue_factory->publishBatch();

        $consumer->consume(function (IncomingMessage $message) use ($expected_job) {
            $received_job = $message->getContent();
            $this->assertEquals($expected_job, [
                'name'   => $received_job['name'],
                'params' => $received_job['params'],
                'options'   => $received_job['options'],
            ]);
        });
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
     * @covers \Hodor\JobQueue\AbstractQueueFactory
     * @covers \Hodor\JobQueue\WorkerQueueFactory
     * @expectedException Exception
     */
    public function testBatchedJobIsDiscardedIfBatchIsDiscarded()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'   => "some-job-{$uniqid}",
            'params' => ['value' => $uniqid],
            'options' => ['queue_name' => 'test-queue'],
        ];

        $this->buffer_queue_factory->beginBatch();
        $this->buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);
        $this->buffer_queue_factory->discardBatch();

        $this->test_util->getConsumerQueue('bufferer-default')->consume(function () {});
    }

    /**
     * @param array $expected_job
     */
    private function assertBufferedJobEquals(array $expected_job)
    {
        $consumer = $this->test_util->getConsumerQueue('bufferer-default');
        $consumer->consume(function (IncomingMessage $message) use ($expected_job) {
            $received_job = $message->getContent();
            $this->assertEquals($expected_job, [
                'name'    => $received_job['name'],
                'params'  => $received_job['params'],
                'options' => $received_job['options'],
            ]);
        });
    }
}
