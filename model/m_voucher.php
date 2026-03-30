<?php
require_once 'm_database.php';

class M_voucher extends M_database {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS Vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Code VARCHAR(100) NOT NULL,
            Description TEXT,
            DiscountPercent INT DEFAULT NULL,
            DiscountAmount DECIMAL(10,2) DEFAULT NULL,
            ValidFrom DATE DEFAULT NULL,
            ValidTo DATE DEFAULT NULL,
            Quantity INT DEFAULT NULL,
            CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->setQuery($sql);
        $this->excuteQuery();
    }

    public function add($data) {
        $conn = $this->getConnection();
        
        // Validate required fields
        if (empty($data['Code'])) {
            error_log("Voucher add: Code is empty");
            return false;
        }
        
        // Build dynamic INSERT with only non-empty values
        $fields = ['Code', 'Description'];
        $values = [$data['Code'], $data['Description'] ?? ''];
        $types = 'ss';
        
        if (!empty($data['DiscountPercent'])) {
            $fields[] = 'DiscountPercent';
            $values[] = intval($data['DiscountPercent']);
            $types .= 'i';
        }
        if (!empty($data['DiscountAmount'])) {
            $fields[] = 'DiscountAmount';
            $values[] = floatval($data['DiscountAmount']);
            $types .= 'd';
        }
        if (!empty($data['ValidFrom'])) {
            $fields[] = 'ValidFrom';
            $values[] = $data['ValidFrom'];
            $types .= 's';
        }
        if (!empty($data['ValidTo'])) {
            $fields[] = 'ValidTo';
            $values[] = $data['ValidTo'];
            $types .= 's';
        }
        if (!empty($data['Quantity'])) {
            $fields[] = 'Quantity';
            $values[] = intval($data['Quantity']);
            $types .= 'i';
        }
        
        $fieldStr = implode(', ', $fields);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO Vouchers ($fieldStr) VALUES ($placeholders)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Voucher add prepare error: " . $conn->error);
            return false;
        }
        
        // Bind params dynamically
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Voucher add execute error: " . $stmt->error);
        }
        return $result;
    }

    public function update($id, $data) {
        $conn = $this->getConnection();
        
        // Validate required fields
        if (!$id || empty($data['Code'])) {
            error_log("Voucher update: Invalid ID or empty Code. ID=$id");
            return false;
        }
        
        // Build dynamic UPDATE with only non-empty values
        $updates = [];
        $values = [];
        $types = '';
        
        // Always update Code and Description
        $updates[] = 'Code = ?';
        $values[] = $data['Code'];
        $types .= 's';
        
        $updates[] = 'Description = ?';
        $values[] = $data['Description'] ?? '';
        $types .= 's';
        
        if (!empty($data['DiscountPercent'])) {
            $updates[] = 'DiscountPercent = ?';
            $values[] = intval($data['DiscountPercent']);
            $types .= 'i';
        } else {
            $updates[] = 'DiscountPercent = NULL';
        }
        
        if (!empty($data['DiscountAmount'])) {
            $updates[] = 'DiscountAmount = ?';
            $values[] = floatval($data['DiscountAmount']);
            $types .= 'd';
        } else {
            $updates[] = 'DiscountAmount = NULL';
        }
        
        if (!empty($data['ValidFrom'])) {
            $updates[] = 'ValidFrom = ?';
            $values[] = $data['ValidFrom'];
            $types .= 's';
        } else {
            $updates[] = 'ValidFrom = NULL';
        }
        
        if (!empty($data['ValidTo'])) {
            $updates[] = 'ValidTo = ?';
            $values[] = $data['ValidTo'];
            $types .= 's';
        } else {
            $updates[] = 'ValidTo = NULL';
        }
        
        if (!empty($data['Quantity'])) {
            $updates[] = 'Quantity = ?';
            $values[] = intval($data['Quantity']);
            $types .= 'i';
        } else {
            $updates[] = 'Quantity = NULL';
        }
        
        // Add ID at the end
        $values[] = intval($id);
        $types .= 'i';
        
        $updateStr = implode(', ', $updates);
        $sql = "UPDATE Vouchers SET $updateStr WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Voucher update prepare error: " . $conn->error);
            return false;
        }
        
        // Bind params dynamically
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Voucher update execute error: " . $stmt->error . " SQL: " . $sql);
        }
        return $result;
    }

    public function delete($id) {
        $conn = $this->getConnection();
        
        if (!$id || !is_numeric($id)) {
            error_log("Voucher delete: Invalid ID. ID=$id");
            return false;
        }
        
        $stmt = $conn->prepare("DELETE FROM Vouchers WHERE id = ?");
        if ($stmt === false) {
            error_log("Voucher delete prepare error: " . $conn->error);
            return false;
        }
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Voucher delete execute error: " . $stmt->error);
        }
        return $result;
    }

    public function getById($id) {
        $conn = $this->getConnection();
        
        if (!$id || !is_numeric($id)) {
            error_log("Voucher getById: Invalid ID. ID=$id");
            return null;
        }
        
        $stmt = $conn->prepare("SELECT * FROM Vouchers WHERE id = ?");
        if ($stmt === false) {
            error_log("Voucher getById prepare error: " . $conn->error);
            return null;
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            error_log("Voucher getById execute error: " . $stmt->error);
            return null;
        }
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function getAll($limit = 50) {
        $conn = $this->getConnection();
        $limit = (int)$limit;
        
        if ($limit <= 0) {
            $limit = 50;
        }
        
        $stmt = $conn->prepare("SELECT * FROM Vouchers ORDER BY CreatedAt DESC LIMIT " . $limit);
        if ($stmt === false) {
            error_log("Voucher getAll prepare error: " . $conn->error);
            return null;
        }
        if (!$stmt->execute()) {
            error_log("Voucher getAll execute error: " . $stmt->error);
            return null;
        }
        return $stmt->get_result();
    }
}

?>