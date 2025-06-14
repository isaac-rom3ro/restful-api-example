<?php

declare(strict_types = 1);

class TaskGateway {
    // We are gonna receive an object of PDO
    private PDO $conn;

    // Inside this constructor the receive parameter are a database class
    public function __construct(Database $database) {
        // Start a connection only here and assign it to our attribute $conn
        $this->conn = $database->getConnection();
    }

    // Simple method to get all those tasks
    public function getAll(): array {
        // SQL query
        $sql = "SELECT * FROM task ORDER BY name";

        // $this->conn->query(); -> returns a statement which is the result of the query
        // query() method already execures the query only leaking the fetch()
        $stmt = $this->conn->query($sql);

        // // With this method, we return an ASSOCIATIVE ARRAY 
        // return $stmt->fetchAll(PDO::FETCH_ASSOC);

        // In order to transform 0 and 1 to boolean we need to convert each row 
        $data = [];

        // Using fetch instead of fetchAll
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Changing the current row to boolean
            $row["is_completed"] = (bool) $row["is_completed"];
            // Assigning the whole row inside the data
            array_push($data, $row);
        }

        return $data;
    }

    // This function returns array or false(because fetch if there is no row so is returned false) 
    public function get(string $id): array | false {
        // Query for get the selected task using id
        $sql = "SELECT * FROM task WHERE id = :id";

        // Preparing the statement
        $stmt = $this->conn->prepare($sql);
        // Adding the parameter
        $stmt->bindParam(":id", $id);
        // Execute the query
        $stmt->execute();

        // Get result
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function create(array $data) : string {
        // Prepare
        $sql = "INSERT INTO task (name, priority, is_completed) VALUES (:name, :priority, :is_completed)";
        $stmt = $this->conn->prepare(query: $sql);

        $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
        //Check priority is empty
        if(empty($data["priority"])) {
            $stmt->bindValue(":priority", null);
        } else {
            $stmt->bindValue(":priority", $data["priority"], PDO::PARAM_INT);
        }

        // If there is no data inside the index, we are assigning the false value
        $stmt->bindValue("is_completed", $data["is_completed"] ?? false,  PDO::PARAM_BOOL);

        $stmt->execute();

        // Return the last id
        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int {
        $fields = []; // Keep the existents updates 

        // Each if check whether the column is specified
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
        
        // If there is no field to update
        if(empty($fields)) {
            return 0;
        } else {
            // Return a statement already done by assign a function
            $sets = array_map(function($value) {
                return "$value = :$value";
            }, array_keys($fields));
            
            $sql = "UPDATE task". " SET ". implode(", ", $sets). " WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            // Bind the values
            foreach($fields as $column => $value) {
                $stmt->bindValue(":$column", $value[0], $value[1]);
            }

            $stmt->execute();

            // Return the rows updated
            return $stmt->rowCount();
        }
    }

    public function delete(string $id): int {
        $sql = "DELETE FROM task WHERE id = :id";
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}