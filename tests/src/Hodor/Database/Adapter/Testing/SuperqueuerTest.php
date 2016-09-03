<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\SuperqueuerTest as SuperqueuerBaseTest;
use Hodor\Database\ConverterAdapter;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Testing\Superqueuer
 */
class SuperqueuerTest extends SuperqueuerBaseTest
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var int
     */
    private $connection_id = 0;

    /**
     * @covers ::__destruct
     * @covers ::requestAdvisoryLock
     */
    public function testAdvisoryLockCanBeAcquired()
    {
        parent::testAdvisoryLockCanBeAcquired();
    }

    /**
     * @return ConverterAdapter
     */
    protected function generateAdapter()
    {
        return new ConverterAdapter(new Factory($this->getDatabase(), ++$this->connection_id));
    }

    /**
     * @return Database
     */
    private function getDatabase()
    {
        if ($this->database) {
            return $this->database;
        }

        $this->database = new Database();

        return $this->database;
    }
}
