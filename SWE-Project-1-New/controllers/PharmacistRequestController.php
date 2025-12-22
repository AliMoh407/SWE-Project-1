<?php

require_once __DIR__ . '/../core/Observer.php';

class PharmacistRequestController
{
    private RequestModel $requestModel;
    private InventoryModel $inventoryModel;
    private EventNotifier $eventNotifier;

    public function __construct(RequestModel $requestModel, InventoryModel $inventoryModel, EventNotifier $eventNotifier)
    {
        $this->requestModel = $requestModel;
        $this->inventoryModel = $inventoryModel;
        $this->eventNotifier = $eventNotifier;
    }

    public function index(): void
    {
        requireRole(ROLE_PHARMACIST);

        $message = '';
        $message_type = '';

        // Handle POST requests (approve/reject)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $request_id = (int)($_POST['request_id'] ?? 0);

            if ($request_id > 0) {
                $request = $this->requestModel->findById($request_id);
                
                if ($request) {
                    switch ($action) {
                        case 'approve':
                            $result = $this->handleApprove($request);
                            if ($result['success']) {
                                $message = $result['message'];
                                $message_type = 'success';
                                // Redirect to prevent form resubmission
                                header('Location: ' . getBaseUrl() . 'routes/pharmacist_requests.php?success=approved');
                                exit();
                            } else {
                                $message = $result['message'];
                                $message_type = 'error';
                            }
                            break;

                        case 'reject':
                            $result = $this->handleReject($request);
                            if ($result['success']) {
                                $message = $result['message'];
                                $message_type = 'success';
                                // Redirect to prevent form resubmission
                                header('Location: ' . getBaseUrl() . 'routes/pharmacist_requests.php?success=rejected');
                                exit();
                            } else {
                                $message = $result['message'];
                                $message_type = 'error';
                            }
                            break;
                    }
                } else {
                    $message = 'Request not found.';
                    $message_type = 'error';
                }
            }
        }

        // Check for success message from redirect
        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'approved') {
                $message = 'Request approved successfully! Inventory stock has been updated.';
                $message_type = 'success';
            } elseif ($_GET['success'] === 'rejected') {
                $message = 'Request rejected successfully.';
                $message_type = 'success';
            }
        }

        // Get all pending requests (controlled medicines)
        $pending_requests = $this->requestModel->getByStatus('Pending');
        
        // Get all requests for display (with filters)
        $status_filter = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $all_requests = $this->requestModel->getAll();
        
        // Filter by status if provided
        if ($status_filter) {
            $all_requests = array_filter($all_requests, function($request) use ($status_filter) {
                return strtolower($request['status']) === strtolower($status_filter);
            });
            $all_requests = array_values($all_requests);
        }
        
        // Filter by search term if provided
        if ($search) {
            $all_requests = $this->requestModel->search($search);
            if ($status_filter) {
                $all_requests = array_filter($all_requests, function($request) use ($status_filter) {
                    return strtolower($request['status']) === strtolower($status_filter);
                });
                $all_requests = array_values($all_requests);
            }
        }

        // Pass inventoryModel to view
        $inventoryModel = $this->inventoryModel;
        $page_title = 'Manage Requests';

        require __DIR__ . '/../views/pharmacist/requests.php';
    }

    private function handleApprove(array $request): array
    {
        // Check if request is already approved
        if ($request['status'] === 'Approved') {
            return ['success' => false, 'message' => 'Request is already approved.'];
        }

        // Get current user name for approval tracking
        global $userModel;
        $currentUser = $userModel->findById($_SESSION['user_id']);
        $approvedBy = $currentUser ? $currentUser['name'] : 'Pharmacist';

        // Use the approveRequest method which handles stock reduction
        if ($this->requestModel->approveRequest($request['id'], $this->inventoryModel, $approvedBy)) {
            // Log activity using Observer Pattern
            $this->eventNotifier->notify('request.approve', [
                'item_name' => $request['item_name'],
                'quantity' => $request['quantity'],
                'description' => "Approved request for {$request['item_name']} (Quantity: {$request['quantity']}) - Stock reduced"
            ]);

            return [
                'success' => true,
                'message' => "Request #{$request['id']} approved successfully. Inventory stock has been reduced."
            ];
        } else {
            // Check if it's a stock issue
            $item = $this->inventoryModel->findById($request['item_id']);
            if ($item && $item['stock'] < $request['quantity']) {
                return [
                    'success' => false,
                    'message' => "Cannot approve: Insufficient stock. Available: {$item['stock']}, Requested: {$request['quantity']}"
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to approve request. Please try again.'
            ];
        }
    }

    private function handleReject(array $request): array
    {
        // Check if request is already processed
        if ($request['status'] !== 'Pending') {
            return ['success' => false, 'message' => 'Only pending requests can be rejected.'];
        }

        // Update request status to Rejected
        if ($this->requestModel->update($request['id'], ['status' => 'Rejected'])) {
            // Log activity using Observer Pattern
            $this->eventNotifier->notify('request.reject', [
                'item_name' => $request['item_name'],
                'quantity' => $request['quantity'],
                'description' => "Rejected request for {$request['item_name']} (Quantity: {$request['quantity']})"
            ]);

            return [
                'success' => true,
                'message' => "Request #{$request['id']} rejected successfully."
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to reject request. Please try again.'
            ];
        }
    }
}

