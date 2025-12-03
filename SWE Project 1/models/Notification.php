<?php

class NotificationModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $result = $this->conn->query(
            "SELECT * FROM notifications 
             ORDER BY timestamp DESC"
        );
        $notifications = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['read'] = (bool)$row['read'];
                $notifications[] = $row;
            }
            $result->free();
        }
        return $notifications;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();
        $stmt->close();
        
        if ($notification) {
            $notification['read'] = (bool)$notification['read'];
        }
        return $notification ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO notifications (type, title, message, item_id, priority, timestamp) 
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            return false;
        }
        
        $itemId = $data['item_id'] ?? null;
        $priority = $data['priority'] ?? 'low';
        
        $stmt->bind_param(
            'sssis',
            $data['type'],
            $data['title'],
            $data['message'],
            $itemId,
            $priority
        );
        
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->conn->prepare("UPDATE notifications SET `read` = 1 WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function markAllAsRead(): bool
    {
        $result = $this->conn->query("UPDATE notifications SET `read` = 1 WHERE `read` = 0");
        return $result !== false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM notifications WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getUnread(): array
    {
        $result = $this->conn->query(
            "SELECT * FROM notifications 
             WHERE `read` = 0 
             ORDER BY timestamp DESC"
        );
        $notifications = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['read'] = (bool)$row['read'];
                $notifications[] = $row;
            }
            $result->free();
        }
        return $notifications;
    }

    public function getByType(string $type): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM notifications 
             WHERE type = ? 
             ORDER BY timestamp DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $row['read'] = (bool)$row['read'];
            $notifications[] = $row;
        }
        $stmt->close();
        return $notifications;
    }

    public function getByPriority(string $priority): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM notifications 
             WHERE priority = ? AND `read` = 0 
             ORDER BY timestamp DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $priority);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $row['read'] = (bool)$row['read'];
            $notifications[] = $row;
        }
        $stmt->close();
        return $notifications;
    }
}

