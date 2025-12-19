<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/PharmacistRequestController.php';
require_once __DIR__ . '/../core/ControllerFactory.php';
require_once __DIR__ . '/../core/Observer.php';

$controllerFactory = ControllerFactory::getInstance();
$eventNotifier = EventNotifier::getInstance();

// Get dependencies
$requestModel = $controllerFactory->getRequestModel();
$inventoryModel = $controllerFactory->getInventoryModel();

// Create controller with dependencies
$controller = new PharmacistRequestController($requestModel, $inventoryModel, $eventNotifier);
$controller->index();

