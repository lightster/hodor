<?php

namespace Hodor\Config;

use Exception;
use Hodor\JobQueue\Config;

class PhpConfigLoader implements LoaderInterface
{
    /**
     * @param  string $file_path
     * @return Config
     * @throws Exception
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
