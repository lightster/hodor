<?php

namespace Hodor\Config;

use Hodor\JobQueue\Config;

use Exception;

class PhpConfigLoader implements LoaderInterface
{
    /**
     * @param  string $file_path
     * @return \Hodor\JobQueue\Config
     */
    public function loadFromFile($file_path)
    {
        if (!file_exists($file_path)) {
            throw new Exception("Config file '{$file_path}' does not exist.");
        }

        $config_array = require $file_path;

        if (!is_array($config_array)) {
            throw new Exception("Config file '{$file_path}' does not return a PHP array.");
        }

        return new Config($file_path, $config_array);
    }
}
