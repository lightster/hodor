<?php
return [
    'test' => [
        'db' => [
            'pgsql' => [
                'dsn' => 'host=localhost user=postgres dbname=travisci_hodor',
            ],
            'yo-pdo' => [
                'dsn'      => 'pgsql:host=localhost;dbname=travisci_hodor',
                'username' => 'postgres',
                'password' => '',
            ],
        ],
    ],
];
