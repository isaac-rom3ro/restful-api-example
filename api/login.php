<?php
/**
 * Login Endpoint - Handles user authentication and token generation
 * This endpoint validates user credentials and issues JWT access and refresh tokens
 * Expected request: POST with JSON body containing username and password
 */

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Validate that the request method is POST
// Login should only accept POST requests for security
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    header("Allow: POST");
    exit;
}

// Parse the JSON request body to extract login credentials
// php://input contains the raw POST data
$data = (array)(json_decode(file_get_contents("php://input", true)));

// Validate that both username and password are provided
// Return 400 Bad Request if credentials are missing
if(
    ! array_key_exists(key: "username", array: $data) ||
    ! array_key_exists(key: "password", array: $data)
) {
    http_response_code(response_code: 400);
    echo json_encode(["message" => "missing login credentials"]);
    exit;
}

// Initialize database connection for user authentication
$database = new Database(
    host: $_ENV["DB_HOST"],
    dbname: $_ENV["DB_NAME"], 
    user: $_ENV["DB_USER"], 
    password: $_ENV["DB_PASSWORD"]
);

// Create user gateway to access user data
$user_gateway = new UserGateway(database: $database);

// Retrieve user by username from database
$user = $user_gateway->getByUserName(username: $data["username"]);

// Security: Use same error message for both invalid username and password
// This prevents username enumeration attacks
if($user === false) {
    http_response_code(response_code: 401); // Unauthorized
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

// Verify the provided password against the stored hash
// password_verify() handles the secure comparison
if(! password_verify(password: $data["password"], hash: $user["password_hash"])) {
    http_response_code(response_code: 401); // Unauthorized
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

// Initialize JWT codec for token generation
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// Include token generation logic
// This file contains the code to create access and refresh tokens
require_once __DIR__ . "/tokens.php";

// Create refresh token gateway to store refresh tokens securely
$refreshTokenGateway = new RefreshTokenGateway(database: $database, key: $_ENV["SECRET_KEY"]);

// Store the refresh token in the database for later validation
// This allows us to revoke tokens on logout
$refreshTokenGateway->create(token: $refresh_token, expiry: $refresh_token_expiry);