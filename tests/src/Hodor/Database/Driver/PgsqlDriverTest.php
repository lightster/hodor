<?php

namespace Hodor\Database\Driver;

use Exception;

use PHPUnit_Framework_TestCase;

class PgsqlDriverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $config_path = __DIR__ . '/../../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $this->config = require $config_path;
    }

    /**
     * @dataProvider adapterProvider
     */
    public function testQueryMultipleCanRunMultipleQueries($adapter)
    {
        $tablename = 'test_multiple_queries_' . uniqid();

        $sql = <<<SQL
CREATE TABLE {$tablename} AS SELECT 1;
DROP TABLE {$tablename};
SQL;
        $adapter->queryMultiple($sql);
    }

    /**
     * @dataProvider adapterProvider
     * @expectedException Exception
     */
    public function testQueryMultipleThrowsAnExceptionOnError($adapter)
    {
        $sql = <<<SQL
SELECT 1 FROM not_there;
SQL;
        $adapter->queryMultiple($sql);
    }

    /**
     * @dataProvider adapterProvider
     */
    public function testSelectRowGeneratorGeneratesResults($adapter)
    {
        $sql = <<<SQL
SELECT 1 AS col UNION
SELECT 2 AS col UNION
SELECT 3 AS col
SQL;
        $row_generator = $adapter->selectRowGenerator($sql);
        $count = 1;
        foreach ($row_generator() as $row) {
            $this->assertEquals($row['col'], $count);
            ++$count;
        }
    }

    /**
     * @dataProvider adapterProvider
     * @expectedException Exception
     */
    public function testSelectRowGeneratorThrowsAnExceptionOnError($adapter)
    {
        $sql = <<<SQL
SELECT 1 FROM not_there;
SQL;
        $adapter->selectRowGenerator($sql);
    }

    /**
     * @dataProvider adapterProvider
     */
    public function testSelectOneReturnsResults($adapter)
    {
        $sql = <<<SQL
SELECT 5 AS col
SQL;
        $this->assertEquals(['col' => '5'], $adapter->selectOne($sql));
    }

    /**
     * @dataProvider adapterProvider
     * @expectedException Exception
     */
    public function testSelectOneThrowsAnExceptionOnError($adapter)
    {
        $sql = <<<SQL
SELECT 1 FROM not_there;
SQL;
        $adapter->selectOne($sql);
    }

    /**
     * @expectedException \Exception
     */
    public function testRequestingAConnectionWithoutADsnThrowsAnException()
    {
        $db = new PgsqlDriver([]);
        $db->getConnection();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testAConnectionFailureThrowsAnException()
    {
        $db = new PgsqlDriver(['dsn' => 'host=localhost user=nonexistent']);
        $db->getConnection();
    }

    public function testAConnectionCanBeMade()
    {
        $db = new PgsqlDriver($this->config['test']['db']['pgsql']);
        $this->assertEquals('resource', gettype($db->getConnection()));
    }

    /**
     * @return array
     */
    public function adapterProvider()
    {
        $config_path = __DIR__ . '/../../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return [
            [new PgsqlDriver($config['test']['db']['pgsql'])],
        ];
    }
}
