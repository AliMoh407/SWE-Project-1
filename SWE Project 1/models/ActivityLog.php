<?php

class ActivityLogModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        // Check if table exists first
        $tableCheck = $this->conn->query("SHOW TABLES LIKE 'activity_logs'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            return [];
        }
        
        $result = $this->conn->query(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             ORDER BY al.timestamp DESC"
        );
        $logs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Use user_name from join, or fallback to user field if it exists
                if (empty($row['user_name']) && !empty($row['user'])) {
                    $row['user'] = $row['user'];
                } else {
                    $row['user'] = $row['user_name'] ?? 'System';
                }
                $logs[] = $row;
            }
            $result->free();
        }
        return $logs;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.id = ?"
        );
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $log = $result->fetch_assoc();
        $stmt->close();
        
        if ($log) {
            $log['user'] = $log['user_name'] ?? 'System';
        }
        return $log ?: null;
    }

    public function create(int $userId, string $action, string $status = 'Completed'): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO activity_logs (user_id, action, status, timestamp) 
             VALUES (?, ?, ?, NOW())"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('iss', $userId, $action, $status);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getByStatus(string $status): array
    {
        $stmt = $this->conn->prepare(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.status = ? 
             ORDER BY al.timestamp DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $row['user'] = $row['user_name'] ?? 'System';
            $logs[] = $row;
        }
        $stmt->close();
        return $logs;
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.user_id = ? 
             ORDER BY al.timestamp DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $row['user'] = $row['user_name'] ?? 'System';
            $logs[] = $row;
        }
        $stmt->close();
        return $logs;
    }
}

