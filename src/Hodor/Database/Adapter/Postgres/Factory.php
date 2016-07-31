<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\Driver\YoPdoDriver;
use Hodor\Database\PgsqlAdapter;
use Lstr\YoPdo\YoPdo;

class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var BufferWorker
     */
    private $buffer_worker;

    /**
     * @var Superqueuer
     */
    private $superqueuer;

    /**
     * @var Dequeuer
     */
    private $dequeuer;

    /**
     * @var PgsqlAdapter
     */
    private $pgsql_adapter;

    /**
     * @var YoPdoDriver
     */
    private $yo_pdo_driver;

    /**
     * @param PgsqlAdapter $pgsql_adapter
     * @param array $config
     */
    public function __construct(PgsqlAdapter $pgsql_adapter, array $config)
    {
        $this->pgsql_adapter = $pgsql_adapter;
        $this->config = $config;
    }

    /**
     * @return BufferWorker
     */
    public function getBufferWorker()
    {
        if ($this->buffer_worker) {
            return $this->buffer_worker;
        }

        $this->buffer_worker = new BufferWorker($this->getYoPdo());

        return $this->buffer_worker;
    }

    /**
     * @return Superqueuer
     */
    public function getSuperqueuer()
    {
        if ($this->superqueuer) {
            return $this->superqueuer;
        }

        $this->superqueuer = new Superqueuer($this->getYoPdo());

        return $this->superqueuer;
    }

    /**
     * @return Dequeuer
     */
    public function getDequeuer()
    {
        if ($this->dequeuer) {
            return $this->dequeuer;
        }

        $this->dequeuer = new Dequeuer($this->getYoPdo());

        return $this->dequeuer;
    }

    /**
     * @return YoPdoDriver
     * @deprecated
     */
    public function getYoPdoDriver()
    {
        if ($this->yo_pdo_driver) {
            return $this->yo_pdo_driver;
        }

        $this->yo_pdo_driver = new YoPdoDriver($this->config);

        return $this->yo_pdo_driver;
    }

    /**
     * @return PgsqlAdapter
     */
    public function getPgsqlAdapter()
    {
        return $this->pgsql_adapter;
    }

    /**
     * @return YoPdo
     */
    private function getYoPdo()
    {
        return $this->getYoPdoDriver()->getYoPdo();
    }
}
