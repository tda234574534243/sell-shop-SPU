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
        $this->setQuery($sql); $this->excuteQuery();
    }

    public function add($data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("INSERT INTO Vouchers (Code, Description, DiscountPercent, DiscountAmount, ValidFrom, ValidTo, Quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiddsi', $data['Code'], $data['Description'], $data['DiscountPercent'], $data['DiscountAmount'], $data['ValidFrom'], $data['ValidTo'], $data['Quantity']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE Vouchers SET Code = ?, Description = ?, DiscountPercent = ?, DiscountAmount = ?, ValidFrom = ?, ValidTo = ?, Quantity = ? WHERE id = ?");
        $stmt->bind_param('ssiddsii', $data['Code'], $data['Description'], $data['DiscountPercent'], $data['DiscountAmount'], $data['ValidFrom'], $data['ValidTo'], $data['Quantity'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("DELETE FROM Vouchers WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getById($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Vouchers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function getAll($limit = 50) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Vouchers ORDER BY CreatedAt DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}

?>
