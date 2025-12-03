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
     * NOTE: This assumes passwords are stored in plain text.
     *       For production, use password_hash/password_verify instead.
     */
    public function findByCredentials(string $username, string $password): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, role, name, email 
             FROM users 
             WHERE username = ? AND password = ? 
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;

        $stmt->close();

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


