<?php
/**
 * User Registration Page
 * Handles user registration with secure password hashing
 * Creates new users with API keys for authentication
 * Provides both form processing and HTML interface
 */

require __DIR__ . "/vendor/autoload.php";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] === "POST") {
    // Load environment variables for database connection
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Initialize database connection using environment variables
    $database = new Database(
        host: $_ENV["DB_HOST"],
        dbname: $_ENV["DB_NAME"],
        user: $_ENV["DB_USER"], 
        password: $_ENV["DB_PASSWORD"]
    );
    
    $conn = $database->getConnection();
    
    // SQL query to insert new user with prepared statement
    $sql = "INSERT INTO user (name, username, password_hash, api_key) VALUES (:name, :username, :password_hash, :api_key)";                                 

    // Hash the password securely using PHP's built-in password_hash function
    // PASSWORD_DEFAULT uses the best available algorithm (currently bcrypt)
    $password_hash = password_hash(password: $_POST["password"], algo: PASSWORD_DEFAULT);

    // Generate a random API key for the user
    // bin2hex converts binary to hexadecimal for safe storage
    $api_key = bin2hex(random_bytes(length: 16));

    // Prepare the statement and bind parameters
    $stmt = $conn->prepare(query: $sql);
    $stmt->bindValue(param:":name", value:$_POST["name"], type: PDO::PARAM_STR);
    $stmt->bindValue(param:":username", value:$_POST["username"], type: PDO::PARAM_STR);
    $stmt->bindValue(param:":password_hash", value:$password_hash, type: PDO::PARAM_STR);
    $stmt->bindValue(param:":api_key", value:$api_key, type: PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Check if registration was successful
    if($stmt->rowCount() > 0) {
        echo "Registered Successfully! Your API key is: $api_key";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Include Pico CSS for modern styling -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"
    >
</head>
<body>
    <main>
        <!-- Registration form -->
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