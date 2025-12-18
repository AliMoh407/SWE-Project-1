<?php

class UserController
{
    private mysqli $conn;
    private UserModel $userModel;

    public function __construct(mysqli $conn, UserModel $userModel)
    {
        $this->conn = $conn;
        $this->userModel = $userModel;
    }

    public function index(): void
    {
        requireRole(ROLE_ADMIN);

        $message = '';
        $message_type = '';

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $this->createUser($message, $message_type);
                    break;
                case 'update':
                    $this->updateUser($message, $message_type);
                    break;
                case 'delete':
                    $this->deleteUser($message, $message_type);
                    break;
            }
        }

        // Refresh users list via model after any changes
        $users = $this->userModel->getAll();

        $page_title = 'User Management';

        // Render view
        require __DIR__ . '/../views/admin/users.php';
    }

    private function createUser(string &$message, string &$message_type): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, password, role, name, email) 
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param(
                'sssss',
                $_POST['username'],
                $_POST['password'],
                $_POST['role'],
                $_POST['name'],
                $_POST['email']
            );
            $stmt->execute();
            $stmt->close();
            $message = 'User created successfully!';
            $message_type = 'success';
        }
    }

    private function updateUser(string &$message, string &$message_type): void
    {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        // If password is provided, update it; otherwise keep the old one
        if (!empty($_POST['password'])) {
            $stmt = $this->conn->prepare(
                "UPDATE users 
                 SET username = ?, password = ?, role = ?, name = ?, email = ? 
                 WHERE id = ?"
            );
            if ($stmt) {
                $stmt->bind_param(
                    'sssssi',
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['role'],
                    $_POST['name'],
                    $_POST['email'],
                    $id
                );
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE users 
                 SET username = ?, role = ?, name = ?, email = ? 
                 WHERE id = ?"
            );
            if ($stmt) {
                $stmt->bind_param(
                    'ssssi',
                    $_POST['username'],
                    $_POST['role'],
                    $_POST['name'],
                    $_POST['email'],
                    $id
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        $message = 'User updated successfully!';
        $message_type = 'success';
    }

    private function deleteUser(string &$message, string &$message_type): void
    {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }

        $message = 'User deleted successfully!';
        $message_type = 'success';
    }
}


