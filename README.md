Hodor
=====

[![Build Status](https://travis-ci.org/lightster/hodor.svg?branch=master)](https://travis-ci.org/lightster/hodor)
[![Test Coverage](https://codeclimate.com/github/lightster/hodor/badges/coverage.svg)](https://codeclimate.com/github/lightster/hodor/coverage)
[![Code Climate](https://codeclimate.com/github/lightster/hodor/badges/gpa.svg)](https://codeclimate.com/github/lightster/hodor)

A worker queue that is evolving to a job queue

## Requirements

 - PHP >= 5.5.18
 - Composer
 - Supervisord
 - Postgres >= 9.3
 - RabbitMQ

## Configuration

Install Hodor in your application via composer:

```bash
composer require lightster/hodor:^0.0.2
```

Create a database on your Postgres server to use with your
instance of Hodor:
```sql
CREATE DATABASE hodor;
```

Copy the Hodor distribution config to wherever you keep your
application configs:

```bash
cp vendor/lightster/hodor/config/dist/config.dist.php config/hodor.php
```

Update the Postgres and RabbitMQ credentials in your config file.

Write your job runner bootstrap in the `job_runner` key of the config
file.  The method defined here will be called with the job name and
job params any time a worker receives a job message.  This method
should not be more than a few lines—anything more than that should
be offloaded into a bootstrap include script or class.  An example
job runner may look like:

```php
<?php
return [
    'job_runner' => function($name, $params) {
        $container = require_once __DIR__ . '/../bootstrap.php';
        $job_runner = $container['job_runner'];
        $job_runner->runJob($name, $params);
    },
];
```

Run the database migrations after your database credentials
are setup in your config:
```
bin/hodor.php database:migrate config/hodor.php
```

Then setup supervisord to manage your job queue processes:

```bash
sudo php bin/supervisord-config-gen.php --config=config/hodor.php
sudo service supervisord reload
```

## Usage

```php
use Hodor\JobQueue\JobQueue;

$job_queue = new JobQueue();
$job_queue->setConfigFile(__DIR__ . '/../../../config/hodor.php');
$job_queue->push(
    'Vendor\Project\SomeJob',           // job_name
    ['number' => 123, 'name' => 'Bob'], // job_params
    ['queue_name' => 'default']         // job_options
);
```
