<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\SuperqueuerTest as SuperqueuerBaseTest;
use Hodor\Database\Adapter\TestUtil\PostgresProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Postgres\Superqueuer
 */
class SuperqueuerTest extends SuperqueuerBaseTest
{
    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new PostgresProvisioner();
    }
}
