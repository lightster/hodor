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
        $config->addQueueConfig('bufferer-test-queue', ['bufferers_per_server' => 5]);

        $this->test_util = new TestingQueueProvisioner($config);

        $this->buffer_queue_factory = $this->test_util->getBufferQueueFactory();
        $this->buffer_queue = $this->buffer_queue_factory->getQueue('test-queue');
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
        $this->test_util->getMessageBank('bufferer-test-queue')->emulateReconnect();
        $this->buffer_queue->processBuffer();
    }

    /**
     * @param array $expected_job
     */
    private function assertBufferedJobEquals(array $expected_job)
    {
        $consumer = $this->test_util->getConsumerQueue('bufferer-test-queue');
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
