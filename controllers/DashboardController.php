<?php

class DashboardController
{
    public function index(): void
    {
        requireLogin();

        global $users, $inventory, $activity_logs;

        // Ensure variables are arrays (fallback to empty array if null)
        $users = $users ?? [];
        $inventory = $inventory ?? [];
        $activity_logs = $activity_logs ?? [];

        $user = getCurrentUser();
        $page_title = 'Dashboard';

        require __DIR__ . '/../views/dashboard/index.php';
    }
}


