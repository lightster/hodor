<?php

namespace Hodor;

use PHPUnit_Framework_TestCase;

class JobQueueFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testConfigCanBeLoadedFromFile()
    {
        JobQueueFacade::setConfigFile(__DIR__ . '/Config/PhpConfig.php');
        $this->assertTrue(
            JobQueueFacade::getConfig() instanceof \Hodor\Config
        );
    }

    public function testFacadeCallsBufferPush()
    {
        $queue_name = 'some_queue_name';
        $job_name = 'some_job_name';
        $job_params = ['a' => 'param'];
        $job_options = ['option' => true];

        $worker_queue = $this->getMockBuilder('\Hodor\WorkerQueue')
            ->disableOriginalConstructor()
            ->setMethods(['push'])
            ->getMock();
        $worker_queue->expects($this->once())
            ->method('push')
            ->with(
                $job_name,
                $job_params
            );

        $queue_factory = $this->getMockBuilder('\Hodor\MessageQueue\QueueFactory')
            ->disableOriginalConstructor()
            ->setMethods(['getWorkerQueue'])
            ->getMock();
        $queue_factory->expects($this->once())
            ->method('getWorkerQueue')
            ->with(
                $queue_name
            )
            ->will($this->returnValue($worker_queue));

        JobQueueFacade::setQueueFactory($queue_factory);
        JobQueueFacade::push(
            $queue_name,
            $job_name,
            $job_params,
            $job_options
        );
    }
}
