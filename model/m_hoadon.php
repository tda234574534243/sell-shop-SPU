<?php
    include_once("m_database.php");
    $db = new M_database();
    class M_hoadon extends M_database
    {
       public function thanhToan($maTK, $sotien)
       {
           $this->setQuery("INSERT INTO HoaDon (MaTK, SoTien) VALUES ('$maTK', '$sotien')");
           return $this->excuteQuery();
       }

       /**
        * Create a new invoice (HoaDon) with optional status.
        * Ensures `Status` column exists (adds it if missing) for backward compatibility.
        * Returns inserted MaHD on success, or false on error.
        */
       public function createHoaDon($maTK, $sotien, $status = 'Chờ thanh toán') {
           // ensure Status column exists (MySQL 8+ supports IF NOT EXISTS)
           $this->setQuery("ALTER TABLE HoaDon ADD COLUMN IF NOT EXISTS `Status` VARCHAR(50) DEFAULT 'Chờ thanh toán'");
           $this->excuteQuery();

           $maTK = $this->real_escape_string($maTK);
           $sotien = $this->real_escape_string($sotien);
           $status = $this->real_escape_string($status);

           $this->setQuery("INSERT INTO HoaDon (MaTK, SoTien, `Status`) VALUES ('$maTK', '$sotien', '$status')");
           $res = $this->excuteQuery();
           if ($res === false) return false;
           return $this->conn->insert_id;
       }

       /**
        * Update the Status field for an invoice.
        */
       public function updateStatus($maHD, $status) {
           $maHD = $this->real_escape_string($maHD);
           $status = $this->real_escape_string($status);
           $this->setQuery("UPDATE HoaDon SET `Status` = '$status', NgayThanhToan = NOW() WHERE MaHD = '$maHD'");
           return $this->excuteQuery();
       }

        public function getHoaDon($maHD)
        {
            $this->setQuery("SELECT * FROM HoaDon WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }

        public function getAllHoaDon()
        {
            $this->setQuery("SELECT * FROM HoaDon");
            return $this->excuteQuery();
        }

        public function getHoaDonByMaTK($maTK)
        {
            $this->setQuery("SELECT * FROM HoaDon WHERE MaTK = '$maTK'");
            return $this->excuteQuery();
        }

        public function getLastHoaDon()
        {
            $this->setQuery("SELECT * FROM HoaDon ORDER BY MaHD DESC LIMIT 1");
            return $this->excuteQuery();
        }

            /**
             * Ensure the table tracking user-hidden invoices exists.
             */
            protected function ensureUserHiddenTable()
            {
                $this->setQuery("CREATE TABLE IF NOT EXISTS User_Hidden_HoaDon (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    MaHD INT NOT NULL,
                    MaTK INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_mahd_matk (MaHD, MaTK)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->excuteQuery();
            }

            /**
             * Mark an invoice as hidden for a specific user (soft-delete for user view).
             */
            public function hideForUser($maHD, $maTK)
            {
                $this->ensureUserHiddenTable();
                $maHD = $this->real_escape_string($maHD);
                $maTK = $this->real_escape_string($maTK);
                $this->setQuery("INSERT INTO User_Hidden_HoaDon (MaHD, MaTK) VALUES ('$maHD', '$maTK') ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
                return $this->excuteQuery();
            }

            public function unhideForUser($maHD, $maTK)
            {
                $this->ensureUserHiddenTable();
                $maHD = $this->real_escape_string($maHD);
                $maTK = $this->real_escape_string($maTK);
                $this->setQuery("DELETE FROM User_Hidden_HoaDon WHERE MaHD = '$maHD' AND MaTK = '$maTK'");
                return $this->excuteQuery();
            }

            public function isHiddenForUser($maHD, $maTK)
            {
                $this->ensureUserHiddenTable();
                $maHD = $this->real_escape_string($maHD);
                $maTK = $this->real_escape_string($maTK);
                $this->setQuery("SELECT 1 FROM User_Hidden_HoaDon WHERE MaHD = '$maHD' AND MaTK = '$maTK' LIMIT 1");
                $r = $this->excuteQuery();
                return ($r && $r->num_rows > 0);
            }

            /**
             * Get invoices for a user excluding those the user hid.
             */
            public function getVisibleHoaDonByMaTK($maTK)
            {
                $this->ensureUserHiddenTable();
                $maTK = $this->real_escape_string($maTK);
                $q = "SELECT h.* FROM HoaDon h LEFT JOIN User_Hidden_HoaDon uh ON uh.MaHD = h.MaHD AND uh.MaTK = '$maTK' WHERE h.MaTK = '$maTK' AND uh.id IS NULL ORDER BY h.MaHD DESC";
                $this->setQuery($q);
                return $this->excuteQuery();
            }

        /**
         * Xóa hóa đơn theo MaHD
         */
        public function deleteHoaDon($maHD)
        {
            $this->setQuery("DELETE FROM HoaDon WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }
    }
?>