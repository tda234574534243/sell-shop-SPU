<?php
    include_once("m_database.php");
    $db = new M_database();
    class M_giohang extends M_database
    {
        public function getCartItems($maTK)
        {
            $this->setQuery("SELECT c.MaSP, c.SoLuong, c.GiaTien, c.NgayMua, c.State, p.TenSP, p.ImageSP
            FROM cart c
            JOIN products p ON c.MaSP = p.MaSP
            WHERE c.MaTK = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("i", $maTK);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function getCartItem($maTK, $maSP)
        {
            $this->setQuery("SELECT * FROM Cart WHERE MaTK = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function getQuantity($maTK, $maSP)
        {
            $this->setQuery("SELECT SoLuong FROM Cart WHERE MaTK = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();
            return $stmt->get_result();
        }

        public function addToCart($maTK, $maSP, $soLuong, $giaTien, $state)
        {
            $this->setQuery("INSERT INTO Cart (MaTK, MaSP, SoLuong, GiaTien, State) VALUES (?, ?, ?, ?, ?)");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("isiss", $maTK, $maSP, $soLuong, $giaTien, $state);
            $stmt->execute();

            $this->setQuery("UPDATE Products SET SoLuong = SoLuong - ? WHERE MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $soLuong, $maSP);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

        public function updateCart($maTK, $maSP, $soLuong)
        {
            $this->setQuery("UPDATE Cart SET SoLuong = SoLuong + ? WHERE MaTK = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("iis", $soLuong, $maTK, $maSP);
            $stmt->execute();
            $this->setQuery("UPDATE Products SET SoLuong = SoLuong - ? WHERE MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $soLuong, $maSP);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

        public function removeFromCart($maTK, $maSP)
        {
            $result = $this->getQuantity($maTK, $maSP);
            $row = $result->fetch_assoc();
            $soLuong = $row['SoLuong'] ?? 0;

            $this->setQuery("DELETE FROM Cart WHERE MaTK = ? AND MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $maTK, $maSP);
            $stmt->execute();

            $this->setQuery("UPDATE Products SET SoLuong = SoLuong + ? WHERE MaSP = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("is", $soLuong, $maSP);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

        public function clearCart($maTK)
        {
            $this->setQuery("DELETE FROM Cart WHERE MaTK = ?");
            $stmt = $this->conn->prepare($this->query);
            $stmt->bind_param("i", $maTK);
            $stmt->execute();
            return $stmt->affected_rows > 0;
        }

    }
?>