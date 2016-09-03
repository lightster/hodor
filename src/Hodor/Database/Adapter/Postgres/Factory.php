<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Adapter\FactoryInterface;
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
     * @var Connection
     */
    private $connection;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
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

        $this->buffer_worker = new BufferWorker($this->getConnection());

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

        $this->superqueuer = new Superqueuer($this->getConnection());

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

        $this->dequeuer = new Dequeuer($this->getConnection());

        return $this->dequeuer;
    }

    /**
     * @return YoPdo
     */
    public function getYoPdo()
    {
        return $this->getConnection()->getYoPdo();
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $this->connection = new Connection($this->config);

        return $this->connection;
    }
}
