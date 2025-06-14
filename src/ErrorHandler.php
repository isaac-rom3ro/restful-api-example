<?php

declare(strict_types = 1);

// Handling errors
class ErrorHandler {
    // This function will be used to throw whatever exception that pass through responses
    public static function handleException(Throwable $exception): void {
        http_response_code(500); // Set the code response for 500
        header("Content-type: application/json; charset=UTF-8"); // Set the header type
        echo json_encode([ // Show messages associated with the exception encountered
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }

    // Handle error and return as json
    public static function handleError(int $errorno, string $errstr, string $errfile, int $errline) {
        throw new ErrorException(message: $errstr, code: 0, severity: $errorno, filename: $errfile, line: $errline); 
    }
}