<?php

namespace Hodor\Database\Adapter\Testing;

use Hodor\Database\Adapter\BufferWorkerInterface;
use Hodor\Database\Adapter\DequeuerInterface;
use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\Adapter\SuperqueuerInterface;

class Factory implements FactoryInterface
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var int
     */
    private $connection_id;

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
     * @param Database $database
     * @param int $connection_id
     */
    public function __construct(Database $database, $connection_id)
    {
        $this->database = $database;
        $this->connection_id = $connection_id;
    }

    /**
     * @return BufferWorkerInterface
     */
    public function getBufferWorker()
    {
        if ($this->buffer_worker) {
            return $this->buffer_worker;
        }

        $this->buffer_worker = new BufferWorker($this->database);

        return $this->buffer_worker;
    }

    /**
     * @return SuperqueuerInterface
     */
    public function getSuperqueuer()
    {
        if ($this->superqueuer) {
            return $this->superqueuer;
        }

        $this->superqueuer = new Superqueuer($this->database, $this->connection_id);

        return $this->superqueuer;
    }

    /**
     * @return DequeuerInterface
     */
    public function getDequeuer()
    {
        if ($this->dequeuer) {
            return $this->dequeuer;
        }

        $this->dequeuer = new Dequeuer($this->database);

        return $this->dequeuer;
    }
}
