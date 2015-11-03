<?php
return [
    'test' => [
        'db' => [
            'yo-pdo-pgsql' => [
                'dsn'      => 'pgsql:host=localhost;dbname=travisci_hodor',
                'username' => 'postgres',
                'password' => '',
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
