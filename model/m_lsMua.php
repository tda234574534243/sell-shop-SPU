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

        public function updateLSMua($maTK, $maSP, $soLuong, $state = null)
        {
            if ($state === null) {
                $this->setQuery("UPDATE LS_Mua 
                                SET SoLuong = SoLuong + ? 
                                WHERE MaTK = ? AND MaSP = ?");
                $stmt = $this->conn->prepare($this->query);
                if ($stmt === false) return false;
                $stmt->bind_param("iis", $soLuong, $maTK, $maSP);
                return $stmt->execute();
            } else {
                // Preserve original NgayMua to avoid ON UPDATE CURRENT_TIMESTAMP changing purchase date
                $this->setQuery("UPDATE LS_Mua 
                                SET SoLuong = SoLuong + ?, State = ?, NgayMua = NgayMua 
                                WHERE MaTK = ? AND MaSP = ?");
                $stmt = $this->conn->prepare($this->query);
                if ($stmt === false) return false;
                // types: soLuong(int), state(string), maTK(int), maSP(string)
                $stmt->bind_param("isis", $soLuong, $state, $maTK, $maSP);
                return $stmt->execute();
            }
        }

        public function updateState($maHD, $maSP, $state)
        {
            // Preserve NgayMua when updating state so revenue month stays tied to purchase date
            $this->setQuery("UPDATE LS_Mua SET State = ?, NgayMua = NgayMua WHERE MaHD = ? AND MaSP = ?");
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

        /**
         * Xóa tất cả dòng LS_Mua theo MaHD (khi xóa hóa đơn)
         */
        public function deleteByMaHD($maHD)
        {
            $this->setQuery("DELETE FROM LS_Mua WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }

        /**
         * Update State for all LS_Mua rows belonging to a MaHD
         */
        public function updateStateByMaHD($maHD, $state)
        {
            $maHD = $this->real_escape_string($maHD);
            $state = $this->real_escape_string($state);
            $this->setQuery("UPDATE LS_Mua SET State = '$state', NgayMua = NgayMua WHERE MaHD = '$maHD'");
            return $this->excuteQuery();
        }
    }
?>