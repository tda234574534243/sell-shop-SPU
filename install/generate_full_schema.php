<?php
// generate_full_schema.php
// Reads public/DATA/*.json and writes install/full_schema.sql

$outFile = __DIR__ . '/full_schema.sql';
$dataDir = __DIR__ . '/../public/DATA';

$accounts = json_decode(file_get_contents($dataDir . '/accounts.json'), true);
$products = json_decode(file_get_contents($dataDir . '/products.json'), true);
$carts = json_decode(file_get_contents($dataDir . '/carts.json'), true);
$hoadons = json_decode(file_get_contents($dataDir . '/hoadon.json'), true);
$ls = json_decode(file_get_contents($dataDir . '/ls_mua.json'), true);
$vouchers = json_decode(file_get_contents($dataDir . '/vouchers.json'), true);

$sql = "-- Full SQL schema + data for Sell-Shop-SPU\n";
$sql .= "CREATE DATABASE IF NOT EXISTS `salespage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE `salespage`;\n\n";

// Drop tables first to allow clean re-imports (reverse FK order)
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
$sql .= "DROP TABLE IF EXISTS `LS_Mua`;\n";
$sql .= "DROP TABLE IF EXISTS `HoaDon`;\n";
$sql .= "DROP TABLE IF EXISTS `Cart`;\n";
$sql .= "DROP TABLE IF EXISTS `Vouchers`;\n";
$sql .= "DROP TABLE IF EXISTS `Products`;\n";
$sql .= "DROP TABLE IF EXISTS `Account`;\n\n";

$sql .= "-- Account table (MaLV kept for compatibility)\n";
$sql .= "CREATE TABLE IF NOT EXISTS `Account` (\n";
$sql .= "  `LevelID` INT(1) NOT NULL DEFAULT 0,\n";
$sql .= "  `MaTK` INT(6) ZEROFILL UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "  `TenTK` VARCHAR(100) NOT NULL,\n";
$sql .= "  `Password` VARCHAR(255) NOT NULL,\n";
$sql .= "  `Email` VARCHAR(100) UNIQUE NOT NULL,\n";
$sql .= "  `SDT` VARCHAR(10) UNIQUE NOT NULL,\n";
$sql .= "  `DiaChi` VARCHAR(100) NOT NULL,\n";
$sql .= "  `MaLV` INT(1) NOT NULL DEFAULT 0\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$sql .= "-- Products table (add Sold column used by application)\n";
$sql .= "CREATE TABLE IF NOT EXISTS `Products` (\n";
$sql .= "  `MaSP` VARCHAR(6) PRIMARY KEY,\n";
$sql .= "  `TenSP` VARCHAR(50) UNIQUE NOT NULL,\n";
$sql .= "  `NSX` VARCHAR(15) NOT NULL,\n";
$sql .= "  `PhanLoai` VARCHAR(100) NOT NULL,\n";
$sql .= "  `SoLuong` INT NOT NULL,\n";
$sql .= "  `GiaTien` FLOAT NOT NULL,\n";
$sql .= "  `MoTa` VARCHAR(100) NOT NULL,\n";
$sql .= "  `BaoHanh` VARCHAR(100) NOT NULL,\n";
$sql .= "  `ImageSP` VARCHAR(100) NOT NULL,\n";
$sql .= "  `TagName` VARCHAR(100) NOT NULL,\n";
$sql .= "  `MaTK` INT(6) ZEROFILL,\n";
$sql .= "  `Sold` INT NOT NULL DEFAULT 0,\n";
$sql .= "  CONSTRAINT P_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$sql .= "-- Cart table\n";
$sql .= "CREATE TABLE IF NOT EXISTS `Cart` (\n";
$sql .= "  `MaTK` INT(6) ZEROFILL NOT NULL,\n";
$sql .= "  `MaSP` VARCHAR(6) NOT NULL,\n";
$sql .= "  `SoLuong` INT NOT NULL,\n";
$sql .= "  `GiaTien` FLOAT NOT NULL,\n";
$sql .= "  `State` VARCHAR(50) NOT NULL,\n";
$sql .= "  `NgayMua` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "  PRIMARY KEY (MaTK, MaSP),\n";
$sql .= "  CONSTRAINT C_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE,\n";
$sql .= "  CONSTRAINT C_MaSP_FK FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$sql .= "-- HoaDon (invoices)\n";
$sql .= "CREATE TABLE IF NOT EXISTS `HoaDon` (\n";
$sql .= "  `MaHD` INT(6) ZEROFILL AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "  `MaTK` INT(6) ZEROFILL NOT NULL,\n";
$sql .= "  `SoTien` FLOAT NOT NULL,\n";
$sql .= "  `NgayThanhToan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "  CONSTRAINT HD_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$sql .= "-- LS_Mua (order items)\n";
$sql .= "CREATE TABLE IF NOT EXISTS `LS_Mua` (\n";
$sql .= "  `MaHD` INT(6) ZEROFILL NOT NULL,\n";
$sql .= "  `MaTK` INT(6) ZEROFILL NOT NULL,\n";
$sql .= "  `MaSP` VARCHAR(6) NOT NULL,\n";
$sql .= "  `TenSP` VARCHAR(100) NOT NULL,\n";
$sql .= "  `SoLuong` INT NOT NULL,\n";
$sql .= "  `GiaTien` FLOAT NOT NULL,\n";
$sql .= "  `State` VARCHAR(50) NOT NULL,\n";
$sql .= "  `NgayMua` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "  PRIMARY KEY (MaHD, MaSP),\n";
$sql .= "  CONSTRAINT LS_MaHD_FK FOREIGN KEY (MaHD) REFERENCES HoaDon(MaHD) ON DELETE CASCADE,\n";
$sql .= "  CONSTRAINT LS_MaSP_FK FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

