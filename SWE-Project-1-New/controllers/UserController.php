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
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // If POST but no action, show error
            $message = 'Error: No action specified in form submission.';
            $message_type = 'error';
        }

        // Refresh users list via model after any changes
        $users = $this->userModel->getAll();

        $page_title = 'User Management';

        // Render view
        require __DIR__ . '/../views/admin/users.php';
    }

    private function createUser(string &$message, string &$message_type): void
    {
        // Validate required fields
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? '');
        
        if (empty($username) || empty($password) || empty($name) || empty($email) || empty($role)) {
            $message = 'All fields are required! Please fill in username, password, name, email, and role.';
            $message_type = 'error';
            return;
        }
        
        // Check if username already exists
        $checkStmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        if ($checkStmt) {
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            if ($result->num_rows > 0) {
                $message = 'Username already exists! Please choose a different username.';
                $message_type = 'error';
                $checkStmt->close();
                return;
            }
            $checkStmt->close();
        }
        
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if (!$hashedPassword) {
            $message = 'Error: Failed to hash password.';
            $message_type = 'error';
            return;
        }
        
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, password, role, name, email) 
             VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $message = 'Database error preparing statement: ' . $this->conn->error;
            $message_type = 'error';
            return;
        }
        
        $stmt->bind_param(
            'sssss',
            $username,
            $hashedPassword,
            $role,
            $name,
            $email
        );
        
        if ($stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            if ($affectedRows > 0) {
                $message = 'User created successfully!';
                $message_type = 'success';
            } else {
                $message = 'User was not created. No rows affected.';
                $message_type = 'error';
            }
        } else {
            $error = $stmt->error;
            // Check for duplicate entry error
            if (strpos($error, 'Duplicate entry') !== false) {
                $message = 'Username or email already exists! Please choose different values.';
            } else {
                $message = 'Error creating user: ' . $error;
            }
            $message_type = 'error';
        }
        $stmt->close();
    }

    private function updateUser(string &$message, string &$message_type): void
    {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        // If password is provided, hash and update it; otherwise keep the old one
        if (!empty($_POST['password'])) {
            // Hash the password before storing
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare(
                "UPDATE users 
                 SET username = ?, password = ?, role = ?, name = ?, email = ? 
                 WHERE id = ?"
            );
            if ($stmt) {
                $stmt->bind_param(
                    'sssssi',
                    $_POST['username'],
                    $hashedPassword,
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


