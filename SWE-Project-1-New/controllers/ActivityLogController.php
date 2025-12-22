<?php

class ActivityLogController
{
    public function index(): void
    {
        requireRole(ROLE_ADMIN);

        global $activity_logs;

        $page_title = 'Activity Logs';

        require __DIR__ . '/../views/admin/activity_logs.php';
    }
}


