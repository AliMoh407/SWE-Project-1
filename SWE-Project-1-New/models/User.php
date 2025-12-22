<?php

class UserModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get all users.
     *
     * @return array
     */
    public function getAll(): array
    {
        $users = [];
        $sql = "SELECT id, username, password, role, name, email FROM users";
        if ($result = $this->conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $result->free();
        }
        return $users;
    }

    /**
     * Find a user by username and password.
     * Uses password_verify for secure password checking with hashed passwords.
     */
    public function findByCredentials(string $username, string $password): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, role, name, email 
             FROM users 
             WHERE username = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;

        $stmt->close();

        // Verify password using password_verify for hashed passwords
        // Also support plain text passwords for backward compatibility
        if ($user) {
            // Get stored password (don't trim yet, as hash might have specific format)
            $storedPassword = $user['password'];
            $inputPassword = trim($password);
            
            // Check if password is hashed (starts with $2y$, $2a$, $2b$, or $2x$)
            if (preg_match('/^\$2[axyb]\$/', $storedPassword)) {
                // Password is hashed, use password_verify
                // password_verify handles trimming internally, but we should pass raw input
                if (!password_verify($inputPassword, $storedPassword)) {
                    return null;
                }
            } else {
                // Password is plain text, compare directly (case-sensitive, trimmed)
                $storedPassword = trim($storedPassword);
                if ($storedPassword !== $inputPassword) {
                    return null;
                }
            }
            // Remove password from returned array for security
            unset($user['password']);
        }

        return $user;
    }

    /**
     * Find a user by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, role, name, email 
             FROM users 
             WHERE id = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;

        $stmt->close();

        return $user;
    }
}


