#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\Daemon\ManagerFactory;

$args = new Arguments();
$config_file = $args->getConfigFile();

$config = Config::loadFromFile($config_file);
$daemonizer = new ManagerFactory($config);
$manager = $daemonizer->getManager();
if ($args->isJson()) {
    echo json_encode($manager->getDaemonConfig()) . "\n";
} else {
    $manager->setupDaemon();
}
