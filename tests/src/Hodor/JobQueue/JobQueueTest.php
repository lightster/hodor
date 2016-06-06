<?php

namespace Hodor\JobQueue;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\JobQueue\JobQueue
 */
class JobQueueTest extends PHPUnit_Framework_TestCase
{
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
            $this->job_queue->getConfig() instanceof \Hodor\JobQueue\Config
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
     * @covers ::getConfig
     * @expectedException Exception
     */
    public function testExceptionIsThrownIfConfigFileIsNotSet()
    {
        $this->job_queue->getConfig();
    }

    /**
     * @covers ::setQueueManager
     * @covers ::push
     * @covers ::getQueueManager
     */
    public function testJobQueuePushCallsBufferPush()
    {
        $queue_name = 'some_queue_name';
        $job_name = 'some_job_name';
        $job_params = ['a' => 'param'];
        $job_options = ['option' => true];

        $buffer_queue = $this->getMockBuilder('\Hodor\JobQueue\BufferQueue')
            ->disableOriginalConstructor()
            ->setMethods(['push'])
            ->getMock();
        $buffer_queue->expects($this->once())
            ->method('push')
            ->with(
                $job_name,
                $job_params,
                $job_options
            );

        $queue_manager = $this->getMockBuilder('\Hodor\JobQueue\QueueManager')
            ->disableOriginalConstructor()
            ->setMethods(['getBufferQueueForJob'])
            ->getMock();
        $queue_manager->expects($this->once())
            ->method('getBufferQueueForJob')
            ->with(
                $job_name,
                $job_params,
                $job_options
            )
            ->will($this->returnValue($buffer_queue));

        $this->job_queue->setQueueManager($queue_manager);
        $this->job_queue->push(
            $job_name,
            $job_params,
            $job_options
        );
    }

    /**
     * @covers ::setConfigFile
     * @covers ::getConfig
     */
    public function testJobQueueUsesADefaultBufferWorker()
    {
        $this->job_queue->setConfigFile(__DIR__ . '/../../../../config/config.test.php');
        $this->assertSame(
            $this->job_queue->getConfig(),
            $this->job_queue->getConfig()
        );
    }
}
