<?php

namespace Hodor\Database\Adapter\Postgres;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Hodor\Database\Adapter\Postgres\Connection
 */
class ConnectionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getYoPdo
     */
    public function testYoPdoObjectIsUseable()
    {
        $connection = new Connection($this->getDbConfig());
        $yo_pdo = $connection->getYoPdo();

        $yo_pdo->queryMultiple('BEGIN');

        $this->assertSame(
            $yo_pdo->query('SELECT txid_current()')->fetch(),
            $yo_pdo->query('SELECT txid_current()')->fetch()
        );

        $yo_pdo->queryMultiple('ROLLBACK');
    }

    /**
     * @covers ::__construct
     * @covers ::getYoPdo
     */
    public function testYoPdoIsReused()
    {
        $connection = new Connection($this->getDbConfig());

        $this->assertSame(
            $connection->getYoPdo(),
            $connection->getYoPdo()
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getDbConfig()
    {
        $config_path = __DIR__ . '/../../../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return $config['test']['db']['yo-pdo-pgsql'];
    }
}
