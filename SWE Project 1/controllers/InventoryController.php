<?php

class InventoryController
{
    public function index(): void
    {
        requireRole(ROLE_PHARMACIST);

        global $inventoryModel, $inventory;

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
        }

        if ($category_filter) {
            $filtered_inventory = array_filter($filtered_inventory, function($item) use ($category_filter) {
                return $item['category'] === $category_filter;
            });
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
                    break;
            }
        }

        // Get unique categories for filter dropdown
        $categories = array_unique(array_column($inventory, 'category'));

        $page_title = 'Inventory Management';

        require __DIR__ . '/../views/pharmacist/inventory.php';
    }
}


