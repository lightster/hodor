#!/usr/bin/env php
<?php

require_once __DIR__ . '/../bootstrap.php';

use Hodor\Command\Arguments as Arguments;
use Hodor\Config\LoaderFacade as Config;
use Hodor\MessageQueue\QueueFactory as QueueFactory;
use Hodor\WorkerQueue;

$args = new Arguments();
$config = Config::loadFromFile($args->getConfigFile());

$queue_factory = new QueueFactory($config);
$worker_queue = new WorkerQueue($queue_factory->getWorkerQueue('default'));

$worker_queue->runNext();
