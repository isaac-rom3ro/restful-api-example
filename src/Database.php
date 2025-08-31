<?php
/**
 * Database Connection Class
 * Manages the PDO connection to the MySQL database
 * Implements singleton pattern to reuse the same connection
 * Configures PDO for secure and efficient database operations
 */

declare(strict_types = 1);

class Database {
    // PDO connection instance - nullable to allow lazy initialization
    private ?PDO $conn = null;

    /**
     * Constructor - Stores database connection parameters
     * @param string $host Database host (e.g., localhost)
     * @param string $dbname Database name
     * @param string $user Database username
     * @param string $password Database password
     */
    public function __construct(private string $host, private string $dbname, private string $user, private string $password) {
    }

    /**
     * Get PDO connection - Creates connection if not exists
     * @return PDO The database connection
     */
    public function getConnection(): PDO {
        // Create DSN (Data Source Name) for MySQL connection
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";

        // Lazy initialization - only create connection when needed
        if($this->conn === null) {
            // Create PDO instance with secure configuration
            $this->conn = new PDO(
                dsn: $dsn, 
                username: $this->user, 
                password: $this->password, 
                options: [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Throw exceptions on errors
                    PDO::ATTR_EMULATE_PREPARES => false,             // Use native prepared statements
                    PDO::ATTR_STRINGIFY_FETCHES => false             // Return native data types
                ]
            );
        }

        return $this->conn;
    }
}