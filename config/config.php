<?php
return [
    'database' => [
        'type' => 'pgsql',
        'dsn'  => 'host=localhost user=lightster dbname=dev_hodor',
    ],
    'queue_defaults' => [
        'host'         => '127.0.0.1',
        'port'         => 5672,
        'username'     => 'guest',
        'password'     => 'guest',
        'queue_prefix' => 'hodor-',
    ],
    'buffer_queue_defaults' => [
        'queue_prefix'         => 'hodor-buffer-',
        'bufferers_per_server' => 10,
    ],
    'buffer_queues' => [
        'default' => [],
    ],
    'worker_queue_defaults' => [
        'queue_prefix' => 'hodor-worker-',
    ],
    'worker_queues' => [
        'default' => [
            'workers_per_server' => 10,
        ],
    ],
    'job_runner' => function($name, $params) {
        var_dump($name, $params);
    },
];
