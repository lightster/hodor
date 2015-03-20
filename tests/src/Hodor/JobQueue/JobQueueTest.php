<?php

namespace Hodor\JobQueue;

use PHPUnit_Framework_TestCase;

class JobQueueTest extends PHPUnit_Framework_TestCase
{
    private $job_queue;

    public function setUp()
    {
        $this->job_queue = new JobQueue();
    }

    public function testConfigCanBeLoadedFromFile()
    {
        $this->job_queue->setConfigFile(__DIR__ . '/../Config/PhpConfig.php');
        $this->assertTrue(
            $this->job_queue->getConfig() instanceof \Hodor\JobQueue\Config
        );
    }

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

        $queue_factory = $this->getMockBuilder('\Hodor\JobQueue\QueueFactory')
            ->disableOriginalConstructor()
            ->setMethods(['getBufferQueueForJob'])
            ->getMock();
        $queue_factory->expects($this->once())
            ->method('getBufferQueueForJob')
            ->with(
                $job_name,
                $job_params,
                $job_options
            )
            ->will($this->returnValue($buffer_queue));

        $this->job_queue->setQueueFactory($queue_factory);
        $this->job_queue->push(
            $job_name,
            $job_params,
            $job_options
        );
    }
}
