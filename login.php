<?php
require_once 'config.php';

// Use Factory Pattern to create controller
$factory = ControllerFactory::getInstance();
$controller = $factory->create('login');
$controller->login();
