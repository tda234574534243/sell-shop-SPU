<?php
require_once 'm_database.php';

class M_notification extends M_database {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS Notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Title VARCHAR(255) NOT NULL,
            Content TEXT,
            Type VARCHAR(50) DEFAULT 'notice',
            RelatedID INT DEFAULT NULL,
            IsActive TINYINT(1) NOT NULL DEFAULT 1,
            CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TIMESTAMP NULL
        )";
        $this->setQuery($sql); $this->excuteQuery();
    }

    public function add($data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("INSERT INTO Notifications (Title, Content, Type, RelatedID, IsActive) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $data['Title'], $data['Content'], $data['Type'], $data['RelatedID'], $data['IsActive']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE Notifications SET Title = ?, Content = ?, Type = ?, RelatedID = ?, IsActive = ?, UpdatedAt = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param('sssiii', $data['Title'], $data['Content'], $data['Type'], $data['RelatedID'], $data['IsActive'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("DELETE FROM Notifications WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function getById($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Notifications WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function getActive($limit = 10) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Notifications WHERE IsActive = 1 ORDER BY CreatedAt DESC LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function countActive() {
        $conn = $this->getConnection();
        $res = $conn->query("SELECT COUNT(*) as cnt FROM Notifications WHERE IsActive = 1");
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? intval($row['cnt']) : 0;
    }
}

?>
