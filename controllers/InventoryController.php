<?php

class InventoryController
{
    public function index(): void
    {
        requireRole(ROLE_PHARMACIST);

        global $inventoryModel, $inventory, $activityLogModel;

        $message = '';
        $message_type = '';

        // Handle POST requests (form submissions)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'add_item':
                    $message = $this->handleAddItem($inventoryModel, $activityLogModel);
                    $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
                    break;

                case 'edit_item':
                    $message = $this->handleEditItem($inventoryModel, $activityLogModel);
                    $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
                    break;

                case 'adjust_stock':
                    $message = $this->handleAdjustStock($inventoryModel, $activityLogModel);
                    $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
                    break;

                default:
                    $message = 'Invalid action.';
                    $message_type = 'error';
            }

            // Redirect to prevent form resubmission
            if ($message_type === 'success') {
                header('Location: ' . getBaseUrl() . 'routes/pharmacist_inventory.php?success=1');
                exit();
            }
        }

        // Check for success message from redirect
        if (isset($_GET['success']) && $_GET['success'] == '1') {
            $message = 'Operation completed successfully!';
            $message_type = 'success';
        }

        // Reload inventory to ensure we have the latest data (especially after POST operations)
        $inventory = $inventoryModel->getAll();

        $search = $_GET['search'] ?? '';
        $category_filter = $_GET['category'] ?? '';
        $status_filter = $_GET['status'] ?? '';

        // Filter inventory based on search and filters
        $filtered_inventory = $inventory;

        if ($search) {
            $filtered_inventory = array_filter($filtered_inventory, function($item) use ($search) {
                return stripos($item['name'], $search) !== false || 
                       stripos($item['category'], $search) !== false;
            });
            $filtered_inventory = array_values($filtered_inventory); // Re-index array
        }

        if ($category_filter) {
            $filtered_inventory = array_filter($filtered_inventory, function($item) use ($category_filter) {
                return $item['category'] === $category_filter;
            });
            $filtered_inventory = array_values($filtered_inventory); // Re-index array
        }

        if ($status_filter) {
            switch ($status_filter) {
                case 'low_stock':
                    $filtered_inventory = $inventoryModel->getLowStock();
                    break;
                case 'expiring_soon':
                    $filtered_inventory = $inventoryModel->getExpiringSoon();
                    break;
                case 'controlled':
                    $filtered_inventory = array_filter($inventory, function($item) {
                        return $item['controlled'];
                    });
                    $filtered_inventory = array_values($filtered_inventory); // Re-index array
                    break;
            }
        }
        
        // Ensure filtered_inventory is always an array
        if (!is_array($filtered_inventory)) {
            $filtered_inventory = [];
        }

        // Get unique categories for filter dropdown
        $categories = array_unique(array_column($inventory, 'category'));

        $page_title = 'Inventory Management';

        require __DIR__ . '/../views/pharmacist/inventory.php';
    }

    private function handleAddItem($inventoryModel, $activityLogModel): string
    {
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        $min_stock = $_POST['min_stock'] ?? 0;
        $expiry_date = $_POST['expiry_date'] ?? '';
        $price = $_POST['price'] ?? 0;
        $controlled = isset($_POST['controlled']) ? 1 : 0;

        if (empty($name) || empty($category) || empty($expiry_date)) {
            return 'Please fill in all required fields.';
        }

        $data = [
            'name' => trim($name),
            'category' => trim($category),
            'stock' => (int)$stock,
            'min_stock' => (int)$min_stock,
            'expiry_date' => $expiry_date,
            'price' => (float)$price,
            'controlled' => $controlled
        ];

        if ($inventoryModel->create($data)) {
            $activityLogModel->create(
                $_SESSION['user_id'],
                "Added new inventory item: {$data['name']}",
                'Completed'
            );
            return 'Item added successfully!';
        }

        return 'Failed to add item. Please try again.';
    }

    private function handleEditItem($inventoryModel, $activityLogModel): string
    {
        $item_id = $_POST['item_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        $min_stock = $_POST['min_stock'] ?? 0;
        $expiry_date = $_POST['expiry_date'] ?? '';
        $price = $_POST['price'] ?? 0;
        $controlled = isset($_POST['controlled']) ? 1 : 0;

        if (empty($item_id) || empty($name) || empty($category) || empty($expiry_date)) {
            return 'Please fill in all required fields.';
        }

        $data = [
            'name' => trim($name),
            'category' => trim($category),
            'stock' => (int)$stock,
            'min_stock' => (int)$min_stock,
            'expiry_date' => $expiry_date,
            'price' => (float)$price,
            'controlled' => $controlled
        ];

        if ($inventoryModel->update((int)$item_id, $data)) {
            $activityLogModel->create(
                $_SESSION['user_id'],
                "Updated inventory item: {$data['name']}",
                'Completed'
            );
            return 'Item updated successfully!';
        }

        return 'Failed to update item. Please try again.';
    }

    private function handleAdjustStock($inventoryModel, $activityLogModel): string
    {
        $item_id = $_POST['item_id'] ?? '';
        $adjustment_type = $_POST['adjustment_type'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $reason = $_POST['reason'] ?? '';

        if (empty($item_id) || empty($adjustment_type) || empty($amount) || $amount <= 0) {
            return 'Please provide valid adjustment details.';
        }

        $item = $inventoryModel->findById((int)$item_id);
        if (!$item) {
            return 'Item not found.';
        }

        if ($inventoryModel->adjustStock((int)$item_id, (int)$amount, $adjustment_type)) {
            $activityLogModel->create(
                $_SESSION['user_id'],
                "Adjusted stock for {$item['name']}: {$adjustment_type} {$amount}" . ($reason ? " (Reason: {$reason})" : ''),
                'Completed'
            );
            return 'Stock adjusted successfully!';
        }

        return 'Failed to adjust stock. Please try again.';
    }
}


