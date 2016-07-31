<?php

namespace Hodor\Database\Driver;

use Exception;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\Database\Driver\YoPdoDriver
 */
class DriverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::queryMultiple
     * @covers ::<private>
     */
    public function testQueryMultipleCanRunMultipleQueries()
    {
        $adapter = $this->getYoPdoDriver();

        $tablename = 'test_multiple_queries_' . uniqid();

        $sql = <<<SQL
CREATE TABLE {$tablename} AS SELECT 1;
DROP TABLE {$tablename};
SQL;
        $adapter->queryMultiple($sql);
    }

    /**
     * @covers ::__construct
     * @covers ::queryMultiple
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testQueryMultipleThrowsAnExceptionOnError()
    {
        $adapter = $this->getYoPdoDriver();

        $sql = <<<SQL
SELECT 1 FROM not_there;
SQL;
        $adapter->queryMultiple($sql);
    }

    /**
     * @covers ::__construct
     * @covers ::selectRowGenerator
     * @covers ::<private>
     */
    public function testSelectRowGeneratorGeneratesResults()
    {
        $adapter = $this->getYoPdoDriver();

        $sql = <<<SQL
SELECT 1 AS col UNION
SELECT 2 AS col UNION
SELECT 3 AS col
SQL;
        $row_generator = $adapter->selectRowGenerator($sql);
        $count = 1;

        foreach ($row_generator as $row) {
            $this->assertEquals($row['col'], $count);
            ++$count;
        }
    }

    /**
     * @covers ::__construct
     * @covers ::selectRowGenerator
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testSelectRowGeneratorThrowsAnExceptionOnError()
    {
        $adapter = $this->getYoPdoDriver();

        $sql = <<<SQL
SELECT 1 FROM not_here;
SQL;

        iterator_to_array($adapter->selectRowGenerator($sql));
    }

    /**
     * @covers ::__construct
     * @covers ::selectOne
     * @covers ::<private>
     */
    public function testSelectOneReturnsResults()
    {
        $adapter = $this->getYoPdoDriver();

        $sql = <<<SQL
SELECT 5 AS col
SQL;
        $this->assertEquals(['col' => '5'], $adapter->selectOne($sql));
    }

    /**
     * @covers ::__construct
     * @covers ::selectOne
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testSelectOneThrowsAnExceptionOnError()
    {
        $adapter = $this->getYoPdoDriver();

        $sql = <<<SQL
SELECT 1 FROM not_there;
SQL;
        $adapter->selectOne($sql);
    }

    /**
     * @covers ::__construct
     * @covers ::insert
     * @covers ::<private>
     */
    public function testInsertedRowCanBeRetrieved()
    {
        $adapter = $this->getYoPdoDriver();

        $tablename = 'test_insert_' . uniqid();

        $sql = <<<SQL
CREATE TABLE {$tablename}
(
    some_id INT NOT NULL,
    some_string VARCHAR NOT NULL
);
SQL;
        $adapter->queryMultiple($sql);

        $row = [
            'some_id'     => '5',
            'some_string' => 'yep',
        ];
        $adapter->insert($tablename, $row);

        $sql = <<<SQL
SELECT *
FROM {$tablename}
SQL;
        $this->assertEquals($row, $adapter->selectOne($sql));
    }

    /**
     * @covers ::__construct
     * @covers ::insert
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testInsertThrowsAnExceptionOnError()
    {
        $adapter = $this->getYoPdoDriver();

        $adapter->insert('some_table', ['no_row' => true]);
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     * @covers ::<private>
     */
    public function testDeletedRowNoLongerExists()
    {
        $adapter = $this->getYoPdoDriver();

        $tablename = 'test_insert_' . uniqid();

        $sql = <<<SQL
CREATE TABLE {$tablename}
(
    some_id INT NOT NULL,
    some_string VARCHAR NOT NULL
);
SQL;
        $adapter->queryMultiple($sql);

        $row = [
            'some_id'     => '5',
            'some_string' => 'yep',
        ];
        $adapter->insert($tablename, $row);

        $sql = <<<SQL
SELECT *
FROM {$tablename}
SQL;
        $this->assertEquals($row, $adapter->selectOne($sql));

        $row = [
            'some_id' => '5',
        ];
        $adapter->delete($tablename, 'some_id = :some_id', $row);

        $sql = <<<SQL
SELECT *
FROM {$tablename}
SQL;
        $this->assertFalse($adapter->selectOne($sql));
    }

    /**
     * @covers ::__construct
     * @covers ::delete
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testDeleteThrowsAnExceptionOnError()
    {
        $adapter = $this->getYoPdoDriver();

        $adapter->delete('some_table', 'no_row = :no_row', ['no_row' => true]);
    }

    /**
     * @return YoPdoDriver
     * @throws Exception
     */
    public function getYoPdoDriver()
    {
        $config_path = __DIR__ . '/../../../../config/config.test.php';
        if (!file_exists($config_path)) {
            throw new Exception("'{$config_path}' not found");
        }

        $config = require $config_path;

        return new YoPdoDriver($config['test']['db']['yo-pdo-pgsql']);
    }
}
