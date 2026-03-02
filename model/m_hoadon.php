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
    }
?>