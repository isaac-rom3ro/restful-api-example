<?php
/**
 * Authentication Class
 * Handles user authentication using JWT tokens and API keys
 * Validates tokens and extracts user information
 * Provides secure authentication for API endpoints
 */

declare(strict_types=1);

class Auth {

    private int $user_id;

    /**
     * Constructor - Initializes authentication dependencies
     * @param UserGateway $user_gateway For user data access
     * @param JWTCodec $codec For JWT token handling
     */
    public function __construct(
        private UserGateway $user_gateway,
        private JWTCodec $codec    
    ) {
    }

    /**
     * Authenticate user using API key (alternative to JWT)
     * @return bool True if authentication successful, false otherwise
     */
    public function authenticateAPIKey(): bool {
        // Check if API key is present in request headers
        if(empty($_SERVER["HTTP_X_API_KEY"])) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "missing API key"]);
            return false;
        }

        $api_key = $_SERVER["HTTP_X_API_KEY"];

        // Validate API key against database
        $user = $this->user_gateway->getByAPIKey(api_key: $api_key); 

        if($user === false) {
            http_response_code(response_code: 401); // Unauthorized
            echo json_encode(["message" => "Invalid API key"]);  
            return false;
        }

        // Store user ID for later use
        $this->user_id = $user["id"];

        return true;
    }

    /**
     * Get the authenticated user's ID
     * @return int User ID
     */
    public function getUserId(): int  {
        return $this->user_id;
    }

    /**
     * Authenticate user using JWT access token
     * @return bool True if authentication successful, false otherwise
     */
    public function authenticateAccessToken(): bool {
        // Extract Bearer token from Authorization header
        // Format should be: "Bearer <token>"
        if( ! preg_match(
            pattern: "/^Bearer\s+(.*)$/", 
            subject: $_SERVER["HTTP_AUTHORIZATION"], 
            matches: $matches
        )) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        try {
            // Decode and validate the JWT token
            $data = $this->codec->decode($matches[1]);
        } catch (InvalidSignatureException) {
            // Token signature is invalid (tampered or wrong secret key)
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "invalid signature"]);
            return false;
        } catch (TokenExpiredException) {
            // Token has expired
            http_response_code(401); // Unauthorized
            echo json_encode(["message" => "token has expired"]);
            return false;
        } catch (Exception $e) {
            // Other token-related errors (malformed, etc.)
            http_response_code(400); // Bad Request
            echo json_encode(["message" => $e->getMessage()]);
            return false;
        }

        // Store user ID from token payload
        $this->user_id = $data["sub"];
        
        return true;
    }   
}
