<?php

namespace Hodor\Database\Phpmig;

use Hodor\Database\Driver\YoPdoDriver;
use Phpmig\Migration\Migration;
use ReflectionClass;

class PgsqlPhpmigAdapter implements AdapterInterface
{
    /**
     * @var YoPdoDriver
     */
    private $driver;

    /**
     * @param YoPdoDriver $driver
     */
    public function __construct(YoPdoDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $sql = <<<SQL
SELECT version
FROM migrations.migrations
ORDER BY version
SQL;

        $versions = [];
        $row_generator = $this->driver->selectRowGenerator($sql);
        foreach ($row_generator as $row) {
            $versions[] = $row['version'];
        }

        return $versions;
    }

    /**
     * @param  Migration $migration
     * @return fluent
     */
    public function up(Migration $migration)
    {
        $migration_reflection = new ReflectionClass(get_class($migration));

        $this->driver->insert('migrations.migrations', [
            'version'        => $migration->getVersion(),
            'migration_hash' => hash_file('sha256', $migration_reflection->getFileName()),
        ]);

        return $this;
    }

    /**
     * @param  Migration $migration
     * @return fluent
     */
    public function down(Migration $migration)
    {
        $version = $migration->getVersion();
        $sql = <<<SQL
SELECT *
FROM migrations.migrations
WHERE version = :version
ORDER BY version
SQL;

        $version_row = $this->driver->selectOne($sql, ['version' => $version]);
        if (!$version_row) {
            throw new Exception(
                "Migration '{$version}' cannot be rolled back because it is not currently applied."
            );
        }

        $migration_reflection = new ReflectionClass(get_class($migration));
        $this->driver->insert('migrations.rollbacks', [
            'version'        => $version_row['version'],
            'migrated_at'    => $version_row['migrated_at'],
            'migration_hash' => $version_row['migration_hash'],
            'rollback_hash'  => hash_file('sha256', $migration_reflection->getFileName()),
        ]);
        $this->driver->delete('migrations.migrations', [
            'version' => $migration->getVersion(),
        ]);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSchema()
    {
        $sql = <<<SQL
SELECT 1 AS schema_exists
FROM pg_tables
WHERE schemaname = 'migrations'
    AND tablename = 'migrations'
SQL;

        $row = $this->driver->selectOne($sql);
        if ($row) {
            return (bool) $row['schema_exists'];
        }

        return false;
    }

    /**
     * @return fluent
     */
    public function createSchema()
    {
        $sql = <<<SQL
CREATE SCHEMA migrations;

CREATE TABLE migrations.migrations
(
    version VARCHAR PRIMARY KEY,
    migrated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    migration_hash VARCHAR NOT NULL
);

CREATE TABLE migrations.rollbacks
(
    version VARCHAR NOT NULL,
    migrated_at TIMESTAMP WITH TIME ZONE NOT NULL,
    migration_hash VARCHAR NOT NULL,
    rolled_back_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    rollback_hash VARCHAR NOT NULL
);
SQL;

        $this->driver->queryMultiple($sql);

        return $this;
    }

    /**
     * @return string
     */
    public function getMigrationsPath()
    {
        return __DIR__ . '/../../../../migrations/postgres';
    }
}
