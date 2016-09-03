<?php

namespace Hodor\Database\Phpmig;

use Lstr\YoPdo\YoPdo;
use Phpmig\Migration\Migration;
use ReflectionClass;

class PgsqlPhpmigAdapter implements AdapterInterface
{
    /**
     * @var YoPdo
     */
    private $yo_pdo;

    /**
     * @param YoPdo $yo_pdo
     */
    public function __construct(YoPdo $yo_pdo)
    {
        $this->yo_pdo = $yo_pdo;
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
        $row_generator = $this->yo_pdo->getSelectRowGenerator($sql);
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

        $this->yo_pdo->insert('migrations.migrations', [
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

        $version_row = $this->yo_pdo->query($sql, ['version' => $version])->fetch();
        if (!$version_row) {
            throw new Exception(
                "Migration '{$version}' cannot be rolled back because it is not currently applied."
            );
        }

        $migration_reflection = new ReflectionClass(get_class($migration));
        $this->yo_pdo->insert('migrations.rollbacks', [
            'version'        => $version_row['version'],
            'migrated_at'    => $version_row['migrated_at'],
            'migration_hash' => $version_row['migration_hash'],
            'rollback_hash'  => hash_file('sha256', $migration_reflection->getFileName()),
        ]);
        $this->yo_pdo->delete(
            'migrations.migrations',
            'version = :version',
            ['version' => $migration->getVersion()]
        );

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

        $row = $this->yo_pdo->query($sql)->fetch();
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

        $this->yo_pdo->queryMultiple($sql);

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
