<?php
/**
 * Task Controller Class
 * Handles HTTP requests for task operations
 * Routes requests based on HTTP method and resource ID
 * Validates input data and provides appropriate responses
 */

declare(strict_types = 1);

class TaskController {
    /**
     * Constructor - Initializes controller with dependencies
     * @param TaskGateway $gateway For task data operations
     * @param int $user_id Current authenticated user ID
     */
    public function __construct(private TaskGateway $gateway, private int $user_id) {}

    /**
     * Main request processor - Routes requests based on method and ID
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string|null $id Task ID (null for collection operations)
     */
    public function processRequest(string $method, ?string $id): void {
        // Handle collection operations (no specific ID)
        if($id == null) {
            if($method == "GET") {
                // Get all tasks for the user
                echo json_encode($this->gateway->getAllFromUser(user_id: $this->user_id));
            } else if($method == "POST") {
                // Create a new task
                // Parse JSON request body
                $data = (array)(json_decode(file_get_contents("php://input", true)));

                // Validate the input data
                $errors = $this->getValidationErrors(data: $data);

                // If validation fails, return error response
                if(! empty($errors)) {
                    $this->respondUnprocessableEntity(errors: $errors);
                    return;
                }

                // Create the task and return success response
                $id = $this->gateway->createFromUser(data: $data, user_id: $this->user_id);
                $this->respondCreated($id);
            } else {
                // Method not allowed for collection
                $this->responseMethodNotAllowed("GET, POST");
            }
        } else {
            // Handle individual resource operations (with ID)
            // First check if the task exists and belongs to the user
            $task = $this->gateway->getFromUser(id: $id, user_id: $this->user_id);

            if($task === false) {
                // Task not found or doesn't belong to user
                $this->respondNotFound(id: $id);
                return;
            }
            
            // Route to appropriate method based on HTTP verb
            switch($method) {
                case "GET":
                    // Return the specific task
                    echo json_encode($task);
                    break;
                case "PATCH":
                    // Update the task
                    // Parse JSON request body
                    $data = (array)(json_decode(file_get_contents("php://input", true)));
                    
                    // Validate the update data
                    $errors = $this->getValidationErrors(data: $data, is_new: false);

                    if(! empty($errors)) {
                        $this->respondUnprocessableEntity(errors: $errors);
                        return;
                    }

                    // Update the task and return response
                    $row = $this->gateway->updateFromUser(id: $id, data: $data, user_id: $this->user_id);
                    $this->respondUpdated(row: $row);
                    break;
                case "DELETE":
                    // Delete the task
                    $row = $this->gateway->deleteFromUser(id: $id, user_id: $this->user_id);
                    $this->respondDelete(row: $row);
                    break;
                default:
                    // Method not allowed for individual resource
                    $this->responseMethodNotAllowed("GET, PATCH, DELETE");
                    break;
            }
        }
    }

    // Response Methods

    /**
     * Return 405 Method Not Allowed response
     * @param string $allowed_methods Comma-separated list of allowed methods
     */
    private function responseMethodNotAllowed(string $allowed_methods): void {
        http_response_code(405); // Method Not Allowed
        header("Allow: {$allowed_methods}"); // Tell client what methods are allowed
    }

    /**
     * Return 404 Not Found response
     * @param string $id The ID that was not found
     */
    private function respondNotFound(string $id): void {
        http_response_code(404); // Not Found
        echo json_encode(["message" => "The task with the id {$id} was not found"]);
    }

    /**
     * Return 201 Created response
     * @param string $id The ID of the newly created resource
     */
    private function respondCreated(string $id): void {
        http_response_code(201); // Created
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    /**
     * Validate input data for task operations
     * @param array $data The data to validate
     * @param bool $is_new Whether this is for creating a new task
     * @return array Array of validation errors
     */
    private function getValidationErrors(array $data, bool $is_new = true): array {
        $errors = [];

        // Name is required for new tasks
        if($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }

        // If priority is provided, it must be an integer
        if(!empty($data["priority"])) {
            if(filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "priority must be an integer";
            }
        }

        return $errors;
    }

    /**
     * Return 422 Unprocessable Entity response
     * @param array $errors Array of validation errors
     */
    private function respondUnprocessableEntity(array $errors): void {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(["errors" => $errors]);
    }

    /**
     * Return 200 OK response for successful update
     * @param int $row Number of rows affected
     */
    private function respondUpdated(int $row): void {
        http_response_code(200); // OK
        echo json_encode(["message" => "Task updated", "rows" => $row]);
    }

    /**
     * Return 200 OK response for successful deletion
     * @param int $row Number of rows affected
     */
    private function respondDelete(int $row) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Task deleted", "row" => $row]);
    }
}