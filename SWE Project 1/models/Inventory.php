<?php

class InventoryModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        // Check if table exists first
        $tableCheck = $this->conn->query("SHOW TABLES LIKE 'inventory'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            return [];
        }
        
        $result = $this->conn->query("SELECT * FROM inventory ORDER BY name");
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['controlled'] = (bool)$row['controlled'];
                $items[] = $row;
            }
            $result->free();
        }
        return $items;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM inventory WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        
        if ($item) {
            $item['controlled'] = (bool)$item['controlled'];
        }
        return $item ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO inventory (name, category, stock, min_stock, expiry_date, price, controlled) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            return false;
        }
        
        $controlled = isset($data['controlled']) ? 1 : 0;
        $stmt->bind_param(
            'ssiisdi',
            $data['name'],
            $data['category'],
            $data['stock'],
            $data['min_stock'],
            $data['expiry_date'],
            $data['price'],
            $controlled
        );
        
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE inventory 
             SET name = ?, category = ?, stock = ?, min_stock = ?, expiry_date = ?, price = ?, controlled = ? 
             WHERE id = ?"
        );
        if (!$stmt) {
            return false;
        }
        
        $controlled = isset($data['controlled']) ? 1 : 0;
        $stmt->bind_param(
            'ssiisdii',
            $data['name'],
            $data['category'],
            $data['stock'],
            $data['min_stock'],
            $data['expiry_date'],
            $data['price'],
            $controlled,
            $id
        );
        
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM inventory WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function adjustStock(int $id, int $amount, string $type): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        $newStock = $item['stock'];
        switch ($type) {
            case 'add':
                $newStock += $amount;
                break;
            case 'subtract':
                $newStock = max(0, $newStock - $amount);
                break;
            case 'set':
                $newStock = $amount;
                break;
            default:
                return false;
        }

        $stmt = $this->conn->prepare("UPDATE inventory SET stock = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $newStock, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getLowStock(): array
    {
        $result = $this->conn->query(
            "SELECT * FROM inventory WHERE stock <= min_stock ORDER BY name"
        );
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['controlled'] = (bool)$row['controlled'];
                $items[] = $row;
            }
            $result->free();
        }
        return $items;
    }

    public function getExpiringSoon(int $days = 30): array
    {
        $futureDate = date('Y-m-d', strtotime("+{$days} days"));
        $stmt = $this->conn->prepare(
            "SELECT * FROM inventory 
             WHERE expiry_date <= ? AND expiry_date >= CURDATE() 
             ORDER BY expiry_date"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $futureDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $row['controlled'] = (bool)$row['controlled'];
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }
}

