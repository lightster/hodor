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
     * @param  array $config
     * @return AdapterInterface
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
