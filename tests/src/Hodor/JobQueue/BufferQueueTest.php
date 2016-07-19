<?php

namespace Hodor\JobQueue;

use DateTime;
use Exception;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
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
     * @var QueueManager
     */
    private $queue_manager;

    public function setUp()
    {
        parent::setUp();

        $this->config = new Config(__FILE__, [
            'adapter_factory' => 'testing',
            'buffer_queues' => [
                'default' => [
                    'bufferers_per_server' => 5,
                ],
            ],
        ]);
        $this->consumer = $this->config->getAdapterFactory()->getConsumer('bufferer-default');
        $this->producer = $this->config->getAdapterFactory()->getProducer('bufferer-default');
        $this->queue = new Queue($this->consumer, $this->producer);
        $this->queue_manager = new QueueManager($this->config);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     */
    public function testJobCanBeBufferQueued()
    {
        $buffer_queue = new BufferQueue($this->queue, $this->queue_manager);

        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => [],
        ];

        $buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);
        $this->assertBufferedJobEquals($expected_job, $this->consumer);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     * @expectedException Exception
     */
    public function testBufferedJobOptionsAreValidated()
    {
        $buffer_queue = new BufferQueue($this->queue, $this->queue_manager);

        $buffer_queue->push('broken-job', [], ['unknown_option' => 'hey']);
    }

    /**
     * @covers ::__construct
     * @covers ::push
     */
    public function testRunAfterIsConvertedToStringIfProvided()
    {
        $buffer_queue = new BufferQueue($this->queue, $this->queue_manager);

        $now = new DateTime();
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "some-job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => ['run_after' => $now],
        ];

        $buffer_queue->push($expected_job['name'], $expected_job['params'], $expected_job['options']);

        $expected_job['options']['run_after'] = $expected_job['options']['run_after']->format('c');
        $this->assertBufferedJobEquals($expected_job, $this->consumer);
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
