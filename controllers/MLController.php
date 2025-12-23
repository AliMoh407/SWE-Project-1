<?php

class MLController
{
    public function predictDemand(): void
    {
        requireRole(ROLE_PHARMACIST);

        global $mlService, $inventoryModel;

        $itemId = $_GET['item_id'] ?? 0;
        $daysAhead = (int)($_GET['days'] ?? 30);

        if ($itemId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid item ID']);
            exit();
        }

        $prediction = $mlService->predictDemand($itemId, $daysAhead);
        $item = $inventoryModel->findById($itemId);

        header('Content-Type: application/json');
        echo json_encode([
            'item_id' => $itemId,
            'item_name' => $item['name'] ?? 'Unknown',
            'prediction' => $prediction,
            'current_stock' => $item['stock'] ?? 0,
            'min_stock' => $item['min_stock'] ?? 0
        ]);
    }

    public function getOptimalReorder(): void
    {
        requireRole(ROLE_PHARMACIST);

        global $mlService, $inventoryModel;

        $itemId = $_GET['item_id'] ?? 0;

        if ($itemId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid item ID']);
            exit();
        }

        $item = $inventoryModel->findById($itemId);
        if (!$item) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Item not found']);
            exit();
        }

        $recommendation = $mlService->getOptimalReorderQuantity(
            $itemId,
            $item['stock'],
            $item['min_stock']
        );

        header('Content-Type: application/json');
        echo json_encode($recommendation);
    }

    public function detectAnomaly(): void
    {
        requireRole(ROLE_ADMIN);

        global $mlService;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'POST method required']);
            exit();
        }

        $requestData = [
            'item_id' => (int)($_POST['item_id'] ?? 0),
            'quantity' => (int)($_POST['quantity'] ?? 0),
            'doctor_id' => (int)($_POST['doctor_id'] ?? 0)
        ];

        $anomaly = $mlService->detectAnomaly($requestData);

        // Store anomaly if detected
        if ($anomaly['is_anomaly']) {
            global $conn;
            $stmt = $conn->prepare(
                "INSERT INTO ml_anomalies (request_id, item_id, anomaly_type, anomaly_score, description) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $requestId = (int)($_POST['request_id'] ?? 0);
            $anomalyType = 'unusual_quantity';
            $description = implode('; ', $anomaly['reasons'] ?? []);
            $stmt->bind_param('iisds', 
                $requestId, 
                $requestData['item_id'], 
                $anomalyType, 
                $anomaly['score'], 
                $description
            );
            $stmt->execute();
            $stmt->close();
        }

        header('Content-Type: application/json');
        echo json_encode($anomaly);
    }

    public function trainModels(): void
    {
        requireRole(ROLE_ADMIN);

        global $mlService;

        $results = $mlService->trainModels();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'trained_items' => count($results),
            'results' => $results
        ]);
    }

    public function getAnomalies(): void
    {
        requireRole(ROLE_ADMIN);

        global $conn;

        $resolved = isset($_GET['resolved']) ? (int)$_GET['resolved'] : 0;
        
        $stmt = $conn->prepare(
            "SELECT a.*, i.name as item_name, r.patient_name 
             FROM ml_anomalies a
             LEFT JOIN inventory i ON a.item_id = i.id
             LEFT JOIN requests r ON a.request_id = r.id
             WHERE a.resolved = ?
             ORDER BY a.created_at DESC
             LIMIT 50"
        );
        
        $stmt->bind_param('i', $resolved);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $anomalies = [];
        while ($row = $result->fetch_assoc()) {
            $anomalies[] = $row;
        }
        
        $stmt->close();

        header('Content-Type: application/json');
        echo json_encode($anomalies);
    }
}

