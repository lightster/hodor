<?php

namespace Hodor;

use PHPUnit_Framework_TestCase;

class JobQueueFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testFacadeCallsBufferPush()
    {
        $job_name = 'some_job_name';
        $job_params = ['a' => 'param'];
        $job_options = ['option' => true];

        $buffer_queue = $this->getMockBuilder('\Hodor\JobQueue')
            ->setMethods(['push'])
            ->getMock();
        $buffer_queue->expects($this->once())
            ->method('push')
            ->with(
                $job_name,
                $job_params,
                $job_options
            );

        JobQueueFacade::setBufferQueue($buffer_queue);
        JobQueueFacade::push(
            $job_name,
            $job_params,
            $job_options
        );
    }
}
