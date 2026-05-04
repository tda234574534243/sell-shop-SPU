<?php
    include_once("m_database.php");
    $db = new M_database();
    class M_lsMua extends M_database
    {
        public function addLSMua($maHD, $maTK, $maSP, $tenSP, $soLuong, $giaTien, $state)
        {
            $this->setQuery("INSERT INTO LS_Mua (MaHD, MaTK, MaSP, TenSP, SoLuong, GiaTien, State) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt = $this->conn->prepare($this->query);
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            if ($stmt === false) {
                $message = date('Y-m-d H:i:s') . " | prepare failed in addLSMua: " . $this->conn->error . " | Query: " . $this->query . PHP_EOL;
                @error_log($message, 3, $logDir . '/db_errors.log');
                return false;
            }
            $bound = $stmt->bind_param("iissids", $maHD, $maTK, $maSP, $tenSP, $soLuong, $giaTien, $state);
            if ($bound === false) {
                $message = date('Y-m-d H:i:s') . " | bind_param failed in addLSMua: " . $stmt->error . PHP_EOL;
                @error_log($message, 3, $logDir . '/db_errors.log');
                return false;
            }
            $exec = $stmt->execute();
            if ($exec === false) {
                $message = date('Y-m-d H:i:s') . " | execute failed in addLSMua: " . $stmt->error . " | Params: MaHD={$maHD},MaTK={$maTK},MaSP={$maSP},SoLuong={$soLuong},GiaTien={$giaTien},State={$state}" . PHP_EOL;
                @error_log($message, 3, $logDir . '/db_errors.log');
            }
            return $exec;
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

        public function updateState($maHD, $maSP, $state)
        {
            $this->setQuery("UPDATE LS_Mua SET State = ? WHERE MaHD = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            if ($stmt === false) {
                return false;
            }
            $stmt->bind_param("sis", $state, $maHD, $maSP);
            return $stmt->execute();
        }

        public function getLSMuaByMaSP($maSP)
        {
            $this->setQuery("SELECT * FROM LS_Mua WHERE MaSP = '$maSP'");
            return $this->excuteQuery();
        }

        // Get LS_Mua row for a specific user and product
        public function getLSMuaByMaTKAndMaSP($maTK, $maSP)
        {
            $this->setQuery("SELECT * FROM LS_Mua WHERE MaTK = '$maTK' AND MaSP = '$maSP'");
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