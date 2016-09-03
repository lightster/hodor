<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\DequeuerTest as DequeuerBaseTest;
use Hodor\Database\Adapter\TestUtil\TestingProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Testing\Dequeuer
 */
class DequeuerTest extends DequeuerBaseTest
{
    /**
     * @return TestingProvisioner
     */
    protected function generateProvisioner()
    {
        return new TestingProvisioner();
    }
}
