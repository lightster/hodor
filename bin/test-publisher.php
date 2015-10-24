#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hodor\JobQueueFacade as Q;

Q::setConfigFile(__DIR__ . '/../config/config.test.php');
Q::push(
    'some_job_name',
    ['some', 'cool', 'values', date('Y-m-d h:i:s')],
    ['queue_name' => 'default']
);
