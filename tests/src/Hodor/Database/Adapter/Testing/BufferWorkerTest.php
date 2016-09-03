<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\BufferWorkerTest as BufferWorkerBaseTest;
use Hodor\Database\Adapter\TestUtil\TestingProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Testing\BufferWorker
 */
class BufferWorkerTest extends BufferWorkerBaseTest
{
    /**
     * @return TestingProvisioner
     */
    protected function generateProvisioner()
    {
        return new TestingProvisioner();
    }
}
