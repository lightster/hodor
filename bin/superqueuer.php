#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\JobQueue\QueueManager;

$args = new Arguments();
$config_file = $args->getConfigFile();

$config = Config::loadFromFile($config_file);
$queue_manager = new QueueManager($config);
$superqueue = $queue_manager->getSuperqueue();

if (!$superqueue->requestProcessLock()) {
    sleep(5);
} elseif (!$superqueue->queueJobsFromDatabaseToWorkerQueue()) {
    sleep(2);
}
