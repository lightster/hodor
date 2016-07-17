#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\JobQueue\QueueManager;

$args = new Arguments();
$config_file = $args->getConfigFile();
$queue_name = $args->getQueueName();

$config = Config::loadFromFile($config_file);
$queue_manager = new QueueManager($config);
$worker_queue = $queue_manager->getWorkerQueue($queue_name);

$worker_queue->runNext($config->getJobRunnerFactory());
