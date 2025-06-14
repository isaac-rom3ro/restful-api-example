<?php

declare(strict_types = 1);

class Database {
    public function __construct(private string $host, private string $dbname, private string $user, private string $password) {
    }

    public function getConnection(): PDO {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";

        // For default the PDO returns data as string
        return new PDO(dsn: $dsn, username:$this->user, password:$this->password, options:[
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Allows exceptions
            PDO::ATTR_EMULATE_PREPARES => false, // Let the database prepares the statements
            PDO::ATTR_STRINGIFY_FETCHES => false // Prevents PDO to turn everything in string
        ]);
    }
}