<?php

class DoctorRequestController
{
    public function create(): void
    {
        requireRole(ROLE_DOCTOR);

        global $inventoryModel, $requestModel, $activityLogModel;
        global $inventory;

        $message = '';
        $message_type = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_id = $_POST['item_id'] ?? '';
            $quantity = $_POST['quantity'] ?? '';
            $patient_id = $_POST['patient_id'] ?? '';
            $patient_name = $_POST['patient_name'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            if ($item_id && $quantity && $patient_id && $patient_name) {
                $item = $inventoryModel->findById((int)$item_id);
                
                if ($item) {
                    // Create request in database
                    $requestData = [
                        'doctor_id' => $_SESSION['user_id'],
                        'item_id' => (int)$item_id,
                        'quantity' => (int)$quantity,
                        'patient_id' => $patient_id,
                        'patient_name' => $patient_name,
                        'notes' => $notes,
                        'status' => $item['controlled'] ? 'Pending' : 'Approved',
                        'priority' => $item['controlled'] ? 'high' : 'normal'
                    ];
                    
                    if ($requestModel->create($requestData)) {
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
                    } else {
                        $message = 'Failed to submit request. Please try again.';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Item not found.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Please fill in all required fields.';
                $message_type = 'error';
            }
        }

        // Get request history for current doctor
        $request_history = $requestModel->getByDoctor($_SESSION['user_id']);

        $page_title = 'Request Items';

        require __DIR__ . '/../views/doctor/requests.php';
    }
}


