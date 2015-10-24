<?php
return [
    'test' => [
        'db' => [
            'pgsql' => [
                'dsn' => 'host=localhost user=postgres dbname=travisci_hodor',
            ],
        ],
        'rabbitmq' => [
            'host'            => '127.0.0.1',
            'port'            => 5672,
            'username'        => 'guest',
            'password'        => 'guest',
            'queue_prefix'    => 'test-hodor-',
        ],
    ],
];
