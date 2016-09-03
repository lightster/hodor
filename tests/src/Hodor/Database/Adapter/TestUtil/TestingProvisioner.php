<?php

namespace Hodor\Database\Adapter\TestUtil;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Factory;
use Hodor\Database\ConverterAdapter;

class TestingProvisioner extends AbstractProvisioner
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var int
     */
    private $connection_id = 0;

    public function setUp()
    {
        $this->database = new Database();
    }

    /**
     * @return ConverterAdapter
     */
    public function generateAdapter()
    {
        return new ConverterAdapter(new Factory($this->database, ++$this->connection_id));
    }
}
