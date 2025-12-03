<?php
require_once '../config.php';
require_once __DIR__ . '/../controllers/UserController.php';

// Delegate request handling to the UserController (MVC)
$controller = new UserController($conn, $userModel);
$controller->index();
