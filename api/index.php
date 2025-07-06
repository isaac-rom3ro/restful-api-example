<?php
require_once "./bootstrap.php";

# $_SERVER["REQUEST_URI"];  Get the full path including resource, id, indexes
# parse_url(, PHP_URL_PATH) transforms it into string 
$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

$parts = explode("/", $url); // Create an array with eeach / being a part 

# Resource -> Task
# Id -> 123
$resource = $parts[3];
$id = $parts[4] ?? null; // If the argument was not included, will be assign null value

// Show each one
// echo $resource . " ". $id, " ", $requestMethod;
// echo $resource . $id;
// echo parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if($resource != "tasks") { // We are checking if the resource is valid
    // header("HTTP/1.1 404 Not Found");
    // header("{$_SERVER["SERVER_PROTOCOL"]} 404 Not Found");
    http_response_code(404); // If it's not, we need to return a response
    exit; // and kill the process
}

# To ensure security we need to use env variables
# After create .env just use (variableName="value")
$database = new Database(host:$_ENV["DB_HOST"], dbname:$_ENV["DB_NAME"], user:$_ENV["DB_USER"], password:$_ENV["DB_PASSWORD"]);

$user_gateway = new UserGateway(database: $database);

$auth = new Auth(user_gateway: $user_gateway);
if(! $auth->authenticateAccessToken()) {
    exit;
}

// $headers = apache_request_headers();
// print_r($headers);

// Getting the user id from the API key
$user_id = $auth->getUserId();
// We are using a gateway to establish a safe connection between the controller and the database
$taskGateway = new TaskGateway(database: $database);

// The controller uses as priority the database 
$controller = new TaskController(gateway: $taskGateway, user_id: $user_id);
// Process the request
$controller->processRequest(method: $requestMethod, id: $id);