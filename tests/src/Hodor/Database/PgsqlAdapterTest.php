<?php

namespace Hodor\Database;

use Hodor\Database\Adapter\TestUtil\PostgresProvisioner;

/**
 * @coversDefaultClass Hodor\Database\PgsqlAdapter
 */
class PgsqlAdapterTest extends AbstractAdapterTest
{
    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new PostgresProvisioner();
    }
}
