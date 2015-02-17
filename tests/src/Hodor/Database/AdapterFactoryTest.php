<?php

namespace Hodor\Database;

use PHPUnit_Framework_TestCase;

class AdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    private $adapter_factory;

    public function setUp()
    {
        parent::setUp();

        $this->adapter_factory = new AdapterFactory([]);
    }

    /**
     * @expectedException \Exception
     */
    public function testRequestingAnAdapterForUnknownNameThrowsAnException()
    {
        $this->adapter_factory->getAdapter('unk');
    }

    public function testAdapterForPgsqlNameIsAPgsqlAdapter()
    {
        $this->assertInstanceOf(
            '\Hodor\Database\PgsqlAdapter',
            $this->adapter_factory->getAdapter('pgsql')
        );
    }
}
