<?php
/**
 * Token Generation Logic - Creates JWT access and refresh tokens
 * This file is included by login.php and refresh.php to generate tokens
 * Uses JWT (JSON Web Tokens) for secure stateless authentication
 */

// Create the payload for the access token
// JWT payload contains claims (statements about the user)
// Standard JWT claims:
// - "sub" (subject): User ID - standard JWT claim for user identification
// - "username": Custom claim for username
// - "exp" (expiration): Token expiration timestamp
$payload = [
    "sub" => $user["id"],           // User ID from database
    "username" => $user["username"], // Username for convenience
    "exp" => time() + 20            // Expires in 20 seconds (very short for security)
];

// Generate the JWT access token
// The JWT codec handles the encoding, signing, and formatting
$access_token = $codec->encode($payload);

// Set refresh token expiration (5 days = 432000 seconds)
// Refresh tokens have longer expiration than access tokens
$refresh_token_expiry = time() + 432000;

// Create refresh token payload
// Refresh tokens only need user ID and expiration
// They don't need username or other claims for security
$refresh_token = $codec->encode([
    "sub" => $user["id"],           // User ID
    "exp" => $refresh_token_expiry  // Expiration time
]);

// Return both tokens as JSON response
// Client should store these tokens securely
echo json_encode([
    "access_token" => $access_token,   // Short-lived token for API access
    "refresh_token" => $refresh_token  // Long-lived token for getting new access tokens
]); 