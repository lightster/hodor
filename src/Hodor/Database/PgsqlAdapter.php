<?php

namespace Hodor\Database;

use Hodor\Database\Adapter\Postgres\Factory;
use Hodor\Database\Driver\YoPdoDriver;
use Hodor\Database\Phpmig\PgsqlPhpmigAdapter;

class PgsqlAdapter extends ConverterAdapter
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Factory
     */
    private $postgres_factory;

    /**
     * @var YoPdoDriver
     */
    private $driver;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->postgres_factory = new Factory($this, $config);

        parent::__construct($this->postgres_factory);
    }

    /**
     * @return PgsqlPhpmigAdapter
     */
    public function getPhpmigAdapter()
    {
        return new PgsqlPhpmigAdapter($this->getDriver());
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
     * @return YoPdoDriver
     */
    private function getDriver()
    {
        if ($this->driver) {
            return $this->driver;
        }

        $this->driver = $this->postgres_factory->getYoPdoDriver();

        return $this->driver;
    }
}
