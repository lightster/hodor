<?php

namespace Hodor\Database\Adapter\TestUtil;

use Exception;
use Hodor\Database\AdapterInterface;
use Hodor\Database\PgsqlAdapter;

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
     * @return AdapterInterface
     * @throws Exception
     */
    abstract public function generateAdapter();

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        $this->adapter = $this->generateAdapter();

        return $this->adapter;
    }
}
