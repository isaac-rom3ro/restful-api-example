<?php
require_once "../vendor/autoload.php";

ini_set("display_errors", "On"); // Show errors

// Set content type of header respond to json and the charset  
header("Content-Type: application/json;charset= UTF-8");

// Set the error handler to our function
set_error_handler("ErrorHandler::handleError");

// This exception will handle the errors associated with code not the response i guess
set_exception_handler("ErrorHandler::handleException"); 

# Loading the package that loads the env file variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();