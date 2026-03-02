<?php
    include_once("m_database.php");
    $db = new M_database();
    class M_lsMua extends M_database
    {
        public function addLSMua($maHD, $maTK, $maSP, $tenSP, $soLuong, $giaTien, $state)
        {
            $this->setQuery("INSERT INTO LS_Mua (MaHD, MaTK, MaSP, TenSP, SoLuong, GiaTien, State) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("iissids", $maHD, $maTK, $maSP, $tenSP, $soLuong, $giaTien, $state);
            return $stmt->execute();
        }

        public function updateLSMua($maTK, $maSP, $soLuong)
        {
            $this->setQuery("UPDATE LS_Mua 
                            SET SoLuong = SoLuong + ? 
                            WHERE MaTK = ? AND MaSP = ?");
                            
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("iis", $soLuong, $maTK, $maSP);
            $stmt->execute();
        }


        public function getLSMuaByMaSP($maSP)
        {
            $this->setQuery("SELECT * FROM LS_Mua WHERE MaSP = '$maSP'");
            return $this->excuteQuery();
        }

        public function getLSMua($maHD)
        {
            $this->setQuery("SELECT * FROM LS_Mua WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }

        public function getAllLSMua()
        {
            $this->setQuery("SELECT * FROM LS_Mua");
            return $this->excuteQuery();
        }

        public function getLSMuaByMaTK($maTK)
        {
            $this->setQuery("SELECT * FROM LS_Mua WHERE MaTK = '$maTK'");
            return $this->excuteQuery();
        }
    }
?>