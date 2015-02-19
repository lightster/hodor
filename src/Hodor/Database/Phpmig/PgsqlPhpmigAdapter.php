<?php

namespace Hodor\Database\Phpmig;

use Phpmig\Adapter\AdapterInterface;
use Phpmig\Migration\Migration;

class PgsqlPhpmigAdapter implements AdapterInterface
{
    /**
     * connection returned by pg_connect()
     * @var resource
     */
    private $connection;

    /**
     * @param resource $connection - connection returned by pg_connect()
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function fetchAll()
    {
    }

    public function up(Migration $migration)
    {
    }

    public function down(Migration $migration)
    {
    }

    public function hasSchema()
    {
    }

    public function createSchema()
    {
    }
}
