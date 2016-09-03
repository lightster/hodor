<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\BufferWorkerTest as BufferWorkerBaseTest;
use Hodor\Database\ConverterAdapter;

/**
 * @coversDefaultClass Hodor\Database\Adapter\Testing\BufferWorker
 */
class BufferWorkerTest extends BufferWorkerBaseTest
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
