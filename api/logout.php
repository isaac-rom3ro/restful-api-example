<?php
/**
 * Logout Endpoint - Revokes refresh tokens to log out users
 * This endpoint invalidates the refresh token to prevent further access
 * Expected request: POST with JSON body containing refresh token
 */

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Validate that the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    header("Allow: POST");
    exit;
}

// Parse the JSON request body to extract the refresh token
$data = (array)(json_decode(file_get_contents("php://input", true)));

// Validate that the refresh token is provided
if(
    ! array_key_exists(key: "token", array: $data)
) {
    http_response_code(response_code: 400); // Bad Request
    echo json_encode(["message" => "missing token"]);
    exit;
}

// Initialize JWT codec for token validation
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

// Decode and validate the refresh token
try {
    $payload = $codec->decode($data["token"]);
} catch (Exception) {
    // Token is invalid (expired, malformed, or tampered)
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "invalid token"]);
    exit;
} 

// Initialize database connection
$database = new Database(
    host: $_ENV["DB_HOST"], 
    dbname: $_ENV["DB_NAME"], 
    user: $_ENV["DB_USER"], 
    password: $_ENV["DB_PASSWORD"]
);

// Create refresh token gateway to revoke the token
$refreshTokenGateway = new RefreshTokenGateway(database: $database, key: $_ENV["SECRET_KEY"]);

// Delete the refresh token from the database
// This prevents the token from being used for future refresh requests
$refreshTokenGateway->delete($data["token"]);