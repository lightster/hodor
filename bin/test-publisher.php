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

$job_options = ['queue_name' => $queue_name];

if (!empty($job_params['job_options']['run_after'])) {
    $job_options['run_after'] = new \DateTime($job_params['job_options']['run_after']);
}
if (!empty($job_params['job_options']['job_rank'])) {
    $job_options['job_rank'] = $job_params['job_options']['job_rank'];
}

Q::setConfigFile($config_file);
Q::push(
    $job_name,
    $job_params,
    $job_options
);
