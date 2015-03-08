#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\JobQueue\QueueFactory as QueueFactory;

$args = new Arguments();
$config = Config::loadFromFile($args->getConfigFile());

$queue_factory = new QueueFactory($config);
$worker_queue = $queue_factory->getWorkerQueue('default');

$worker_queue->runNext();
