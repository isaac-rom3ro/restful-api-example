<?php
/**
 * Main API Router - Handles all task-related requests
 * This file serves as the entry point for the RESTful API
 * It processes incoming requests, authenticates users, and routes to appropriate controllers
 */

require_once "./bootstrap.php";

// Parse the request URL to extract the resource and ID
// $_SERVER["REQUEST_URI"] contains the full path (e.g., /api/tasks/123)
// parse_url extracts just the path component
$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Split the URL into parts to extract resource and ID
// Example: /api/tasks/123 becomes ["", "api", "tasks", "123"]
$parts = explode("/", $url); 

// Extract the resource (e.g., "tasks") and optional ID (e.g., "123")
// $parts[3] is the resource because URL structure is /api/tasks/123
// $parts[4] ?? null uses null coalescing to set ID to null if not provided
$resource = $parts[3];
$id = $parts[4] ?? null; 

// Validate that the requested resource is supported
// Currently only "tasks" resource is implemented
if($resource != "tasks") { 
    // Return 404 Not Found for unsupported resources
    http_response_code(404); 
    exit; 
}

// Initialize database connection using environment variables
// This creates a secure connection to the MySQL database
$database = new Database(
    host: $_ENV["DB_HOST"], 
    dbname: $_ENV["DB_NAME"], 
    user: $_ENV["DB_USER"], 
    password: $_ENV["DB_PASSWORD"]
);

// Create user gateway for user-related database operations
$user_gateway = new UserGateway(database: $database);

// Initialize JWT codec for token encoding/decoding
// Uses the secret key from environment variables for security
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// Create authentication handler
// This will validate JWT access tokens in the Authorization header
$auth = new Auth(user_gateway: $user_gateway, codec: $codec);

// Authenticate the request using the access token
// If authentication fails, the method will output error and exit
if(! $auth->authenticateAccessToken()) {
    exit;
}

// Get the authenticated user's ID from the JWT token
// This ensures users can only access their own tasks
$user_id = $auth->getUserId();

// Create task gateway for task-related database operations
$taskGateway = new TaskGateway(database: $database);

// Create task controller to handle the business logic
// Pass the user_id to ensure data isolation between users
$controller = new TaskController(gateway: $taskGateway, user_id: $user_id);

// Process the request based on HTTP method and resource ID
// The controller will handle GET, POST, PATCH, DELETE operations
$controller->processRequest(method: $requestMethod, id: $id);