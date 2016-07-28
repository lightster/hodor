<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\Adapter\FactoryTest as FactoryBaseTest;

/**
 * @coversDefaultClass \Hodor\Database\Adapter\Testing\Factory
 */
class FactoryTest extends FactoryBaseTest
{
    /**
     * @return FactoryInterface
     */
    protected function getTestFactory()
    {
        return new Factory(new Database(), 1);
    }
}
