<?php

declare(strict_types = 1);

use Database;
use Dotenv\Dotenv;
use RefreshTokenGateway;

require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$database = new Database(host:$_ENV["DB_HOST"], dbname:$_ENV["DB_NAME"], user:$_ENV["DB_USER"], password:$_ENV["DB_PASSWORD"]);

$refreshTokenGateway = new RefreshTokenGateway(database: $database, key: $_ENV["SECRET_KEY"]);

echo $refreshTokenGateway->deletExpired() . "\n";