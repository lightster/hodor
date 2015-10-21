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
echo json_encode($daemonizer->getManager()->getDaemonConfig());

