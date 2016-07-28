<?php

namespace Hodor\Database\Adapter\Testing;

use Exception;

class Database
{
    /**
     * @var int
     */
    private $current_id;

    /**
     * @var array
     */
    private $locks = [];

    /**
     * @var array
     */
    private $connection_locks = [];

    /**
     * @var array
     */
    private $tables = [
        'buffered_jobs' => [],
        'queued_jobs'   => [],
    ];

    /**
     * @param string $table_name
     * @param string $row_id
     * @param array $row
     * @throws Exception
     */
    public function insert($table_name, $row_id, $row)
    {
        if (isset($this->tables[$table_name][$row_id])) {
            throw new Exception("Row with id '{$row_id}' already exists in '{$table_name}'.");
        }

        $this->tables[$table_name][$row_id] = $row;
    }

    /**
     * @param string $table_name
     * @param string $row_id
     * @throws Exception
     */
    public function delete($table_name, $row_id)
    {
        if (!isset($this->tables[$table_name][$row_id])) {
            throw new Exception("Row with id '{$row_id}' does not exist in '{$table_name}'.");
        }

        unset($this->tables[$table_name][$row_id]);
    }

    /**
     * @param string $table_name
     * @param string $row_id
     * @return bool
     */
    public function has($table_name, $row_id)
    {
        return isset($this->tables[$table_name][$row_id]);
    }

    /**
     * @param string $table_name
     * @return array
     * @throws Exception
     */
    public function getAll($table_name)
    {
        if (!isset($this->tables[$table_name])) {
            throw new Exception("Table named '{$table_name}' does not exist.");
        }

        return $this->tables[$table_name];
    }

    /**
     * @param int $connection_id
     */
    public function releaseAdvisoryLocks($connection_id)
    {
        if (!isset($this->connection_locks[$connection_id])) {
            return;
        }

        foreach ($this->connection_locks[$connection_id] as $lock) {
            unset($this->locks[$lock[0]][$lock[1]]);
        }

        unset($this->connection_locks[$connection_id]);
    }

    /**
     * @param int $connection_id
     * @param string $category
     * @param string $name
     * @return bool
     */
    public function requestAdvisoryLock($connection_id, $category, $name)
    {
        if (isset($this->locks[$category][$name])) {
            return $this->locks[$category][$name] == $connection_id;
        }

        $this->locks[$category][$name] = $connection_id;
        $this->connection_locks[$connection_id][] = [$category, $name];

        return true;
    }

    /**
     * @return int
     */
    public function allocateId()
    {
        return ++$this->current_id;
    }
}
