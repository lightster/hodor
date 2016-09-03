<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\DequeuerTest as DequeuerBaseTest;
use Hodor\Database\Adapter\TestUtil\PostgresProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Postgres\Dequeuer
 */
class DequeuerTest extends DequeuerBaseTest
{
    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new PostgresProvisioner();
    }
}
