<?php

namespace Hodor\Database\Phpmig;

use Exception;
use Hodor\Config\LoaderFactory as ConfigFactory;
use Hodor\Database\AdapterFactory as DbFactory;
use Pimple;

class Container extends Pimple
{
    /**
     * @param string $config_file
     * @throws Exception
     */
    public function addDefaultServices($config_file = null)
    {
        $this->addConfigFileService($config_file);

        $this['hodor.config.factory'] = $this->share(
            function () {
                return new ConfigFactory();
            }
        );

        $this['hodor.config'] = $this->share(
            function (Pimple $container) {
                return $container['hodor.config.factory']->loadFromFile($this['hodor.config.file']);
            }
        );

        $this['hodor.database.config'] = $this->share(
            function (Pimple $container) {
                return $container['hodor.config']->getDatabaseConfig();
            }
        );

        $this['hodor.database.factory'] = $this->share(
            function (Pimple $container) {
                return new DbFactory($container['hodor.database.config']);
            }
        );

        $this['hodor.database'] = $this->share(
            function (Pimple $container) {
                $db_factory = $container['hodor.database.factory'];
                $db_config = $container['hodor.database.config'];

                return $db_factory->getAdapter($db_config['type']);
            }
        );

        $this['phpmig.adapter'] = $this->share(
            function (Pimple $container) {
                $db_adapter = $container['hodor.database'];

                return $db_adapter->getPhpmigAdapter();
            }
        );

        $this['phpmig.migrations_path'] = $this->share(
            function (Pimple $container) {
                return $container['phpmig.adapter']->getMigrationsPath();
            }
        );

        $this['phpmig.migrations_template_path'] = $this->share(
            function (Pimple $container) {
                return __DIR__ . '/MigrationTemplate.php';
            }
        );
    }

    /**
     * @return PgsqlPhpmigAdapter
     */
    public function getPhpmigAdapter()
    {
        return $this['phpmig.adapter'];
    }

    /**
     * @param $config_file
     * @throws Exception
     */
    private function addConfigFileService($config_file)
    {
        if ($config_file) {
            $this['hodor.config.file'] = $config_file;
            return;
        }

        $config_path = getenv('HODOR_CONFIG');
        if (!$config_path) {
            throw new Exception(
                "Please provide a config file using a 'HODOR_CONFIG' environment variable."
            );
        }

        $this['hodor.config.file'] = $config_path;
    }
}
