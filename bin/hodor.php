#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\DatabaseMigrateCommand;
use Symfony\Component\Console\Application as ConsoleApp;

$app = require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\DaemonGenerateConfigCommand;

$console = new ConsoleApp(
    'hodor utilities'
);

$console->add(new DaemonGenerateConfigCommand());
$console->add(new DatabaseMigrateCommand());

$exit_code = $console->run();

exit($exit_code);
