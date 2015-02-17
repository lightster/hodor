<?php

namespace Hodor\Database;

use Exception;

class AdapterFactory
{
    /**
     * @var array
     */
    private $adapter_factories = [
        'pgsql' => '\Hodor\Database\PgsqlAdapter',
    ];

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
    }

    /**
     * @param  string $name
     * @return \Hodor\Config\LoaderInterface
     */
    public function getAdapter($name)
    {
        if (!isset($this->adapter_factories[$name])) {
            throw new Exception(
                "A database adapter factory is not associated with '{$name}'."
            );
        }

        $adapter_class = $this->adapter_factories[$name];

        return new $adapter_class($this->config);
    }
}
