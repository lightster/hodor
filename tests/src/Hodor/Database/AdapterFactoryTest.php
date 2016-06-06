<?php

namespace Hodor\Database;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Database\AdapterFactory
 */
class AdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    private $adapter_factory;

    public function setUp()
    {
        parent::setUp();

        $this->adapter_factory = new AdapterFactory([]);
    }

    /**
     * @covers ::__construct
     * @covers ::getAdapter
     * @expectedException \Exception
     */
    public function testRequestingAnAdapterForUnknownNameThrowsAnException()
    {
        $this->adapter_factory->getAdapter('unk');
    }

    /**
     * @covers ::__construct
     * @covers ::getAdapter
     */
    public function testAdapterForPgsqlNameIsAPgsqlAdapter()
    {
        $this->assertInstanceOf(
            '\Hodor\Database\PgsqlAdapter',
            $this->adapter_factory->getAdapter('pgsql')
        );
    }
}
