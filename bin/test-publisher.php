#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hodor\JobQueueFacade as Q;
use Hodor\Command\Arguments as Arguments;

$args = new Arguments();
$config_file = $args->getConfigFile();
$queue_name = $args->getQueueName();
$job_name = $args->getJobName();
$job_params = $args->getJobParams();

Q::setConfigFile($config_file);
Q::push(
    $job_name,
    $job_params,
    ['queue_name' => $queue_name]
);
