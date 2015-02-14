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
     * @var array
     */
    private $processed_config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
     * @return mixed
     */
    private function getOption($option)
    {
        if (null === $this->processed_config) {
            $this->processConfig();
        }

        if (!isset($this->processed_config[$option])) {
            throw new Exception("Undefined config option '{$option}'.");
        }

        return $this->processed_config[$option];
    }

    private function processConfig()
    {
        if (!isset($this->config['database'])) {
            throw new Exception("Required config option '{$option}' not provided.");
        }

        $this->processed_config['database'] = $this->config['database'];
    }
}
