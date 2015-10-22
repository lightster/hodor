<?php

namespace Hodor\Database;

use Hodor\Database\Driver\PgsqlDriver;
use Hodor\Database\Phpmig\PgsqlPhpmigAdapter;

use Exception;

class PgsqlAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var PgsqlDriver
     */
    private $driver;

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
        return new PgsqlPhpmigAdapter($this->getDriver());
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

    /**
     * @param string $sql
     * @return void
     */
    public function queryMultiple($sql)
    {
        return $this->getDriver()->queryMultiple($sql);
    }

    /**
     * @return PgsqlDriver
     */
    private function getDriver()
    {
        if ($this->driver) {
            return $this->driver;
        }

        $this->driver = new PgsqlDriver($this->config);

        return $this->driver;
    }
}
