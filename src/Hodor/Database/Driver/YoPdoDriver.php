<?php

namespace Hodor\Database\Driver;

use Lstr\YoPdo\Factory as YoPdoFactory;
use Lstr\YoPdo\YoPdo;

class YoPdoDriver
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var YoPdo
     */
    private $yo_pdo;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $sql
     * @return void
     */
    public function queryMultiple($sql)
    {
        return $this->getYoPdo()->queryMultiple($sql);
    }

    /**
     * @param  string $sql
     * @return callable generator
     */
    public function selectRowGenerator($sql)
    {
        $result = $this->getYoPdo()->query($sql);

        return function () use ($result) {
            while ($row = $result->fetch()) {
                yield $row;
            }
        };
    }

    /**
     * @param  string $sql
     * @param  array $params
     * @return callable
     */
    public function selectOne($sql, $params = array())
    {
        $result = $this->getYoPdo()->query($sql, $params);

        return $result->fetch();
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public function insert($table, array $row)
    {
        $this->getYoPdo()->insert($table, $row);
    }

    /**
     * @param string $table
     * @param array $condition
     * @return void
     */
    public function delete($table, array $condition)
    {
        $placeholders = array();
        foreach ($condition as $column => $value) {
            $placeholders[] = "{$column} = :{$column}";
        }

        $placeholder_sql = implode("AND\n", $placeholders);

        $this->getYoPdo()->query(
            "
                DELETE FROM {$table}
                WHERE {$placeholder_sql}
            ",
            $condition
        );
    }

    /**
     * @return YoPdo
     */
    private function getYoPdo()
    {
        if ($this->yo_pdo) {
            return $this->yo_pdo;
        }

        $factory = new YoPdoFactory();
        $this->yo_pdo = $factory->createFromConfig($this->config);

        return $this->yo_pdo;
    }
}
