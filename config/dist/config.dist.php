<?php
return [
    'superqueuer' => [
        'database' => [
            'type' => 'pgsql',
            'dsn'  => 'host=localhost user=hodor dbname=hodor',
        ],
    ],
    'queue_defaults' => [
        'host'            => '127.0.0.1',
        'port'            => 5672,
        'username'        => 'guest',
        'password'        => 'guest',
        'queue_prefix'    => 'hodor-',
        'connection_type' => 'stream',
    ],
    'buffer_queue_defaults' => [
        'queue_prefix'         => 'hodor-buffer-',
        'bufferers_per_server' => 10,
        'superqueuer'          => 'default',
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
    'daemon' => [
        'type'           => 'supervisord',
        'config_path'    => '/etc/supervisord/conf.d/hodor.conf',
        'process_owner'  => 'apache',
        'program_prefix' => 'hodor',
        'logs'           => [
            'error' => [
                'path'         => '/var/log/hodor/%(program_name)s_%(process_num)d.error.log',
                'max_size'     => '10MB',
                'rotate_count' => 2,
            ],
            'debug' => [
                'path'         => '/var/log/hodor/%(program_name)s_%(process_num)d.debug.log',
                'max_size'     => '10MB',
                'rotate_count' => 2,
            ],
        ],
    ],
];
