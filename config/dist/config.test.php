<?php
return [
    'test' => [
        'db' => [
            'pgsql' => [
                'dsn' => 'host=localhost',
            ],
            'yo-pdo' => [
                'dsn'      => 'pgsql:host=localhost;dbname=test_hodor',
                'username' => 'postgres',
                'password' => '',
            ],
        ],
    ],
];
