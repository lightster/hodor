<?php

namespace Hodor\JobQueue;

use DateTime;
use Exception;
use Hodor\Database\Adapter\Testing\BufferWorker;
use Hodor\Database\Adapter\Testing\Database;
use Hodor\JobQueue\Config\MessageQueueConfig;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Adapter\Testing\Consumer;
use Hodor\MessageQueue\Adapter\Testing\MessageBank;
use Hodor\MessageQueue\Adapter\Testing\Producer;
use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\Queue;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\BufferQueue
 */
class BufferQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var MessageBank
     */
    private $message_bank;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var BufferQueue
     */
    private $buffer_queue;

    /**
     * @var Database
     */
    private $database;

    public function setUp()
    {
        parent::setUp();

        $this->config = new Config(__FILE__, [
            'adapter_factory' => 'testing',
            'worker_queues' => [
                'test-queue' => [
                    'bufferers_per_server' => 5,
                ],
            ],
        ]);
        $this->message_bank = new MessageBank();
        $this->consumer = new Consumer($this->message_bank);
        $this->producer = new Producer($this->message_bank);
        $this->queue = new Queue($this->consumer, $this->producer);
        $this->database = new Database();
        $this->buffer_queue = new BufferQueue(
            $this->queue,
            new BufferWorker($this->database),
            $this->config
        );
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
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
        $this->assertBufferedJobEquals($expected_job, $this->consumer);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
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
            $this->assertBufferedJobEquals($expected_job, $this->consumer);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @covers ::<private>
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
        $this->assertBufferedJobEquals($expected_job, $this->consumer);
    }

    /**
     * @covers ::__construct
     * @covers ::processBuffer
     * @covers ::<private>
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

        $job = current($this->database->getAll('buffered_jobs'));
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
        $this->message_bank->emulateReconnect();
        $this->buffer_queue->processBuffer();
    }

    /**
     * @param array $expected_job
     * @param ConsumerInterface $consumer
     */
    private function assertBufferedJobEquals(array $expected_job, ConsumerInterface $consumer)
    {
        $consumer->consumeMessage(function (IncomingMessage $message) use ($expected_job) {
            $received_job = $message->getContent();
            $this->assertEquals($expected_job, [
                'name'    => $received_job['name'],
                'params'  => $received_job['params'],
                'options' => $received_job['options'],
            ]);
        });
    }
}
