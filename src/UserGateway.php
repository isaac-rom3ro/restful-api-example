<?php
/**
 * User Gateway Class
 * Data Access Layer for user-related database operations
 * Handles user authentication, retrieval, and management
 * Uses prepared statements for security against SQL injection
 */

declare(strict_types=1);

class UserGateway {
    private PDO $conn;

    /**
     * Constructor - Initializes database connection
     * @param Database $database Database connection instance
     */
    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    /**
     * Get user by API key (for API key authentication)
     * @param string $api_key The API key to search for
     * @return array|false User data array or false if not found
     */
    public function getByAPIKey(string $api_key): array | false {
        $sql = "SELECT * FROM user WHERE api_key = :api_key";

        $stmt = $this->conn->prepare(query: $sql);              
        $stmt->bindValue(param:":api_key" , value: $api_key, type: PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get user by username (for login authentication)
     * @param string $username The username to search for
     * @return array|false User data array or false if not found
     */
    public function getByUserName(string $username): array | false {
        $sql = "SELECT * FROM user WHERE username = :username";
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->bindValue(param: ":username", value: $username, type: PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(mode: PDO::FETCH_ASSOC);
    }

    /**
     * Get user by ID (for token validation)
     * @param int $id The user ID to search for
     * @return array|false User data array or false if not found
     */
    public function getByID(int $id): array | false 
    {
        $sql = "SELECT * FROM user WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}