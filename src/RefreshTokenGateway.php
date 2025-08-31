<?php
/**
 * Refresh Token Gateway Class
 * Manages refresh tokens in the database for secure token rotation
 * Implements token whitelist to prevent use of revoked tokens
 * Uses HMAC hashing for secure token storage
 */

class RefreshTokenGateway
{
    private PDO $conn;
    private string $key;

    /**
     * Constructor - Initializes database connection and secret key
     * @param Database $database Database connection instance
     * @param string $key Secret key for HMAC hashing
     */
    public function __construct(Database $database, string $key)
    {
        $this->conn = $database->getConnection();
        $this->key = $key;
    }

    /**
     * Store a refresh token in the database
     * @param string $token The refresh token to store
     * @param int $expiry Expiration timestamp
     * @return bool True if successful, false otherwise
     */
    public function create(string $token, int $expiry): bool 
    {
        // Hash the token for secure storage
        // This prevents token exposure if database is compromised
        $hash = hash_hmac("sha256", $token, $this->key);

        // SQL to insert the token hash and expiration
        $sql = "INSERT INTO refresh_token (token_hash, expires_at) VALUES (:token_hash, :expires_at)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->bindValue(":expires_at", $expiry, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove a refresh token from the database (logout/revocation)
     * @param string $token The refresh token to delete
     * @return int Number of rows affected
     */
    public function delete(string $token): int
    {
        // Hash the token to match stored hash
        $hash = hash_hmac("sha256", $token, $this->key);

        // SQL to delete the token hash
        $sql = "DELETE FROM refresh_token WHERE token_hash = :token_hash";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Check if a refresh token exists in the whitelist
     * @param string $token The refresh token to check
     * @return array|false Token data if found, false otherwise
     */
    public function getByToken(string $token)
    {
        // Hash the token to match stored hash
        $hash = hash_hmac("sha256", $token, $this->key);

        // SQL to find the token hash
        $sql = "SELECT * FROM refresh_token WHERE token_hash = :token_hash";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Remove expired refresh tokens from the database
     * This should be run periodically via cron job
     * @return int Number of expired tokens removed
     */
    public function deletExpired()
    {
        // SQL to delete tokens that have expired
        $sql = "DELETE FROM refresh_token WHERE expires_at < UNIX_TIMESTAMP()";

        $stmt = $this->conn->query($sql);

        return $stmt->rowCount();
    }
}