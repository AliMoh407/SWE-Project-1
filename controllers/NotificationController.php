<?php

class NotificationController
{
    public function index(): void
    {
        requireRole(ROLE_PHARMACIST);

        global $inventoryModel, $notificationModel;

        // Get notifications data from inventory
        $low_stock_items = $inventoryModel->getLowStock();
        $expiring_soon_items = $inventoryModel->getExpiringSoon();

        // Get notifications from database
        $notifications = $notificationModel->getAll();

        // Handle notification actions
        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'mark_read':
                    $id = (int)($_POST['notification_id'] ?? 0);
                    if ($id > 0 && $notificationModel->markAsRead($id)) {
                        $message = 'Notification marked as read';
                    }
                    break;
                case 'mark_all_read':
                    if ($notificationModel->markAllAsRead()) {
                        $message = 'All notifications marked as read';
                    }
                    break;
                case 'delete':
                    $id = (int)($_POST['notification_id'] ?? 0);
                    if ($id > 0 && $notificationModel->delete($id)) {
                        $message = 'Notification deleted';
                    }
                    break;
            }
            // Refresh notifications after action
            $notifications = $notificationModel->getAll();
        }

        $page_title = 'Notifications';

        require __DIR__ . '/../views/pharmacist/notifications.php';
    }
}


