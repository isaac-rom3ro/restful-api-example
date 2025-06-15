<?php
    require __DIR__ . "/vendor/autoload.php";

    // Checking if the form was submitted by POST method
    if($_SERVER["REQUEST_METHOD"] === "POST") {
        // In order to use .env 
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Connection to database
        $database = new Database(host: $_ENV["DB_HOST"],
                                 dbname: $_ENV["DB_NAME"],
                                 user: $_ENV["DB_USER"], 
                                 password: $_ENV["DB_PASSWORD"]);
        
        $conn = $database->getConnection();
        // SQL with placeholders  
        $sql = "INSERT INTO user (name, username, password_hash, api_key) VALUES (:name, :username, :password_hash, :api_key)";                                 

        // Hash the password
        $password_hash = password_hash(password: $_POST["password"], algo: PASSWORD_DEFAULT);

        // Create a random hexadecimal api_key
        $api_key = bin2hex(random_bytes(length: 16));

        // binding values
        $stmt = $conn->prepare(query: $sql);
        $stmt->bindValue(param:":name", value:$_POST["name"], type: PDO::PARAM_STR);
        $stmt->bindValue(param:":username", value:$_POST["username"], type: PDO::PARAM_STR);
        $stmt->bindValue(param:":password_hash", value:$password_hash, type: PDO::PARAM_STR);
        $stmt->bindValue(param:":api_key", value:$api_key, type: PDO::PARAM_STR);

        // Execute
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            echo "Registered";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"
>
</head>
<body>
    <main>
        <form action="" method="POST">
            <label for="name">Name</label>
            <input type="text" name="name" id="">

            <label for="name">Username</label>
            <input type="text" name="username" id="">

            <label for="name">Password</label>
            <input type="password" name="password" id="">
            
            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>