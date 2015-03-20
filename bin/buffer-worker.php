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
$buffer_worker = $queue_factory->getBufferQueue($queue_name);

$buffer_worker->processBuffer();
