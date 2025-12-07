<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/DoctorHistoryController.php';

$controller = new DoctorHistoryController();
$controller->index();

