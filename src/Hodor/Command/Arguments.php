<?php

namespace Hodor\Command;

use Exception;

class Arguments
{
    /**
     * @var callable
     */
    private $cli_opts_loader;

    /**
     * @var array
     */
    private $loaded_arguments = [];

    /**
     * @return string
     */
    public function getConfigFile()
    {
        return $this->getRequiredArgument('config');
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->getRequiredArgument('queue');
    }

    /**
     * @return boolean
     */
    public function isJson()
    {
        $this->processArguments();

        return array_key_exists('json', $this->loaded_arguments);
    }

    /**
     * @param callable $cli_opts_loader
     */
    public function setCliOptsLoader(callable $cli_opts_loader)
    {
        $this->cli_opts_loader = $cli_opts_loader;
    }

    /**
     * @param  string $name
     * @return string
     * @throws Exception
     */
    private function getRequiredArgument($name)
    {
        $this->processArguments();

        if (empty($this->loaded_arguments[$name])) {
            throw new Exception("Argument '{$name}' is required.");
        }

        return $this->loaded_arguments[$name];
    }

    /**
     * @return void
     */
    private function processArguments()
    {
        if ($this->loaded_arguments) {
            return;
        }

        $args_loader = $this->getCliOptsLoader();
        $args = $args_loader();

        $this->processArgument($args, 'config', 'c');
        $this->processArgument($args, 'queue', 'q');
        $this->processArgument($args, 'json', '');
    }

    /**
     * @return callable
     */
    private function getCliOptsLoader()
    {
        if ($this->cli_opts_loader) {
            return $this->cli_opts_loader;
        }

        $this->cli_opts_loader = function () {
            return getopt(
                'c:q:',
                [
                    'config:',
                    'queue:',
                    'json',
                ]
            );
        };

        return $this->cli_opts_loader;
    }

    /**
     * @param  array  $args
     * @param  string $long
     * @param  string $short
     */
    private function processArgument(array $args, $long, $short)
    {
        if (array_key_exists($long, $args)) {
            $this->loaded_arguments[$long] = $args[$long];
        } elseif (array_key_exists($short, $args)) {
            $this->loaded_arguments[$long] = $args[$short];
        }
    }
}