$sql .= "-- Vouchers (structure used by model/m_voucher.php)\n";
$sql .= "CREATE TABLE IF NOT EXISTS `Vouchers` (\n";
$sql .= "  `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "  `Code` VARCHAR(100) NOT NULL,\n";
$sql .= "  `Description` TEXT,\n";
$sql .= "  `DiscountPercent` INT DEFAULT NULL,\n";
$sql .= "  `DiscountAmount` DECIMAL(10,2) DEFAULT NULL,\n";
$sql .= "  `ValidFrom` DATE DEFAULT NULL,\n";
$sql .= "  `ValidTo` DATE DEFAULT NULL,\n";
$sql .= "  `Quantity` INT DEFAULT NULL,\n";
$sql .= "  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";


$sql .= "SET FOREIGN_KEY_CHECKS=1;\n\n";

$sql .= "-- Safety: ensure compatibility with existing installs\n";
$sql .= "ALTER TABLE `Account` ADD COLUMN IF NOT EXISTS `MaLV` INT(1) NOT NULL DEFAULT 0;\n";
$sql .= "ALTER TABLE `Products` ADD COLUMN IF NOT EXISTS `Sold` INT NOT NULL DEFAULT 0;\n\n";

$sql .= "-- Insert data\n";

// Accounts (hash passwords and set MaLV same as LevelID)
foreach ($accounts as $acc) {
    $LevelID = (int)$acc['LevelID'];
    $TenTK = addslashes($acc['TenTK']);
    $Password = password_hash($acc['Password'], PASSWORD_DEFAULT);
    $Email = addslashes($acc['Email']);
    $SDT = addslashes($acc['SDT']);
    $DiaChi = addslashes($acc['DiaChi']);
    $MaLV = $LevelID;

    $sql .= "INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES ($LevelID, '". $TenTK ."', '". $Password ."', '". $Email ."', '". $SDT ."', '". $DiaChi ."', $MaLV);\n";
}

$sql .= "\nSET FOREIGN_KEY_CHECKS=0;\n";

// Products
foreach ($products as $p) {
    $MaSP = addslashes($p['MaSP']);
    $TenSP = addslashes($p['TenSP']);
    $NSX = addslashes($p['NSX']);
    $PhanLoai = addslashes($p['PhanLoai']);
    $SoLuong = (int)$p['SoLuong'];
    $GiaTien = (float)$p['GiaTien'];
    $MoTa = addslashes($p['MoTa']);
    $BaoHanh = addslashes($p['BaoHanh']);
    $ImageSP = addslashes($p['ImageSP']);
    $TagName = addslashes($p['TagName']);
    $MaTK = isset($p['MaTK']) ? intval($p['MaTK']) : 'NULL';
    $Sold = isset($p['Sold']) ? intval($p['Sold']) : 0;

    $sql .= "INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('".$MaSP."', '".$TenSP."', '".$NSX."', '".$PhanLoai."', $SoLuong, $GiaTien, '".$MoTa."', '".$BaoHanh."', '".$ImageSP."', '".$TagName."', ".($MaTK==='NULL'?'NULL':$MaTK).", $Sold);\n";
}

// Cart
foreach ($carts as $c) {
    $MaTK = intval($c['MaTK']);
    $MaSP = addslashes($c['MaSP']);
    $SoLuong = intval($c['SoLuong']);
    $GiaTien = (float)$c['GiaTien'];
    $State = addslashes($c['State']);

    $sql .= "INSERT INTO `Cart` (MaTK, MaSP, SoLuong, GiaTien, State) VALUES ($MaTK, '".$MaSP."', $SoLuong, $GiaTien, '".$State."');\n";
}

// HoaDon
foreach ($hoadons as $h) {
    $MaHD = intval($h['MaHD']);
    $MaTK = intval($h['MaTK']);
    $SoTien = (float)$h['SoTien'];
    $sql .= "INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES ($MaHD, $MaTK, $SoTien);\n";
}

// LS_Mua
foreach ($ls as $row) {
    $MaHD = intval($row['MaHD']);
    $MaTK = intval($row['MaTK']);
    $MaSP = addslashes($row['MaSP']);
    $TenSP = '';
    // try to find TenSP from products
    foreach ($products as $p) { if ($p['MaSP'] == $row['MaSP']) { $TenSP = addslashes($p['TenSP']); break; } }
    $SoLuong = intval($row['SoLuong']);
    $State = addslashes($row['State']);
    $sql .= "INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES ($MaHD, $MaTK, '".$MaSP."', '".$TenSP."', $SoLuong, '".$State."');\n";
}

// Vouchers: map MaV -> Code, store Discount into DiscountAmount
foreach ($vouchers as $v) {
    $Code = addslashes($v['MaV'] ?? ($v['Code'] ?? ''));
    $Discount = isset($v['Discount']) ? (float)$v['Discount'] : 0;
    // store as amount by default; admin can edit to percent if desired
    $sql .= "INSERT INTO `Vouchers` (Code, Description, DiscountAmount) VALUES ('".$Code."', '', ". $Discount .");\n";
}

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents($outFile, $sql);
echo "Wrote: $outFile\n";

?>
