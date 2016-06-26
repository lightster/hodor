<?php

namespace Hodor\Database\Exception;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Database\Exception\BufferedJobNotFoundException
 */
class BufferedJobNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @expectedException Hodor\Database\Exception\BufferedJobNotFoundException
     */
    public function testExceptionIsThrowable()
    {
        throw new BufferedJobNotFoundException("Job not found", 1, []);
    }

    /**
     * @covers ::__construct
     */
    public function testExceptionMessageIsTheSamePassedToConstructor()
    {
        $expected = "Job not found: " . uniqid();
        $exception = new BufferedJobNotFoundException($expected, 1, []);

        $this->assertSame($expected, $exception->getMessage());
    }
}
