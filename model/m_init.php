<?php 
require_once("m_database.php");

class M_init extends M_database {   

    public function Create_Structure() {

        // ACCOUNT
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS Account (
                LevelID INT(1) NOT NULL DEFAULT 0,
                MaTK INT(6) ZEROFILL UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                TenTK VARCHAR(100) NOT NULL,
                Password VARCHAR(255) NOT NULL,
                Email VARCHAR(100) UNIQUE NOT NULL,
                SDT VARCHAR(10) UNIQUE NOT NULL,
                DiaChi VARCHAR(100) NOT NULL,
                MaLV INT(1) NOT NULL DEFAULT 0
            )
        ");
        $this->excuteQuery();

        // PRODUCTS
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS Products (
                MaSP VARCHAR(6) PRIMARY KEY,
                TenSP VARCHAR(50) UNIQUE NOT NULL,
                NSX VARCHAR(15) NOT NULL,
                PhanLoai VARCHAR(100) NOT NULL,
                SoLuong INT NOT NULL,
                GiaTien FLOAT NOT NULL,
                MoTa VARCHAR(100) NOT NULL,
                BaoHanh VARCHAR(100) NOT NULL,
                ImageSP VARCHAR(100) NOT NULL,
                TagName VARCHAR(100) NOT NULL,
                MaTK INT(6) ZEROFILL,
                Sold INT NOT NULL DEFAULT 0,
                FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE
            )
        ");
        $this->excuteQuery();

        // CART
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS Cart (
                MaTK INT(6) ZEROFILL NOT NULL,
                MaSP VARCHAR(6) NOT NULL,
                SoLuong INT NOT NULL,
                GiaTien FLOAT NOT NULL,
                State VARCHAR(50) NOT NULL,
                NgayMua TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (MaTK, MaSP),
                FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE,
                FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE
            )
        ");
        $this->excuteQuery();

        // HOADON
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS HoaDon (
                MaHD INT(6) ZEROFILL AUTO_INCREMENT PRIMARY KEY,
                MaTK INT(6) ZEROFILL NOT NULL,
                SoTien FLOAT NOT NULL,
                NgayThanhToan TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE
            )
        ");
        $this->excuteQuery();

        // LS_MUA (FIX THIẾU CỘT)
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS LS_Mua (
                MaHD INT(6) ZEROFILL NOT NULL,
                MaTK INT(6) ZEROFILL NOT NULL,
                MaSP VARCHAR(6) NOT NULL,
                TenSP VARCHAR(100) NOT NULL,
                SoLuong INT NOT NULL,
                GiaTien FLOAT NOT NULL,
                State VARCHAR(50) NOT NULL,
                NgayMua TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (MaHD, MaSP),
                FOREIGN KEY (MaHD) REFERENCES HoaDon(MaHD) ON DELETE CASCADE,
                FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE
            )
        ");
        $this->excuteQuery();

        // VOUCHERS (FIX STRUCTURE)
        $this->setQuery("
            CREATE TABLE IF NOT EXISTS Vouchers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                Code VARCHAR(100) NOT NULL,
                Description TEXT,
                DiscountPercent INT DEFAULT NULL,
                DiscountAmount DECIMAL(10,2) DEFAULT NULL,
                ValidFrom DATE DEFAULT NULL,
                ValidTo DATE DEFAULT NULL,
                Quantity INT DEFAULT NULL,
                CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $this->excuteQuery();
    }

    public function Insert_Data() {

        // ===== ACCOUNT =====
        $accounts = json_decode(file_get_contents('../public/Data/accounts.json'), true);
        if ($accounts) {
            foreach ($accounts as $acc) {

                $sql = "INSERT IGNORE INTO Account 
                (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV)
                VALUES (
                    {$acc['LevelID']},
                    '".addslashes($acc['TenTK'])."',
                    '".password_hash($acc['Password'], PASSWORD_DEFAULT)."',
                    '{$acc['Email']}',
                    '{$acc['SDT']}',
                    '".addslashes($acc['DiaChi'])."',
                    0
                )";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }

        // ===== PRODUCTS =====
        $products = json_decode(file_get_contents('../public/Data/products.json'), true);
        if ($products) {
            foreach ($products as $p) {

                $sql = "INSERT IGNORE INTO Products
                (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold)
                VALUES (
                    '{$p['MaSP']}',
                    '".addslashes($p['TenSP'])."',
                    '{$p['NSX']}',
                    '".addslashes($p['PhanLoai'])."',
                    {$p['SoLuong']},
                    {$p['GiaTien']},
                    '".addslashes($p['MoTa'])."',
                    '".addslashes($p['BaoHanh'])."',
                    '".addslashes($p['ImageSP'])."',
                    '".addslashes($p['TagName'])."',
                    '{$p['MaTK']}',
                    0
                )";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }

        // ===== CART =====
        $carts = json_decode(file_get_contents('../public/Data/carts.json'), true);
        if ($carts) {
            foreach ($carts as $c) {

                $sql = "INSERT IGNORE INTO Cart
                (MaTK, MaSP, SoLuong, GiaTien, State)
                VALUES (
                    '{$c['MaTK']}',
                    '{$c['MaSP']}',
                    {$c['SoLuong']},
                    {$c['GiaTien']},
                    '{$c['State']}'
                )";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }

        // ===== HOADON =====
        $hoadons = json_decode(file_get_contents('../public/Data/hoadon.json'), true);
        if ($hoadons) {
            foreach ($hoadons as $h) {

                $sql = "INSERT IGNORE INTO HoaDon (MaHD, MaTK, SoTien)
                        VALUES ('{$h['MaHD']}', '{$h['MaTK']}', {$h['SoTien']})";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }

        // ===== LS_MUA (FIX THIẾU FIELD) =====
        $ls = json_decode(file_get_contents('../public/Data/ls_mua.json'), true);
        if ($ls) {
            foreach ($ls as $l) {

                $sql = "INSERT IGNORE INTO LS_Mua
                (MaHD, MaTK, MaSP, TenSP, SoLuong, GiaTien, State)
                VALUES (
                    '{$l['MaHD']}',
                    '{$l['MaTK']}',
                    '{$l['MaSP']}',
                    '".addslashes($l['TenSP'] ?? 'Unknown')."',
                    {$l['SoLuong']},
                    ".($l['GiaTien'] ?? 0).",
                    '{$l['State']}'
                )";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }

        // ===== VOUCHERS (NEW STRUCTURE) =====
        $vouchers = json_decode(file_get_contents('../public/Data/vouchers.json'), true);
        if ($vouchers) {
            foreach ($vouchers as $v) {

                $sql = "INSERT INTO Vouchers
                (Code, Description, DiscountAmount)
                VALUES (
                    '{$v['MaV']}',
                    '',
                    {$v['Discount']}
                )";

                $this->setQuery($sql);
                $this->excuteQuery();
            }
        }
    }
}

// RUN
$init = new M_init();
$init->Create_Structure();
$init->Insert_Data();

echo "✅ Database initialized successfully!";
?>