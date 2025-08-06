<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return [
    'database' => [
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'dbname' => $_ENV['DB_NAME']
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'],
        'expiry' => $_ENV['JWT_EXPIRY']
    ]
];