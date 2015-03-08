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

        if (!empty($args['config'])) {
            $this->loaded_arguments['config'] = $args['config'];
        } elseif (!empty($args['c'])) {
            $this->loaded_arguments['config'] = $args['c'];
        }

        if (!empty($args['queue'])) {
            $this->loaded_arguments['queue'] = $args['queue'];
        } elseif (!empty($args['q'])) {
            $this->loaded_arguments['queue'] = $args['q'];
        }
    }
}
