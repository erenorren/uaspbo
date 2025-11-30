<?php

return [
    'host' => 'localhost',
    'database' => 'elearning_db',
    'username' => 'root',
    'password' => 'Sh3Belajar!SQL',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];