<?php
require_once '../config.php';
require_once __DIR__ . '/../controllers/DoctorHistoryController.php';

$controller = new DoctorHistoryController();
$controller->index();
