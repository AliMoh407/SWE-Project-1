<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/MLController.php';

$mlController = new MLController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'predict-demand':
        $mlController->predictDemand();
        break;
    
    case 'optimal-reorder':
        $mlController->getOptimalReorder();
        break;
    
    case 'detect-anomaly':
        $mlController->detectAnomaly();
        break;
    
    case 'train-models':
        $mlController->trainModels();
        break;
    
    case 'get-anomalies':
        $mlController->getAnomalies();
        break;
    
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        break;
}

