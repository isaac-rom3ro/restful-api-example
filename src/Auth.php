<?php
declare(strict_types=1);

class Auth {

    private int $user_id;

    public function __construct(private UserGateway $user_gateway) {

    }

    public function authenticateAPIKey(): bool {
        // Check if the API key is not absent in the header 
        if(empty($_SERVER["HTTP_X_API_KEY"])) {
            http_response_code(400);
            echo json_encode(["message" => "missing API key"]);
            return false;
        }

        $api_key = $_SERVER["HTTP_X_API_KEY"];

        // Check if the API key is valid

        $user = $this->user_gateway->getByAPIKey(api_key: $api_key); 

        if($user === false) {
            http_response_code(response_code: 401);
            echo json_encode(["message" => "Invalid API key"]);  
            return false;
        }

        $this->user_id = $user["id"];

        return true;
    }

    public function getUserId(): int  {
        return $this->user_id;
    }

    public function authenticateAccessToken(): bool {
        if( ! preg_match(pattern: "/^Bearer\s+(.*)$/", subject: $_SERVER["HTTP_AUTHORIZATION"], matches: $matches)) {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        $plain_text = base64_decode($matches[1], true);
        
        if($plain_text === false) {
            http_response_code(400);
            echo json_encode(["message" => "invalid authorization header"]);
            return false;
        }

        $data = json_decode($plain_text, true);
        if( $data === null ) {
            http_response_code(400);
            echo json_encode(["message" => "invalid JSON"]);
            return false;
        }

        $this->user_id = $data["id"];
        
        return true;
    }   

}
