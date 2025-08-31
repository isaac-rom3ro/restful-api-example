<?php
declare(strict_types=1);

class UserGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function getByAPIKey(string $api_key): array | false {
        $sql = "SELECT * FROM user WHERE api_key = :api_key";

        $stmt = $this->conn->prepare(query: $sql);              
        $stmt->bindValue(param:":api_key" , value: $api_key, type: PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getByUserName(string $username): array | false {
        $sql = "SELECT * FROM user WHERE username = :username";
        $stmt = $this->conn->prepare(query: $sql);
        $stmt->bindValue(param: ":username", value: $username, type: PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(mode: PDO::FETCH_ASSOC);
    }

    public function getByID(int $id): array | false 
    {
        $sql = "SELECT * FROM user WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}