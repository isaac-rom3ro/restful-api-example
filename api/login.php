<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Check if the request method was POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit;
}

// data associated to inputs like name="" password"" in the request
$data = (array)(json_decode(file_get_contents("php://input", true)));

// Check for the credential exists
if(
    ! array_key_exists(key: "username", array: $data) ||
    ! array_key_exists(key: "password", array: $data)
) {
    http_response_code(response_code: 400);
    echo json_encode(["message" => "missing login credentials"]);
    exit;
}

// Init a connection in order to use the user gateway
$database = new Database(host: $_ENV["DB_HOST"],
                        dbname: $_ENV["DB_NAME"], 
                        user: $_ENV["DB_USER"], 
                        password: $_ENV["DB_PASSWORD"]);

$user_gateway = new UserGateway(database: $database);

// get & from user by its name
$user = $user_gateway->getByUserName(username: $data["username"]);

// Both verifications are throwing the same respond in order of security
// check if the user exists by the name
if($user === false) {
    http_response_code(response_code: 401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}
// check if the passwords match
if(! password_verify(password: $data["password"], hash: $user["password_hash"])) {
    http_response_code(response_code: 401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

// In order to use JWT
// First we have to get the informations 
// key -> value
// using sub instead of id is a sub
// Claim Standard
$payload = [
    "sub" => $user["id"],
    "username" => $user["name"]
];

// In order to save credentials with security layer 
// We used to have base64_encode in a json associative array containing the informations needed
// Since it's not secure, stick with JWT 
// $access_token = base64_encode(json_encode($payload));

$codec = new JWTCodec();
$access_token = $codec->encode($payload);

echo json_encode([
    "access_token" => $access_token 
]);