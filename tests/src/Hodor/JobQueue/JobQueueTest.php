<?php

namespace Hodor\JobQueue;

use Exception;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\Testing\MessageBankFactory;
use Hodor\MessageQueue\AdapterFactory;
use Hodor\MessageQueue\IncomingMessage;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\JobQueue
 */
class JobQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JobQueue
     */
    private $job_queue;

    public function setUp()
    {
        $this->job_queue = new JobQueue();
    }

    /**
     * @covers ::setConfigFile
     * @covers ::getConfig
     */
    public function testConfigCanBeLoadedFromFile()
    {
        $this->job_queue->setConfigFile(__DIR__ . '/../../../../config/config.test.php');
        $this->assertTrue(
            $this->job_queue->getConfig() instanceof Config
        );
    }

    /**
     * @covers ::setConfigFile
     * @covers ::getConfig
     */
    public function testConfigCanBeRetrievedMultipleTimes()
    {
        $this->job_queue->setConfigFile(__DIR__ . '/../../../../config/config.test.php');
        $this->assertSame(
            $this->job_queue->getConfig(),
            $this->job_queue->getConfig()
        );
    }

    /**
     * @covers ::setConfig
     * @covers ::getConfig
     */
    public function testConfigObjectCanBePassedIn()
    {
        $config = new Config(__FILE__, []);
        $this->job_queue->setConfig($config);

        $this->assertSame($config, $this->job_queue->getConfig());
    }

    /**
     * @covers ::getConfig
     * @expectedException Exception
     */
    public function testExceptionIsThrownIfConfigFileIsNotSet()
    {
        $this->job_queue->getConfig();
    }

    /**
     * @covers ::push
     * @covers ::getQueueManager
     */
    public function testPushAppendsJobsToBufferQueue()
    {
        $mq_adapter = $this->setupMqAdapter();
        $expected_job = $this->queueJob();
        $this->assertBufferedJobEquals($expected_job, $mq_adapter);
    }

    /**
     * @covers ::push
     * @covers ::getQueueManager
     * @covers ::beginBatch
     * @covers ::publishBatch
     */
    public function testBatchedJobIsPublishedIfAndOnlyIfBatchIsPublished()
    {
        $mq_adapter = $this->setupMqAdapter();

        $this->job_queue->beginBatch();
        $expected_job = $this->queueJob();

        try {
            $consumer = $mq_adapter->getConsumer('bufferer-default');
            $consumer->consumeMessage(function () {
                $this->fail('A message should not be available for consuming until after batch is published.');
            });
        } catch (Exception $exception) {
            // the exception is expected. do nothing.
        }

        $this->job_queue->publishBatch();

        $this->assertBufferedJobEquals($expected_job, $mq_adapter);
    }

    /**
     * @covers ::push
     * @covers ::getQueueManager
     * @covers ::beginBatch
     * @covers ::discardBatch
     * @expectedException Exception
     */
    public function testBatchedJobIsDiscardedIfBatchIsDiscarded()
    {
        $mq_adapter = $this->setupMqAdapter();

        $this->job_queue->beginBatch();
        $expected_job = $this->queueJob();
        $this->job_queue->discardBatch();

        $this->assertBufferedJobEquals($expected_job, $mq_adapter);
    }

    /**
     * @return FactoryInterface
     */
    private function setupMqAdapter()
    {
        $config = new Config(__FILE__, [
            'message_queue_factory' => [
                'type' => 'testing',
                'message_bank_factory' => new MessageBankFactory(),
            ],
            'superqueue' => ['database' => ['type' => 'testing']],
            'buffer_queues'   => [
                'default' => [
                    'host' => 'fake-host',
                    'username' => 'guest',
                    'password' => 'guest',
                    'bufferers_per_server' => 1,
                ],
            ],
        ]);

        $this->job_queue->setConfig($config);

        return (new AdapterFactory())->getAdapter($config->getMessageQueueConfig());
    }

    /**
     * @return array
     */
    private function queueJob()
    {
        $uniqid = uniqid();
        $expected_job = [
            'name'    => "job-{$uniqid}",
            'params'  => ['value' => $uniqid],
            'options' => ['mutex_id' => "mutex-{$uniqid}"],
        ];
        $this->job_queue->push(
            $expected_job['name'],
            $expected_job['params'],
            $expected_job['options']
        );

        return $expected_job;
    }

    /**
     * @param array $expected_job
     * @param FactoryInterface $mq_adapter
     */
    private function assertBufferedJobEquals(array $expected_job, FactoryInterface $mq_adapter)
    {
        $consumer = $mq_adapter->getConsumer('bufferer-default');
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
