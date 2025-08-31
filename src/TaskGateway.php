<?php
/**
 * Task Gateway Class
 * Data Access Layer for task-related database operations
 * Handles CRUD operations for tasks with user isolation
 * Uses prepared statements for security and data type handling
 */

declare(strict_types = 1);

class TaskGateway {
    // PDO connection for database operations
    private PDO $conn;

    /**
     * Constructor - Initializes database connection
     * @param Database $database Database connection instance
     */
    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    /**
     * Get all tasks for a specific user
     * @param int $user_id The user ID to get tasks for
     * @return array Array of task data with boolean conversion
     */
    public function getAllFromUser(int $user_id): array {
        // SQL query to get all tasks for the user
        $sql = "SELECT * FROM task WHERE user_id =:user_id";

        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Process results and convert is_completed to boolean
        $data = [];

        // Fetch rows one by one to process them
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Convert is_completed from 0/1 to boolean
            $row["is_completed"] = (bool) $row["is_completed"];
            // Add processed row to result array
            array_push($data, $row);
        }

        return $data;
    }

    /**
     * Get a specific task by ID for a user
     * @param string $id Task ID
     * @param int $user_id User ID for security
     * @return array|false Task data or false if not found
     */
    public function getFromUser(string $id, int $user_id): array | false {
        // Query to get specific task with user validation
        $sql = "SELECT * FROM task WHERE id = :id AND user_id =:user_id";

        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Get result
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Create a new task for a user
     * @param array $data Task data (name, priority, is_completed)
     * @param int $user_id User ID to associate task with
     * @return string The ID of the newly created task
     */
    public function createFromUser(array $data, int $user_id) : string {
        // SQL to insert new task
        $sql = "INSERT INTO task (name, priority, is_completed, user_id) VALUES (:name, :priority, :is_completed, :user_id)";
        $stmt = $this->conn->prepare(query: $sql);

        // Bind task name
        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        
        // Handle priority (can be null)
        if(empty($data["priority"])) {
            $stmt->bindValue(":priority", null);
        } else {
            $stmt->bindValue(":priority", $data["priority"], PDO::PARAM_INT);
        }

        // Bind completion status (default to false if not provided)
        $stmt->bindValue(":is_completed", $data["is_completed"] ?? false, PDO::PARAM_BOOL);
        
        // Bind user ID for security
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        // Return the ID of the newly created task
        return $this->conn->lastInsertId();
    }

    /**
     * Update an existing task for a user
     * @param string $id Task ID to update
     * @param array $data Task data to update
     * @param int $user_id User ID for security
     * @return int Number of rows affected
     */
    public function updateFromUser(string $id, array $data, int $user_id): int {
        $fields = []; // Array to store fields to update

        // Check which fields are provided and prepare them for update
        if(array_key_exists(key: "name", array: $data)) {
            $fields["name"] = [
                $data["name"],
                PDO::PARAM_STR
            ];
        }

        if(array_key_exists(key: "priority", array: $data)) {
            $fields["priority"] = [
                $data["priority"],
                $data["priority"] === null ? PDO::PARAM_NULL : PDO::PARAM_INT
            ];
        }

        if(array_key_exists(key: "is_completed", array: $data)) {
            $fields["is_completed"] = [
                $data["is_completed"],
                PDO::PARAM_BOOL
            ];
        }
        
        // If no fields to update, return 0
        if(empty($fields)) {
            return 0;
        } else {
            // Build dynamic SQL update statement
            $sets = array_map(function($value) {
                return "$value = :$value";
            }, array_keys($fields));
            
            $sql = "UPDATE task". " SET ". implode(", ", $sets). " WHERE id =:id AND user_id =:user_id";
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            
            // Bind all the update fields
            foreach($fields as $column => $value) {
                $stmt->bindValue(":$column", $value[0], $value[1]);
            }
            
            $stmt->execute();
            
            // Return number of rows affected
            return $stmt->rowCount();
        }
    }

    /**
     * Delete a task for a user
     * @param string $id Task ID to delete
     * @param int $user_id User ID for security
     * @return int Number of rows affected
     */
    public function deleteFromUser(string $id, $user_id): int {
        $sql = "DELETE FROM task WHERE id = :id AND user_id =:user_id";
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}