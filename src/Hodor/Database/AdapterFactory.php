<?php

namespace Hodor\Database;

use Exception;
use Hodor\Database\Adapter\FactoryInterface;

class AdapterFactory
{
    /**
     * @var array
     */
    private $adapter_factories = [
        'pgsql' => '\Hodor\Database\Adapter\Postgres\Factory',
    ];

    /**
     * @param  array $config
     * @return FactoryInterface
     * @throws Exception
     */
    public function getAdapter(array $config)
    {
        if (empty($config['type'])) {
            throw new Exception(
                "The database connection 'type' must provided in connection config."
            );
        }

        $name = $config['type'];
        if (!isset($this->adapter_factories[$name])) {
            throw new Exception(
                "A database adapter factory is not associated with '{$name}'."
            );
        }

        $adapter_class = $this->adapter_factories[$name];

        return new $adapter_class($config);
    }
}
