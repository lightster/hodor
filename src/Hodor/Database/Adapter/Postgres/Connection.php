<?php

namespace Hodor\Database\Adapter\Postgres;

use Lstr\YoPdo\Factory as YoPdoFactory;
use Lstr\YoPdo\YoPdo;

class Connection
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
     * @return YoPdo
     */
    public function getYoPdo()
    {
        if ($this->yo_pdo) {
            return $this->yo_pdo;
        }

        $factory = new YoPdoFactory();
        $this->yo_pdo = $factory->createFromConfig($this->config);

        return $this->yo_pdo;
    }
}
