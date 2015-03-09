#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\JobQueue\QueueFactory as QueueFactory;

$args = new Arguments();
$config_file = $args->getConfigFile();
$queue_name = $args->getQueueName();

$config = Config::loadFromFile($config_file);
$queue_factory = new QueueFactory($config);
$worker_queue = $queue_factory->getWorkerQueue($queue_name);

$worker_queue->runNext();
