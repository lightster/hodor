<?php

namespace Hodor;

use Exception;

class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        if (!isset($this->config['database'])) {
            throw new Exception("Required config option 'database' not provided.");
        }
    }

    /**
     * @return array
     */
    public function getDatabaseConfig()
    {
        return $this->getOption('database');
    }

    /**
     * @param  string $option
     * @param  mixed $default
     * @return mixed
     */
    private function getOption($option, $default = null)
    {
        if (null === $this->config) {
            $this->processConfig();
        }

        if (!array_key_exists($option, $this->config)) {
            $this->config[$option] = $default;
        }

        return $this->config[$option];
    }
}
