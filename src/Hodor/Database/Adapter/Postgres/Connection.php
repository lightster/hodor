<?php

namespace Hodor\Database\Adapter\Postgres;

use Hodor\Database\Driver\YoPdoDriver;
use Lstr\YoPdo\YoPdo;

class Connection
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var YoPdoDriver
     */
    private $yo_pdo_driver;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return YoPdoDriver
     */
    public function getYoPdoDriver()
    {
        if ($this->yo_pdo_driver) {
            return $this->yo_pdo_driver;
        }

        $this->yo_pdo_driver = new YoPdoDriver($this->config);

        return $this->yo_pdo_driver;
    }

    /**
     * @return YoPdo
     */
    public function getYoPdo()
    {
        return $this->getYoPdoDriver()->getYoPdo();
    }
}
