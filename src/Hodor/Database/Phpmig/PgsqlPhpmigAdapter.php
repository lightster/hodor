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
        $sql = <<<SQL
SELECT version
FROM migrations.migrations
ORDER BY version
SQL;

        $versions = [];
        $result = pg_query($this->connection, $sql);
        while ($row = pg_fetch_assoc($result)) {
            $versions[] = $row['version'];
        }

        return $versions;
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
        $sql = <<<SQL
CREATE SCHEMA migrations;

CREATE TABLE migrations.migrations
(
    version VARCHAR PRIMARY KEY,
    file_hash VARCHAR NOT NULL,
    migrated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE migrations.rollbacks
(
    version VARCHAR NOT NULL,
    file_hash VARCHAR NOT NULL,
    migrated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    rolled_back_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);
SQL;

        pg_query($this->connection, $sql);

        return $this;
    }
}
