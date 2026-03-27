<?php
require_once 'm_database.php';

class M_comment extends M_database {
    public function __construct() {
        parent::__construct();
        // Ensure table exists
        $sql = "CREATE TABLE IF NOT EXISTS Comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            MaSP VARCHAR(20) NOT NULL,
            MaTK INT NOT NULL,
            Content TEXT,
            Rating TINYINT NULL,
            Hidden TINYINT(1) NOT NULL DEFAULT 0,
            CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TIMESTAMP NULL
        )";
        $this->setQuery($sql);
        $this->excuteQuery();
    }

    public function addComment($MaSP, $MaTK, $content, $rating = null) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("INSERT INTO Comments (MaSP, MaTK, Content, Rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sisi', $MaSP, $MaTK, $content, $rating);
        return $stmt->execute();
    }

    public function getCommentsByProduct($MaSP) {
        $conn = $this->getConnection();
        // include user's display name and avatar for UI
        $stmt = $conn->prepare("SELECT c.*, a.TenTK, a.Avatar FROM Comments c JOIN Account a ON a.MaTK = c.MaTK WHERE c.MaSP = ? ORDER BY c.CreatedAt DESC");
        $stmt->bind_param('s', $MaSP);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getCommentById($id) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Comments WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function getAverageRating($MaSP) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT AVG(Rating) as avgR, COUNT(Rating) as cnt FROM Comments WHERE MaSP = ? AND Rating IS NOT NULL");
        $stmt->bind_param('s', $MaSP);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return ['avg' => round($row['avgR'] ?? 0,1), 'count' => intval($row['cnt'])];
    }

    public function getUserRating($MaSP, $MaTK) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT Rating FROM Comments WHERE MaSP = ? AND MaTK = ? ORDER BY CreatedAt DESC LIMIT 1");
        $stmt->bind_param('si', $MaSP, $MaTK);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) return $row['Rating'];
        return null;
    }

    public function editComment($id, $MaTK, $content, $rating = null, $isAdmin = false) {
        $conn = $this->getConnection();
        if (!$isAdmin) {
            $stmt = $conn->prepare("UPDATE Comments SET Content = ?, Rating = ?, UpdatedAt = CURRENT_TIMESTAMP WHERE id = ? AND MaTK = ?");
            $stmt->bind_param('siii', $content, $rating, $id, $MaTK);
        } else {
            $stmt = $conn->prepare("UPDATE Comments SET Content = ?, Rating = ?, UpdatedAt = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param('sii', $content, $rating, $id);
        }
        return $stmt->execute();
    }

    public function deleteComment($id, $MaTK = null, $isAdmin = false) {
        $conn = $this->getConnection();
        if ($isAdmin) {
            $stmt = $conn->prepare("DELETE FROM Comments WHERE id = ?");
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $conn->prepare("DELETE FROM Comments WHERE id = ? AND MaTK = ?");
            $stmt->bind_param('ii', $id, $MaTK);
        }
        return $stmt->execute();
    }

    public function setHidden($id, $MaTK, $hidden = 1, $isAdmin = false) {
        $conn = $this->getConnection();
        if ($isAdmin) {
            $stmt = $conn->prepare("UPDATE Comments SET Hidden = ? WHERE id = ?");
            $stmt->bind_param('ii', $hidden, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Comments SET Hidden = ? WHERE id = ? AND MaTK = ?");
            $stmt->bind_param('iii', $hidden, $id, $MaTK);
        }
        return $stmt->execute();
    }
}

?>
