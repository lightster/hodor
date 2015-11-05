#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hodor\JobQueueFacade as Q;
use Hodor\Command\Arguments as Arguments;

$args = new Arguments();
$config_file = $args->getConfigFile();
$queue_name = $args->getQueueName();

Q::setConfigFile($config_file);
Q::push(
    'some_job_name',
    ['some', 'cool', 'values', date('Y-m-d h:i:s')],
    ['queue_name' => $queue_name]
);
