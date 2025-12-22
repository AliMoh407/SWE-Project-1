<?php

class AuthController
{
    private UserModel $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function login(): void
    {
        $error_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? ''; // Don't trim password as it might be intentional whitespace

            if (empty($username) || empty($password)) {
                $error_message = 'Please enter both username and password';
            } else {
                // Use the UserModel (OOP) to find the user by credentials
                $user = $this->userModel->findByCredentials($username, $password);

                if ($user) {    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: ' . getBaseUrl() . 'routes/dashboard.php');
                    exit();
                } else {
                    $error_message = 'Invalid username or password';
                }
            }
        }

        $page_title = 'Login';

        // Render view
        require __DIR__ . '/../views/auth/login.php';
    }
}


