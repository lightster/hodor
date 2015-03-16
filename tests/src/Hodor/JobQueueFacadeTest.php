<?php

namespace Hodor;

use PHPUnit_Framework_TestCase;

class JobQueueFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testConfigCanBeLoadedFromFile()
    {
        JobQueueFacade::setConfigFile(__DIR__ . '/Config/PhpConfig.php');
        $this->assertTrue(
            JobQueueFacade::getConfig() instanceof \Hodor\JobQueue\Config
        );
    }

    public function testFacadeCallsBufferPush()
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
            ->setMethods(['getBufferQueue'])
            ->getMock();
        $queue_factory->expects($this->once())
            ->method('getBufferQueue')
            ->with(
                'default'
            )
            ->will($this->returnValue($buffer_queue));

        JobQueueFacade::setQueueFactory($queue_factory);
        JobQueueFacade::push(
            $job_name,
            $job_params,
            $job_options
        );
    }
}
