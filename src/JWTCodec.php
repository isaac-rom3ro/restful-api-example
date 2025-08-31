<?php
/**
 * JWT (JSON Web Token) Codec Class
 * Handles encoding and decoding of JWT tokens for secure authentication
 * Implements HMAC-SHA256 signing for token integrity
 * Uses base64url encoding for URL-safe token transmission
 */

declare(strict_types = 1);

class JWTCodec {

    private string $secretKey;

    /**
     * Constructor - Stores the secret key for token signing
     * @param string $secretKey Secret key used for HMAC signing
     */
    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Encode data into a JWT token
     * @param array $payload The data to encode in the token
     * @return string The encoded JWT token
     */
    public function encode(array $payload): string 
    {
        // Create JWT header with token type and algorithm
        $header = json_encode([
            "typ" => "JWT",    // Token type
            "alg" => "HS256"   // HMAC SHA256 algorithm
        ]);
        $header = $this->base64urlEncode($header);

        // Encode the payload (user data)
        $payload = json_encode([$payload]);
        $payload = $this->base64urlEncode($payload);
        
        // Create signature using HMAC-SHA256
        // Signature = HMAC(header.payload, secret_key)
        $signature = hash_hmac(
            "sha256",
            $header . "." . $payload,
            $this->secretKey, 
            true  // Return raw binary data
        );
        $signature = $this->base64urlEncode($signature);

        // JWT format: header.payload.signature
        return $header . "." . $payload . "." . $signature;
    }

    /**
     * Decode and validate a JWT token
     * @param string $token The JWT token to decode
     * @return array The decoded payload
     * @throws InvalidArgumentException If token format is invalid
     * @throws InvalidSignatureException If signature doesn't match
     * @throws TokenExpiredException If token has expired
     */
    public function decode(string $token): array
    {   
        // Validate JWT format using regex
        // JWT should have 3 parts separated by dots
        if (
            preg_match(
                '/^(?P<header>.+)\.(?P<payload>.+)\.(?P<signature>.+)$/', 
                $token, 
                $matches
            ) !== 1
        ) {
            throw new InvalidArgumentException("invalid token format");
        }

        // Recreate signature to verify token integrity
        $signature = hash_hmac(
            "sha256",
            $matches["header"] . "." . $matches["payload"],
            $this->secretKey, 
            true
        );

        $signatureFromToken = $this->base64urlDecode($matches["signature"]);

        // Compare signatures using hash_equals for timing attack protection
        if (hash_equals($signature, $signatureFromToken) === false) {
            throw new InvalidSignatureException("signature does not match");
        }

        // Decode and extract payload
        $payload = json_decode($this->base64urlDecode($matches["payload"]), true)[0];

        // Check if token has expired
        if ($payload["exp"] < time()) {
            throw new TokenExpiredException();
        }

        return $payload;
    }

    /**
     * Encode string to base64url format
     * @param string $text Text to encode
     * @return string Base64url encoded string
     */
    private function base64urlEncode(string $text): string
    {
        // Convert to base64 and make URL-safe
        // Replace characters that cause issues in URLs:
        // + becomes - (plus might be interpreted as space in URLs)
        // / becomes _ (slash might be treated as path separator)
        // = becomes empty (equals used in query parameters)
        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }

    /**
     * Decode base64url string back to original format
     * @param string $text Base64url encoded text
     * @return string Decoded string
     */
    private function base64urlDecode(string $text): string
    {
        // Reverse the base64url encoding
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text
        ));
    }
}