<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/ReportController.php';

$controller = new ReportController();
$controller->index();

