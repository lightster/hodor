<?php

namespace Hodor\Database\Driver;

use Exception;

class PgsqlDriver
{
    /**
     * @var array
     */
    private $config;

    /**
     * connection returned by pg_connect()
     * @var resource
     */
    private $connection;

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
        $result = @pg_query($this->getConnection(), $sql);

        if (($error = $this->hasError($result))) {
            throw new Exception(
                "A query error occurred!\nError: {$error}\nQuery: {$sql}"
            );
        }
    }

    /**
     * @param  string $value
     * @return string
     */
    public function escapeValue($value)
    {
        return pg_escape_literal($value);
    }

    /**
     * @param  string $sql
     * @return callable generator
     */
    public function selectRowGenerator($sql)
    {
        $result = @pg_query($this->getConnection(), $sql);
        if (($error = $this->hasError($result))) {
            throw new Exception(
                "A query error occurred!\nError: {$error}\nQuery: {$sql}"
            );
        }

        return function () use ($result) {
            while ($row = pg_fetch_assoc($result)) {
                yield $row;
            }
        };
    }

    /**
     * @param  string $sql
     * @return callable
     */
    public function selectOne($sql)
    {
        $result = @pg_query($this->getConnection(), $sql);
        if (($error = $this->hasError($result))) {
            throw new Exception(
                "A query error occurred!\nError: {$error}\nQuery: {$sql}"
            );
        }

        return pg_fetch_assoc($result);
    }

    /**
     * @param string $table
     * @param array $row
     * @return void
     */
    public function insert($table, array $row)
    {
        $result = pg_insert($this->getConnection(), $table, $row);

        if (($error = $this->hasError($result))) {
            throw new Exception(
                "Inserting into '{$table}' failed!\nError: {$error}\nData:\n"
                . var_export($row, true)
            );
        }
    }

    /**
     * @param string $table
     * @param array $condition
     * @return void
     */
    public function delete($table, array $condition)
    {
        $result = pg_delete($this->getConnection(), $table, $condition);

        if (($error = $this->hasError($result))) {
            throw new Exception(
                "Deleting from '{$table}' failed!\nError: {$error}\nCondition:\n"
                . var_export($condition, true)
            );
        }
    }

    /**
     * @return resource
     */
    public function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        if (empty($this->config['dsn'])) {
            throw new Exception("The 'dsn' part of the database config was not provided.");
        }

        $this->connection = pg_connect($this->config['dsn']);

        if (!$this->connection) {
            // TODO: figure out how to get the error message
            // $error = pg_last_error();
            $error = '';
            throw new Exception(
                "Could not connect to Postgres server with given DSN:\n  {$error}"
            );
        }

        return $this->connection;
    }

    /**
     * @param  resource $result
     * @return void
     */
    private function hasError($result)
    {
        if (false === $result) {
            return pg_last_error($this->getConnection()) ?: true;
        }

        return false;
    }
}
