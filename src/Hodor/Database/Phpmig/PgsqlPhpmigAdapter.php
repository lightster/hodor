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
        $sql = <<<SQL
SELECT 1
FROM pg_tables
WHERE schemaname = 'migrations'
    AND tablename = 'migrations'
SQL;

        $result = pg_query($this->connection, $sql);
        if ($row = pg_fetch_row($result)) {
            return (bool) $row[0];
        }

        return false;
    }

    public function createSchema()
    {
    }
}
