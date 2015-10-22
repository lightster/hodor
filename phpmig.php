<?php

use Hodor\Config\LoaderFactory as ConfigFactory;
use Hodor\Database\AdapterFactory as DbFactory;

$container = new Pimple();

$container['hodor.config.factory'] = $container->share(function() {
    return new ConfigFactory();
});

$container['hodor.config'] = $container->share(function(Pimple $container) {
    $config_path = getenv('HODOR_CONFIG');
    if (!$config_path) {
        throw new Exception(
            "Please provide a config file using a 'HODOR_CONFIG' environment variable."
        );
    }

    return $container['hodor.config.factory']->loadFromFile(getenv('HODOR_CONFIG'));
});

$container['hodor.database.config'] = $container->share(function(Pimple $container) {
    return $container['hodor.config']->getDatabaseConfig();
});

$container['hodor.database.factory'] = $container->share(function(Pimple $container) {
    return new DbFactory($container['hodor.database.config']);
});

$container['hodor.database'] = $container->share(function(Pimple $container) {
    $db_factory = $container['hodor.database.factory'];
    $db_config = $container['hodor.database.config'];
    return $db_factory->getAdapter($db_config['type']);
});

$container['phpmig.adapter'] = $container->share(function(Pimple $container) {
    $db_adapter = $container['hodor.database'];
    return $db_adapter->getPhpmigAdapter();
});

$container['phpmig.migrations_path'] = $container->share(function(Pimple $container) {
    return $container['phpmig.adapter']->getMigrationsPath();
});

$container['phpmig.migrations_template_path'] = $container->share(function(Pimple $container) {
    return __DIR__ . '/src/Hodor/Database/Phpmig/MigrationTemplate.php';
});

return $container;
