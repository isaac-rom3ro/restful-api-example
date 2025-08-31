<?php
/**
 * Bootstrap file - Initializes the application environment and configuration
 * This file is included at the beginning of all API endpoints to set up:
 * - Autoloading for classes
 * - Error handling configuration
 * - Environment variables loading
 * - Response headers setup
 */

// Load Composer's autoloader to automatically load classes from the src/ directory
require_once "../vendor/autoload.php";

// Enable error display for development (should be disabled in production)
ini_set("display_errors", "On"); 

// Set the response content type to JSON with UTF-8 encoding
// This ensures all API responses are returned as JSON
header("Content-Type: application/json;charset= UTF-8");

// Set up custom error handler to catch PHP errors and convert them to exceptions
// This ensures all errors are handled consistently through our exception handler
set_error_handler("ErrorHandler::handleError");

// Set up custom exception handler to catch all unhandled exceptions
// This provides a consistent JSON error response format for all exceptions
set_exception_handler("ErrorHandler::handleException"); 

// Load environment variables from .env file
// This loads database credentials, secret keys, and other configuration
// The dirname(__DIR__) gets the parent directory (project root) where .env is located
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();