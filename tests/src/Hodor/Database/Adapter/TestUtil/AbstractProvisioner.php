<?php

namespace Hodor\Database\Adapter\TestUtil;

use Exception;
use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\AdapterInterface;

abstract class AbstractProvisioner
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    abstract public function setUp();

    public function tearDown()
    {
        $this->adapter = null;
    }

    /**
     * @return FactoryInterface
     * @throws Exception
     */
    abstract public function generateAdapterFactory();

    /**
     * @return FactoryInterface
     */
    public function getAdapterFactory()
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        $this->adapter = $this->generateAdapterFactory();

        return $this->adapter;
    }
}
