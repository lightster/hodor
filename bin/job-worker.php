#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hodor\Config\LoaderFacade as Config;
use Hodor\MessageQueue\QueueFactory as QueueFactory;
use Hodor\WorkerQueue;

$config = Config::loadFromFile(__DIR__ . '/../config/config.php');

$queue_factory = new QueueFactory($config);
$worker_queue = new WorkerQueue($queue_factory->getWorkerQueue('default'));

$worker_queue->runNext();
