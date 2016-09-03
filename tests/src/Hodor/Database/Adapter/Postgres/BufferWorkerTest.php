<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\BufferWorkerTest as BufferWorkerBaseTest;
use Hodor\Database\Adapter\TestUtil\PostgresProvisioner;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Postgres\BufferWorker
 */
class BufferWorkerTest extends BufferWorkerBaseTest
{
    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new PostgresProvisioner();
    }
}
