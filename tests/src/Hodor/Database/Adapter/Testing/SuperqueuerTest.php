<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\SuperqueuerTest as SuperqueuerBaseTest;
use Hodor\Database\Adapter\TestUtil\TestingProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Testing\Superqueuer
 */
class SuperqueuerTest extends SuperqueuerBaseTest
{
    /**
     * @covers ::__destruct
     * @covers ::requestAdvisoryLock
     */
    public function testAdvisoryLockCanBeAcquired()
    {
        parent::testAdvisoryLockCanBeAcquired();
    }

    /**
     * @return TestingProvisioner
     */
    protected function generateProvisioner()
    {
        return new TestingProvisioner();
    }
}
