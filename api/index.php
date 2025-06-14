<?php

ini_set("display_errors", "On"); // Show errors
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

// requiring classes without autoloader yet
require_once "../vendor/autoload.php";
header("Content-Type: application/json;charset= UTF-8");
// Set the error handler to our function
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException"); // This exception will handle the errors associated with code not the response i guess
# Here im using a package that loads the env file variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

# To ensure security we need to use env variables
# After create .env just use (variableName="value")
$database = new Database(host:$_ENV["DB_HOST"], dbname:$_ENV["DB_NAME"], user:$_ENV["DB_USER"], password:$_ENV["DB_PASSWORD"]);

// We are using a gateway to establish a safe connection between the controller and the database
$taskGateway = new TaskGateway(database: $database);

// The controller uses as priority the database 
$controller = new TaskController(gateway: $taskGateway);
// Process the request
$controller->processRequest(method: $requestMethod, id: $id);