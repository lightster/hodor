<?php

namespace Hodor\Command;

use Exception;

class Arguments
{
    /**
     * @var array
     */
    private $loaded_arguments;

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
     * @param  string $name
     * @return string
     */
    private function getRequiredArgument($name)
    {
        $this->processArguments();

        if (empty($this->loaded_arguments[$name])) {
            throw new Exception("Argument '{$name}' is required.");
        }

        return $this->loaded_arguments[$name];
    }

    private function processArguments()
    {
        if ($this->loaded_arguments) {
            return;
        }

        $args = getopt(
            'c:q:',
            [
                'config:',
                'queue:',
                'json',
            ]
        );

        $this->processArgument($args, 'config', 'c');
        $this->processArgument($args, 'queue', 'q');
        $this->processArgument($args, 'json', '');
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
