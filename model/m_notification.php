<?php
require_once 'm_database.php';

class M_notification extends M_database {
    public function __construct() {
        parent::__construct();
        $sql = "CREATE TABLE IF NOT EXISTS Notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Title VARCHAR(255) NOT NULL,
            Content TEXT,
            -- keep Type for backward compatibility
            Type VARCHAR(50) DEFAULT 'notice',
            RecipientUserId INT NOT NULL DEFAULT 0,
            RelatedID INT DEFAULT NULL,
            IsActive TINYINT(1) NOT NULL DEFAULT 1,
            CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TIMESTAMP NULL
        )";
        $this->setQuery($sql); $this->excuteQuery();

        // ensure RecipientUserId column exists for older schemas
        $conn = $this->getConnection();
        $colCheck = $conn->query("SHOW COLUMNS FROM Notifications LIKE 'RecipientUserId'");
        if ($colCheck && $colCheck->num_rows == 0) {
            $conn->query("ALTER TABLE Notifications ADD COLUMN RecipientUserId INT NOT NULL DEFAULT 0");
        }
    }

    public function add($data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("INSERT INTO Notifications (Title, Content, RelatedID, IsActive, RecipientUserId) VALUES (?, ?, ?, ?, ?)");
        $related = !empty($data['RelatedID']) ? intval($data['RelatedID']) : 0;
        $isActive = isset($data['IsActive']) ? intval($data['IsActive']) : 0;
        $recipient = isset($data['RecipientUserId']) ? intval($data['RecipientUserId']) : 0;
        $stmt->bind_param('ssiii', $data['Title'], $data['Content'], $related, $isActive, $recipient);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE Notifications SET Title = ?, Content = ?, RelatedID = ?, IsActive = ?, RecipientUserId = ?, UpdatedAt = CURRENT_TIMESTAMP WHERE id = ?");
        $related = !empty($data['RelatedID']) ? intval($data['RelatedID']) : 0;
        $isActive = isset($data['IsActive']) ? intval($data['IsActive']) : 0;
        $recipient = isset($data['RecipientUserId']) ? intval($data['RecipientUserId']) : 0;
        $stmt->bind_param('ssiiii', $data['Title'], $data['Content'], $related, $isActive, $recipient, $id);
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

    // $currentUserId: null = anonymous (only global), 0 = include only global, >0 = include global + user-specific
    // $admin: when true, ignore recipient filter and return all active notifications
    public function getActive($limit = 10, $currentUserId = null, $admin = false) {
        $conn = $this->getConnection();
        if ($admin) {
            $stmt = $conn->prepare("SELECT * FROM Notifications WHERE IsActive = 1 ORDER BY CreatedAt DESC LIMIT ?");
            $stmt->bind_param('i', $limit);
        } else {
            if ($currentUserId === null) {
                // anonymous visitor: only global (RecipientUserId = 0)
                $stmt = $conn->prepare("SELECT * FROM Notifications WHERE IsActive = 1 AND RecipientUserId = 0 ORDER BY CreatedAt DESC LIMIT ?");
                $stmt->bind_param('i', $limit);
            } else {
                // logged-in user or explicit 0: include global (0) and specific user id
                $stmt = $conn->prepare("SELECT * FROM Notifications WHERE IsActive = 1 AND (RecipientUserId = 0 OR RecipientUserId = ?) ORDER BY CreatedAt DESC LIMIT ?");
                $uid = intval($currentUserId);
                $stmt->bind_param('ii', $uid, $limit);
            }
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function countActive($currentUserId = null, $admin = false) {
        $conn = $this->getConnection();
        if ($admin) {
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM Notifications WHERE IsActive = 1");
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            if ($currentUserId === null) {
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM Notifications WHERE IsActive = 1 AND RecipientUserId = 0");
                $stmt->execute();
                $res = $stmt->get_result();
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM Notifications WHERE IsActive = 1 AND (RecipientUserId = 0 OR RecipientUserId = ?)");
                $uid = intval($currentUserId);
                $stmt->bind_param('i', $uid);
                $stmt->execute();
                $res = $stmt->get_result();
            }
        }
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? intval($row['cnt']) : 0;
    }
}

?>
