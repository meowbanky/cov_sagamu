<?php
// auth_api/config/config.php
//
// Secrets are read from the .env at the cov_admin root — never hardcoded here.
// This file previously carried literal database and JWT credentials, which
// reached a public git history; keep it free of literal values.

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$required = ['DB_HOST', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD', 'JWT_SECRET'];
foreach ($required as $key) {
    if (empty($_ENV[$key])) {
        error_log("config: missing required environment variable {$key}");
        throw new RuntimeException("Server configuration incomplete: {$key} is not set");
    }
}

return [
    'database' => [
        'host'     => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'dbname'   => $_ENV['DB_NAME'],
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'],
        'expiry' => isset($_ENV['JWT_EXPIRY']) ? (int) $_ENV['JWT_EXPIRY'] : 3600,
    ],
];
