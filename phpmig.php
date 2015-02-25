<?php

use Hodor\Config\LoaderFactory as ConfigFactory;
use Hodor\Database\AdapterFactory as DbFactory;

$container = new Pimple();

$container['config'] = $container->share(function() {
    $config_factory = new ConfigFactory();

    return $config_factory->loadFromFile(__DIR__ . '/config/config.php');
});

$container['phpmig.adapter'] = $container->share(function() use ($container) {
    $db_config = $container['config']->getDatabaseConfig();
    $db_factory = new DbFactory($db_config);
    $db_adapter = $db_factory->getAdapter($db_config['type']);

    return $db_adapter->getPhpmigAdapter();
});

$container['phpmig.migrations_path'] = __DIR__ . '/migrations';

return $container;
