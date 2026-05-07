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
         * Xóa hóa đơn theo MaHD
         */
        public function deleteHoaDon($maHD)
        {
            $this->setQuery("DELETE FROM HoaDon WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }
    }
?>