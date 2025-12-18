<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController($conn, $userModel);
$controller->index();

