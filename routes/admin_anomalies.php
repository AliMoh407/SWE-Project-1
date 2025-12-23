<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controllers/MLController.php';

requireRole(ROLE_ADMIN);

$mlController = new MLController();

// Handle training models
if (isset($_GET['train']) && $_GET['train'] == '1') {
    // This will output JSON, so we'll handle it separately
    $mlController->trainModels();
    exit();
}

// Get anomalies
$resolved = isset($_GET['resolved']) ? (int)$_GET['resolved'] : 0;

$stmt = $conn->prepare(
    "SELECT a.*, i.name as item_name, r.patient_name, r.quantity as request_quantity
     FROM ml_anomalies a
     LEFT JOIN inventory i ON a.item_id = i.id
     LEFT JOIN requests r ON a.request_id = r.id
     WHERE a.resolved = ?
     ORDER BY a.created_at DESC
     LIMIT 100"
);

$stmt->bind_param('i', $resolved);
$stmt->execute();
$result = $stmt->get_result();

$anomalies = [];
while ($row = $result->fetch_assoc()) {
    $anomalies[] = $row;
}

$stmt->close();

$page_title = 'ML Anomalies';
$message = '';
$message_type = '';

// Handle resolve action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve') {
    $anomalyId = (int)($_POST['anomaly_id'] ?? 0);
    if ($anomalyId > 0) {
        $updateStmt = $conn->prepare("UPDATE ml_anomalies SET resolved = 1 WHERE id = ?");
        $updateStmt->bind_param('i', $anomalyId);
        if ($updateStmt->execute()) {
            $message = 'Anomaly marked as resolved.';
            $message_type = 'success';
            header('Location: ' . getBaseUrl() . 'routes/admin_anomalies.php?success=1');
            exit();
        }
        $updateStmt->close();
    }
}

if (isset($_GET['success'])) {
    $message = 'Operation completed successfully!';
    $message_type = 'success';
}

include __DIR__ . '/../views/admin/anomalies.php';

