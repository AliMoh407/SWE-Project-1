<?php

require_once __DIR__ . '/../core/Observer.php';

class DoctorRequestController
{
    private InventoryModel $inventoryModel;
    private RequestModel $requestModel;
    private EventNotifier $eventNotifier;

    public function __construct(InventoryModel $inventoryModel, RequestModel $requestModel, ActivityLogModel $activityLogModel)
    {
        $this->inventoryModel = $inventoryModel;
        $this->requestModel = $requestModel;
        $this->eventNotifier = EventNotifier::getInstance();
    }

    public function create(): void
    {
        requireRole(ROLE_DOCTOR);

        global $inventory;

        // Ensure inventory is loaded
        if (!isset($inventory) || $inventory === null) {
            $inventory = $this->inventoryModel->getAll();
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
                $item = $this->inventoryModel->findById((int)$item_id);
                
                if ($item) {
                    // Validate quantity is positive
                    $quantity = (int)$quantity;
                    if ($quantity <= 0) {
                        $message = 'Quantity must be greater than 0.';
                        $message_type = 'error';
                    } else {
                        // Create request in database
                        $requestData = [
                            'doctor_id' => $_SESSION['user_id'],
                            'item_id' => (int)$item_id,
                            'quantity' => $quantity,
                            'patient_id' => trim($patient_id),
                            'patient_name' => trim($patient_name),
                            'notes' => trim($notes),
                            'status' => $item['controlled'] ? 'Pending' : 'Approved',
                            'priority' => $item['controlled'] ? 'high' : 'normal'
                        ];
                        
                        if ($this->requestModel->create($requestData)) {
                            // Use Observer Pattern to automatically log activity
                            $this->eventNotifier->notify('request.create', [
                                'item_name' => $item['name'],
                                'quantity' => $quantity,
                                'status' => $requestData['status']
                            ]);
                            
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
        $request_history = $this->requestModel->getByDoctor($_SESSION['user_id']);

        $page_title = 'Request Items';

        require __DIR__ . '/../views/doctor/requests.php';
    }
}


