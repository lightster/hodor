<?php

namespace Hodor\Database;

use Closure;
use Exception;
use Hodor\Database\Adapter\FactoryInterface;
use Hodor\Database\Adapter\Postgres\Factory as PostgresFactory;
use Hodor\Database\Adapter\Testing\Database;
use Hodor\Database\Adapter\Testing\Factory as TestingFactory;

class AdapterFactory
{
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

        $adapter_factory = $this->getAdapterFactory($config['type']);

        return $adapter_factory($config);
    }

    /**
     * @param string $type
     * @return Closure
     * @throws Exception
     */
    private function getAdapterFactory($type)
    {
        if ('pgsql' === $type) {
            return function (array $config) {
                return new PostgresFactory($config);
            };
        }

        if ('testing' === $type) {
            return function (array $config) {
                $config = array_merge(
                    [
                        'database'      => new Database(),
                        'connection_id' => 1,
                    ],
                    $config
                );

                return new TestingFactory($config['database'], $config['connection_id']);
            };
        }

        throw new Exception("A database adapter factory is not associated with '{$type}'.");
    }
}
