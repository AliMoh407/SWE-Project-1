<?php
require_once 'config.php';
require_once __DIR__ . '/controllers/ActivityLogController.php';

$controller = new ActivityLogController();
$controller->index();

