<?php

namespace Hodor\Database;

use Exception;

class PgsqlAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $config;

    /**
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

    public function createJob($job)
    {
    }

    public function getJobsToRun()
    {
    }

    public function markJobAsStarted($job)
    {
    }

    public function markJobAsCompleted($job)
    {
    }

    public function markJobAsFailed($job)
    {
    }

    public function getPhpmigAdapter()
    {
    }

    public function beginTransaction()
    {
    }

    public function commitTransaction()
    {
    }

    public function rollbackTransaction()
    {
    }

    public function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        if (empty($this->config['dsn'])) {
            throw new Exception("The 'dsn' part of the database config was not provided.");
        }

        $this->connection = @pg_connect($this->config['dsn']);

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
}
