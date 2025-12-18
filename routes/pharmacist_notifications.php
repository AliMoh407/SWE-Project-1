<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

$controller = new NotificationController();
$controller->index();

