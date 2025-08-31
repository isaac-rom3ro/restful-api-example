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

$userId = $payload["sub"];

$database = new Database(host:$_ENV["DB_HOST"], dbname:$_ENV["DB_NAME"], user:$_ENV["DB_USER"], password:$_ENV["DB_PASSWORD"]);

$refreshTokenGateway = new RefreshTokenGateway(database: $database, key: $_ENV["SECRET_KEY"]);

$refresh_token = $refreshTokenGateway->getByToken($data["token"]);

if ($refresh_token === false) {
    http_response_code(400);

    echo json_encode(["message" => "invalid token (not in whitelist)"]);
    exit;
}


$userGateway = new UserGateway(database: $database);

$user = $userGateway->getByID(id: $userId);

if ($user === false) {
    http_response_code(401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;
}

require_once __DIR__ . "/tokens.php";

$refreshTokenGateway->delete($data["token"]);

$refreshTokenGateway->create(token: $refresh_token, expiry: $refresh_token_expiry);