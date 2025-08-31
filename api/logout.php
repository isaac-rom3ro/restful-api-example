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
    ! array_key_exists(key: "token", array: $data)
) {
    http_response_code(response_code: 400);
    echo json_encode(["message" => "missing token"]);
    exit;
}

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

try {
    $payload = $codec->decode($data["token"]);
} catch (Exception) {
    http_response_code(400);

    echo json_encode(["message" => "invalid token"]);
    exit;
} 

$database = new Database(host:$_ENV["DB_HOST"], dbname:$_ENV["DB_NAME"], user:$_ENV["DB_USER"], password:$_ENV["DB_PASSWORD"]);

$refreshTokenGateway = new RefreshTokenGateway(database: $database, key: $_ENV["SECRET_KEY"]);

$refreshTokenGateway->delete($data["token"]);