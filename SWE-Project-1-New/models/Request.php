<?php

class RequestModel
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $result = $this->conn->query(
            "SELECT r.*, i.name as item_name, u.name as doctor_name 
             FROM requests r 
             LEFT JOIN inventory i ON r.item_id = i.id 
             LEFT JOIN users u ON r.doctor_id = u.id 
             ORDER BY r.requested_date DESC"
        );
        $requests = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
            $result->free();
        }
        return $requests;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.*, i.name as item_name, u.name as doctor_name 
             FROM requests r 
             LEFT JOIN inventory i ON r.item_id = i.id 
             LEFT JOIN users u ON r.doctor_id = u.id 
             WHERE r.id = ?"
        );
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();
        return $request ?: null;
    }

    public function getByDoctor(int $doctorId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.*, i.name as item_name, u.name as doctor_name 
             FROM requests r 
             LEFT JOIN inventory i ON r.item_id = i.id 
             LEFT JOIN users u ON r.doctor_id = u.id 
             WHERE r.doctor_id = ? 
             ORDER BY r.requested_date DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $doctorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
        return $requests;
    }

    public function create(array $data): int|false
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO requests (doctor_id, item_id, quantity, patient_id, patient_name, notes, status, priority, requested_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            return false;
        }
        
        $status = $data['status'] ?? 'Pending';
        $priority = $data['priority'] ?? 'normal';
        
        $stmt->bind_param(
            'iiisssss',
            $data['doctor_id'],
            $data['item_id'],
            $data['quantity'],
            $data['patient_id'],
            $data['patient_name'],
            $data['notes'],
            $status,
            $priority
        );
        
        $success = $stmt->execute();
        $insertId = $success ? $this->conn->insert_id : false;
        $stmt->close();
        return $insertId;
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $types = '';
        $values = [];
        
        $allowedFields = ['quantity', 'patient_id', 'patient_name', 'notes', 'status', 'priority'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $types .= $field === 'quantity' ? 'i' : 's';
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        if (isset($data['status']) && $data['status'] === 'Approved') {
            $fields[] = "approved_date = NOW()";
            if (isset($data['approved_by'])) {
                $fields[] = "approved_by = ?";
                $types .= 's';
                $values[] = $data['approved_by'];
            }
        }
        
        $types .= 'i';
        $values[] = $id;
        
        $sql = "UPDATE requests SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param($types, ...$values);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * Approve a request and reduce inventory stock
     * This method should be called when approving a pending request
     * 
     * @param int $requestId The request ID to approve
     * @param InventoryModel $inventoryModel The inventory model to update stock
     * @param string|null $approvedBy Optional: who approved the request
     * @return bool True if successful, false otherwise
     */
    public function approveRequest(int $requestId, InventoryModel $inventoryModel, ?string $approvedBy = null): bool
    {
        // Get the request details
        $request = $this->findById($requestId);
        if (!$request) {
            return false;
        }
        
        // Check if already approved
        if ($request['status'] === 'Approved') {
            return true; // Already approved
        }
        
        // Check if stock is available
        $item = $inventoryModel->findById($request['item_id']);
        if (!$item) {
            return false;
        }
        
        if ($item['stock'] < $request['quantity']) {
            return false; // Insufficient stock
        }
        
        // Update request status
        $updateData = [
            'status' => 'Approved'
        ];
        if ($approvedBy !== null) {
            $updateData['approved_by'] = $approvedBy;
        }
        
        if (!$this->update($requestId, $updateData)) {
            return false;
        }
        
        // Reduce inventory stock
        return $inventoryModel->adjustStock($request['item_id'], $request['quantity'], 'subtract');
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM requests WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getByStatus(string $status): array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.*, i.name as item_name, u.name as doctor_name 
             FROM requests r 
             LEFT JOIN inventory i ON r.item_id = i.id 
             LEFT JOIN users u ON r.doctor_id = u.id 
             WHERE r.status = ? 
             ORDER BY r.requested_date DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
        return $requests;
    }

    public function search(string $searchTerm): array
    {
        $searchTerm = "%{$searchTerm}%";
        $stmt = $this->conn->prepare(
            "SELECT r.*, i.name as item_name, u.name as doctor_name 
             FROM requests r 
             LEFT JOIN inventory i ON r.item_id = i.id 
             LEFT JOIN users u ON r.doctor_id = u.id 
             WHERE r.patient_name LIKE ? OR r.patient_id LIKE ? OR i.name LIKE ? 
             ORDER BY r.requested_date DESC"
        );
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();
        return $requests;
    }
}

