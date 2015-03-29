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
            ]
        );

        $this->processArgument($args, 'config', 'c');
        $this->processArgument($args, 'queue', 'q');
    }

    /**
     * @param  array  $args
     * @param  string $long
     * @param  string $short
     */
    private function processArgument(array $args, $long, $short)
    {
        if (!empty($args[$long])) {
            $this->loaded_arguments[$long] = $args[$long];
        } elseif (!empty($args[$short])) {
            $this->loaded_arguments[$long] = $args[$short];
        }
    }
}
