#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\JobQueue\QueueFactory as QueueFactory;

$args = new Arguments();
$config_file = $args->getConfigFile();

$config = Config::loadFromFile($config_file);
$queue_factory = new QueueFactory($config);
$superqueue = $queue_factory->getSuperqueue();

if (!$superqueue->requestProcessLock()) {
    sleep(5);
}

if (!$superqueue->queueJobsFromDatabaseToWorkerQueue()) {
    sleep(2);
}
