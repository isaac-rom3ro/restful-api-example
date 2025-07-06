<?php

declare(strict_types = 1);

class TaskController {
    public function __construct(private TaskGateway $gateway, private int $user_id) {}

    public function processRequest(string $method, ?string $id): void {
        //?string -> means that the variable can receive string or a null value
        if($id == null) {
            if($method == "GET") {
                // Show the data in json format
                echo json_encode($this->gateway->getAllFromUser(user_id: $this->user_id));
            } else if($method == "POST") {
                // php://input -> get contents with assigned value like name=Isaac from the request body 
                // json_decode(Transform it into an object but as we passed true inside file_get_contents the return is about associative array)
                // (array) -> we are using type cast because if the return was null it converts into an empty array[]
                $data = (array)(json_decode(file_get_contents("php://input", true)));

                $errors = $this->getValidationErrors(data: $data);

                // If there is error
                if(! empty($errors)) {
                    $this->respondUnprocessableEntity(errors: $errors);
                    return;
                }

                $id = $this->gateway->createFromUser(data: $data, user_id: $this->user_id);
                $this->respondCreated($id);
            } else {
                $this->responseMethodNotAllowed("GET, POST");
            }
        } else {
            // We are checking if the task + id is valid
            $task = $this->gateway->getFromUser(id: $id, user_id: $this->user_id);

            // If is not
            if($task === false) {
                // Response
                $this->respondNotFound(id: $id);
                // Return it because we dont need this method anymore
                return;
            }
            
            switch($method) {
                case "GET":
                    // Show data in json format
                    echo json_encode($task);
                    break;
                case "PATCH":
                    // This method will handle an update
                    // give us /resource and /id
                    $data = (array)(json_decode(file_get_contents("php://input", true)));
                    
                    // Check for invalids
                    $errors = $this->getValidationErrors(data: $data, is_new: false);

                    if(! empty($errors)) {
                        $this->respondUnprocessableEntity(errors: $errors);
                        return;
                    }

                    // Call the method
                    $row = $this->gateway->updateFromUser(id: $id, data: $data, user_id: $this->user_id);
                    $this->respondUpdated(row: $row);
                    break;
                case "DELETE":
                    $row = $this->gateway->deleteFromUser(id: $id, user_id: $this->user_id);
                    $this->respondDelete(row: $row);
                    break;
                default:
                    $this->responseMethodNotAllowed("GET, PATCH, DELETE");
                    break;
            }
        }
    }

    // Respond and Validations

    // To handle the 405 code we need a function
    private function responseMethodNotAllowed(string $allowed_methods): void {
        http_response_code(405);
        header("Allow: {$allowed_methods}"); // Pass the allowed methods here 
    }

    private function respondNotFound(string $id): void {
        // Set the http response to 404 not found
        http_response_code(404);
        // echo a message
        echo json_encode(["message" => "The task with the id {$id} was not found"]);
    }

    // Response for a successful task created
    private function respondCreated(string $id): void {
        http_response_code(201); // 201 -> Create 
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    // Get errors associated with syntax of the request 
    private function getValidationErrors(array $data, bool $is_new = true): array {
        $errors = [];

        if($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }

        if(!empty($data["priority"])) {
            // Check if the data is an integer
            if(filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "priority must be an integer";
            }
        }

        return $errors;
    }

    // Handle the response code 422 Unprocessable Content
    private function respondUnprocessableEntity(array $errors): void {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    // Respond for the respond updated
    private function respondUpdated(int $row): void {
        http_response_code(200);
        echo json_encode(["message" => "Task updated", "rows" => $row]);
    }

    private function respondDelete(int $row) {
        http_response_code(200);
        echo json_encode(["message" => "Task deleted", "row" => $row]);
    }
}