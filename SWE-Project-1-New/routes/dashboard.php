<?php
require_once __DIR__ . '/../config.php';

// Use Factory Pattern to create controller
$factory = ControllerFactory::getInstance();
$controller = $factory->create('dashboard');
$controller->index();

