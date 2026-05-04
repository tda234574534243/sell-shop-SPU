<?php
    include_once('m_database.php');
    class M_wishlist extends M_database {
        private function ensureTableExists() {
            // Create table if missing to avoid runtime errors on fresh installs
            $sql = "CREATE TABLE IF NOT EXISTS `Wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `MaTK` INT(6) ZEROFILL NOT NULL,
  `MaSP` VARCHAR(6) NOT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `MaTK_MaSP` (`MaTK`, `MaSP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            // Try to create table; ignore result
            $this->setQuery($sql);
            @$this->excuteQuery();
        }

        public function add($maTK, $maSP) {
            $this->ensureTableExists();
            $this->setQuery("INSERT IGNORE INTO Wishlist (MaTK, MaSP, CreatedAt) VALUES (?, ?, NOW())");
            $stmt = $this->conn->prepare($this->query);
            if (!$stmt) {
                // fallback to non-prepared query (safe cast)
                $this->setQuery("INSERT IGNORE INTO Wishlist (MaTK, MaSP, CreatedAt) VALUES (" . (int)$maTK . ", '" . $this->real_escape_string($maSP) . "', NOW())");
                $res = $this->excuteQuery();
                return $res ? ($this->conn->affected_rows > 0) : false;
            }
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

        public function remove($maTK, $maSP) {
            $this->ensureTableExists();
            $this->setQuery("DELETE FROM Wishlist WHERE MaTK = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            if (!$stmt) {
                $this->setQuery("DELETE FROM Wishlist WHERE MaTK = " . (int)$maTK . " AND MaSP = '" . $this->real_escape_string($maSP) . "'");
                $res = $this->excuteQuery();
                return $res ? ($this->conn->affected_rows > 0) : false;
            }
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

        public function isFavorited($maTK, $maSP) {
            $this->ensureTableExists();
            $this->setQuery("SELECT 1 FROM Wishlist WHERE MaTK = ? AND MaSP = ? LIMIT 1");
            $stmt = $this->conn->prepare($this->query);
            if (!$stmt) {
                $this->setQuery("SELECT 1 FROM Wishlist WHERE MaTK = " . (int)$maTK . " AND MaSP = '" . $this->real_escape_string($maSP) . "' LIMIT 1");
                $res = $this->excuteQuery();
                return ($res && $res->num_rows>0);
            }
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();
            $res = $stmt->get_result();
            return ($res && $res->num_rows>0);
        }

        public function getByUser($maTK) {
            $this->ensureTableExists();
            $this->setQuery("SELECT w.MaSP, p.TenSP, p.ImageSP, p.GiaTien FROM Wishlist w JOIN products p ON w.MaSP = p.MaSP WHERE w.MaTK = ? ORDER BY w.CreatedAt DESC");
            $stmt = $this->conn->prepare($this->query);
            if (!$stmt) {
                $this->setQuery("SELECT w.MaSP, p.TenSP, p.ImageSP, p.GiaTien FROM Wishlist w JOIN products p ON w.MaSP = p.MaSP WHERE w.MaTK = " . (int)$maTK . " ORDER BY w.CreatedAt DESC");
                return $this->excuteQuery();
            }
            $stmt->bind_param("i", $maTK);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function countByUser($maTK) {
            $this->ensureTableExists();
            $this->setQuery("SELECT COUNT(*) as c FROM Wishlist WHERE MaTK = ?");
            $stmt = $this->conn->prepare($this->query);
            if (!$stmt) {
                $this->setQuery("SELECT COUNT(*) as c FROM Wishlist WHERE MaTK = " . (int)$maTK);
                $res = $this->excuteQuery();
                $row = $res ? $res->fetch_assoc() : null;
                return isset($row['c']) ? (int)$row['c'] : 0;
            }
            $stmt->bind_param("i", $maTK);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            return isset($row['c']) ? (int)$row['c'] : 0;
        }
    }
?>
