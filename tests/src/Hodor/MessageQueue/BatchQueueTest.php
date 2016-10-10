<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\BatchQueue
 */
class BatchQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::push
     */
    public function testMessageCanBeProduced()
    {
        $expected_value = "hi there, " . uniqid();
        $pusher = function ($message) use ($expected_value) {
            $this->assertSame($expected_value, $message);
        };

        $batch_queue = new BatchQueue($pusher);
        $batch_queue->push($expected_value);
    }
}
