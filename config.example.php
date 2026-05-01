<?php

return [
    'app' => [
        'name' => 'VentiPOS',
        'version' => '1.0.0',
        'debug' => true,
        'url' => 'http://your-domain.com',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'ventipos',
        'username' => 'your_db_user',
        'password' => 'your_db_password',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'session' => [
        'lifetime' => 86400,
    ],
];
