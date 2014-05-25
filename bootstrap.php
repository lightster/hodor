<?php

require_once __DIR__ . '/vendor/autoload.php';

use Lstr\Silex\Config\ConfigServiceProvider;
use Lstr\Silex\Database\DatabaseServiceProvider;
use Lstr\Silex\Template\TemplateServiceProvider;
use Silex\Application;

$app = new Application();

// lstr-silex components
$app->register(new ConfigServiceProvider());
$app->register(new DatabaseServiceProvider());
$app->register(new TemplateServiceProvider());

$app['config'] = $app['lstr.config']->load(array(
    __DIR__ . '/config/autoload/*.global.php',
    __DIR__ . '/config/autoload/*.local.php',
));

if (isset($app['config']['debug'])) {
    $app['debug'] = $app['config']['debug'];
}

$app['job-queue.db'] = $app['lstr.db'](function (Application $app) {
    return $app['config']['job-queue.db.config'];
});

return $app;
