<?php

class ReportController
{
    public function index(): void
    {
        requireRole(ROLE_ADMIN);

        global $users, $inventory;

        // Calculate report statistics
        $total_users = count($users);
        $total_inventory = count($inventory);
        $low_stock_count = count(array_filter($inventory, 'isLowStock'));
        $expiring_soon_count = count(array_filter($inventory, function($item) { return isExpiringSoon($item); }));
        $controlled_medicines = count(array_filter($inventory, function($item) { return $item['controlled']; }));

        // User role distribution
        $role_distribution = array_count_values(array_column($users, 'role'));

        // Category distribution
        $category_distribution = array_count_values(array_column($inventory, 'category'));

        // Low stock items
        $low_stock_items = array_filter($inventory, 'isLowStock');

        // Expiring soon items
        $expiring_soon_items = array_filter($inventory, function($item) { return isExpiringSoon($item); });

        $page_title = 'Reports';

        require __DIR__ . '/../views/admin/reports.php';
    }
}


