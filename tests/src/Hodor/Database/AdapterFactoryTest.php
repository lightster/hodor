<?php

namespace Hodor\Database;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Database\AdapterFactory
 */
class AdapterFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterFactory
     */
    private $adapter_factory;

    public function setUp()
    {
        parent::setUp();

        $this->adapter_factory = new AdapterFactory();
    }

    /**
     * @covers ::getAdapter
     * @expectedException \Exception
     */
    public function testRequestingAnAdapterWithoutProvidingATypeThrowsAnException()
    {
        $this->adapter_factory->getAdapter([]);
    }

    /**
     * @covers ::getAdapter
     * @expectedException \Exception
     */
    public function testRequestingAnAdapterForUnknownTypeThrowsAnException()
    {
        $this->adapter_factory->getAdapter(['type' => 'unk']);
    }

    /**
     * @covers ::getAdapter
     */
    public function testAdapterForPgsqlNameIsAPostgresFactory()
    {
        $this->assertInstanceOf(
            '\Hodor\Database\Adapter\Postgres\Factory',
            $this->adapter_factory->getAdapter(['type' => 'pgsql'])
        );
    }
}
