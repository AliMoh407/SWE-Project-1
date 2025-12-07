<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/InventoryController.php';

$controller = new InventoryController();
$controller->index();

