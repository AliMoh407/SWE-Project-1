<?php
require_once 'config.php';
require_once __DIR__ . '/controllers/DoctorRequestController.php';

$controller = new DoctorRequestController();
$controller->create();

