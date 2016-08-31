<?php

namespace Hodor\Database;

use Exception;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Factory;

/**
 * @coversDefaultClass Hodor\Database\ConverterAdapter
 */
class TestingAdapterTest extends AbstractAdapterTest
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
     * @covers ::getAdapterFactory
     */
    public function testFactoryInterfaceIsSameInterfacePassedToConstructor()
    {
        $factory = new Factory($this->database, ++$this->connection_id);
        $adapter = new ConverterAdapter($factory);

        $this->assertSame($factory, $adapter->getAdapterFactory());
    }

    /**
     * @return ConverterAdapter
     * @throws Exception
     */
    protected function generateAdapter()
    {
        return new ConverterAdapter(new Factory($this->database, ++$this->connection_id));
    }
}
