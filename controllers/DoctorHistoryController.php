<?php

class DoctorHistoryController
{
    public function index(): void
    {
        requireRole(ROLE_DOCTOR);

        global $requestModel;

        $search = $_GET['search'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        $date_filter = $_GET['date_filter'] ?? '';

        // Get request history for current doctor
        $request_history = $requestModel->getByDoctor($_SESSION['user_id']);

        // Filter requests based on search criteria
        $filtered_requests = $request_history;

        if ($search) {
            $filtered_requests = $requestModel->search($search);
            // Filter by doctor
            $filtered_requests = array_filter($filtered_requests, function($r) {
                return $r['doctor_id'] == $_SESSION['user_id'];
            });
        }

        if ($status_filter) {
            $filtered_requests = array_filter($filtered_requests, function($request) use ($status_filter) {
                return strtolower($request['status']) === strtolower($status_filter);
            });
        }

        if ($date_filter) {
            $filtered_requests = array_filter($filtered_requests, function($request) use ($date_filter) {
                $request_date = new DateTime($request['requested_date']);
                $today = new DateTime();
                
                switch ($date_filter) {
                    case 'today':
                        return $request_date->format('Y-m-d') === $today->format('Y-m-d');
                    case 'week':
                        $week_ago = clone $today;
                        $week_ago->modify('-7 days');
                        return $request_date >= $week_ago;
                    case 'month':
                        $month_ago = clone $today;
                        $month_ago->modify('-30 days');
                        return $request_date >= $month_ago;
                    default:
                        return true;
                }
            });
        }

        // Calculate statistics
        $total_requests = count($request_history);
        $approved_requests = count(array_filter($request_history, function($r) { return $r['status'] === 'Approved'; }));
        $pending_requests = count(array_filter($request_history, function($r) { return $r['status'] === 'Pending'; }));
        $rejected_requests = count(array_filter($request_history, function($r) { return $r['status'] === 'Rejected'; }));

        $page_title = 'Request History';

        require __DIR__ . '/../views/doctor/request_history.php';
    }
}


