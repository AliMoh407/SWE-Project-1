<?php
require_once 'config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Delegate request handling to the AuthController (MVC)
$controller = new AuthController($userModel);
$controller->login();
