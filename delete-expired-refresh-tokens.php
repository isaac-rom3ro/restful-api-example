<?php
/**
 * Cleanup Script for Expired Refresh Tokens
 * This script should be run periodically (e.g., via cron job) to clean up expired tokens
 * Removes refresh tokens that have passed their expiration time
 * Helps maintain database performance and security
 */

declare(strict_types = 1);

use Database;
use Dotenv\Dotenv;
use RefreshTokenGateway;

// Load Composer autoloader
require __DIR__ . "/vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize database connection
$database = new Database(
    host: $_ENV["DB_HOST"], 
    dbname: $_ENV["DB_NAME"], 
    user: $_ENV["DB_USER"], 
    password: $_ENV["DB_PASSWORD"]
);

// Create refresh token gateway for cleanup operations
$refreshTokenGateway = new RefreshTokenGateway(
    database: $database, 
    key: $_ENV["SECRET_KEY"]
);

// Delete expired tokens and output the count
echo $refreshTokenGateway->deletExpired() . "\n";