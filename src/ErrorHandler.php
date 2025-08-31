<?php
/**
 * Error Handler Class
 * Provides centralized error and exception handling for the application
 * Converts all errors and exceptions to JSON responses
 * Ensures consistent error reporting format across the API
 */

declare(strict_types = 1);

class ErrorHandler {
    /**
     * Handle uncaught exceptions
     * Converts exceptions to JSON error responses
     * @param Throwable $exception The exception that was thrown
     */
    public static function handleException(Throwable $exception): void {
        http_response_code(500); // Internal Server Error
        header("Content-type: application/json; charset=UTF-8"); // Set JSON content type
        
        // Return detailed error information in JSON format
        echo json_encode([
            "code" => $exception->getCode(),        // Error code
            "message" => $exception->getMessage(),  // Error message
            "file" => $exception->getFile(),        // File where error occurred
            "line" => $exception->getLine()         // Line number where error occurred
        ]);
    }

    /**
     * Handle PHP errors and convert them to exceptions
     * This allows all errors to be handled consistently
     * @param int $errorno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number where error occurred
     */
    public static function handleError(int $errorno, string $errstr, string $errfile, int $errline) {
        // Convert PHP error to ErrorException
        // This allows the error to be caught by the exception handler
        throw new ErrorException(
            message: $errstr, 
            code: 0, 
            severity: $errorno, 
            filename: $errfile, 
            line: $errline
        ); 
    }
}