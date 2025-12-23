<?php

class DoctorRequestController
{
    public function create(): void
    {
        requireRole(ROLE_DOCTOR);

        global $inventoryModel, $requestModel, $activityLogModel, $conn;
        global $inventory;

        // Ensure inventory is loaded
        if (!isset($inventory) || $inventory === null) {
            $inventory = $inventoryModel->getAll();
        }

        $message = '';
        $message_type = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = $_POST['item_id'] ?? '';
            $quantity = $_POST['quantity'] ?? '';
            $patient_id = $_POST['patient_id'] ?? '';
            $patient_name = $_POST['patient_name'] ?? '';
            $notes = $_POST['notes'] ?? '';

            // Validate required fields
            if (empty($item_id) || empty($quantity) || empty($patient_id) || empty($patient_name)) {
                $message = 'Please fill in all required fields.';
                $message_type = 'error';
            } else {
                $item = $inventoryModel->findById((int) $item_id);

                if ($item) {
                    // Validate quantity is positive
                    $quantity = (int) $quantity;
                    if ($quantity <= 0) {
                        $message = 'Quantity must be greater than 0.';
                        $message_type = 'error';
                    } else {
                        // Create request in database
                        $requestData = [
                            'doctor_id' => $_SESSION['user_id'],
                            'item_id' => (int) $item_id,
                            'quantity' => $quantity,
                            'patient_id' => trim($patient_id),
                            'patient_name' => trim($patient_name),
                            'notes' => trim($notes),
                            'status' => $item['controlled'] ? 'Pending' : 'Approved',
                            'priority' => $item['controlled'] ? 'high' : 'normal'
                        ];

                        if ($requestModel->create($requestData)) {
                            // Check for anomalies using ML
                            global $mlService;
                            if ($mlService) {
                                $requestId = $conn->insert_id;
                                $anomalyCheck = $mlService->detectAnomaly([
                                    'request_id' => $requestId,
                                    'item_id' => (int) $item_id,
                                    'quantity' => $quantity,
                                    'doctor_id' => $_SESSION['user_id']
                                ]);

                                // Store anomaly if detected
                                if ($anomalyCheck['is_anomaly'] && $anomalyCheck['score'] > 0.5) {
                                    $stmt = $conn->prepare(
                                        "INSERT INTO ml_anomalies (request_id, item_id, anomaly_type, anomaly_score, description) 
                                         VALUES (?, ?, ?, ?, ?)"
                                    );
                                    $anomalyType = 'unusual_quantity';
                                    $description = implode('; ', $anomalyCheck['reasons'] ?? []);
                                    $cleanItemId = (int) $item_id;
                                    $stmt->bind_param(
                                        'iisds',
                                        $requestId,
                                        $cleanItemId,
                                        $anomalyType,
                                        $anomalyCheck['score'],
                                        $description
                                    );
                                    $stmt->execute();
                                    $stmt->close();

                                    // Create notification for admin
                                    global $notificationModel;
                                    if ($notificationModel) {
                                        $notificationModel->create([
                                            'type' => 'anomaly',
                                            'title' => 'Anomaly Detected',
                                            'message' => "Unusual request detected: {$item['name']} x{$quantity} by " . getCurrentUser()['name'],
                                            'priority' => 'high'
                                        ]);
                                    }
                                }
                            }

                            // Log activity
                            $activityLogModel->create(
                                $_SESSION['user_id'],
                                "Requested {$item['name']} (Quantity: {$quantity})",
                                $requestData['status']
                            );

                            if ($item['controlled']) {
                                $message = 'Controlled medicine request submitted for approval.';
                                $message_type = 'warning';
                            } else {
                                $message = 'Item request submitted successfully!';
                                $message_type = 'success';
                            }

                            // Redirect to prevent form resubmission
                            header('Location: ' . getBaseUrl() . 'routes/doctor_requests.php?success=1');
                            exit();
                        } else {
                            $message = 'Failed to submit request. Please try again.';
                            $message_type = 'error';
                        }
                    }
                } else {
                    $message = 'Item not found. Please select a valid item.';
                    $message_type = 'error';
                }
            }
        }

        // Check for success message from redirect
        if (isset($_GET['success']) && $_GET['success'] == '1') {
            $message = 'Item request submitted successfully!';
            $message_type = 'success';
        }

        // Get request history for current doctor
        $request_history = $requestModel->getByDoctor($_SESSION['user_id']);

        $page_title = 'Request Items';

        require __DIR__ . '/../views/doctor/requests.php';
    }
}


