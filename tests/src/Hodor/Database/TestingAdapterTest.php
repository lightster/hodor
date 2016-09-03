<?php

namespace Hodor\Database;

use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Factory;
use Hodor\Database\Adapter\TestUtil\TestingProvisioner;

/**
 * @coversDefaultClass Hodor\Database\ConverterAdapter
 */
class TestingAdapterTest extends AbstractAdapterTest
{
    /**
     * @covers ::getAdapterFactory
     */
    public function testFactoryInterfaceIsSameInterfacePassedToConstructor()
    {
        $factory = new Factory(new Database(), 1);
        $adapter = new ConverterAdapter($factory);

        $this->assertSame($factory, $adapter->getAdapterFactory());
    }

    /**
     * @return PostgresProvisioner
     */
    protected function generateProvisioner()
    {
        return new TestingProvisioner();
    }
}
