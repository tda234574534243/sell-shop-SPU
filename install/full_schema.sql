-- Full SQL schema + data for Sell-Shop-SPU
CREATE DATABASE IF NOT EXISTS `salespage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `salespage`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `LS_Mua`;
DROP TABLE IF EXISTS `HoaDon`;
DROP TABLE IF EXISTS `Cart`;
DROP TABLE IF EXISTS `Vouchers`;
DROP TABLE IF EXISTS `Products`;
DROP TABLE IF EXISTS `Account`;

-- Account table (MaLV kept for compatibility)
CREATE TABLE IF NOT EXISTS `Account` (
  `LevelID` INT(1) NOT NULL DEFAULT 0,
  `MaTK` INT(6) ZEROFILL UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `TenTK` VARCHAR(100) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `Email` VARCHAR(100) UNIQUE NOT NULL,
  `SDT` VARCHAR(10) UNIQUE NOT NULL,
  `DiaChi` VARCHAR(100) NOT NULL,
  `MaLV` INT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table (add Sold column used by application)
CREATE TABLE IF NOT EXISTS `Products` (
  `MaSP` VARCHAR(6) PRIMARY KEY,
  `TenSP` VARCHAR(50) UNIQUE NOT NULL,
  `NSX` VARCHAR(15) NOT NULL,
  `PhanLoai` VARCHAR(100) NOT NULL,
  `SoLuong` INT NOT NULL,
  `GiaTien` FLOAT NOT NULL,
  `MoTa` VARCHAR(100) NOT NULL,
  `BaoHanh` VARCHAR(100) NOT NULL,
  `ImageSP` VARCHAR(100) NOT NULL,
  `TagName` VARCHAR(100) NOT NULL,
  `MaTK` INT(6) ZEROFILL,
  `Sold` INT NOT NULL DEFAULT 0,
  CONSTRAINT P_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE IF NOT EXISTS `Cart` (
  `MaTK` INT(6) ZEROFILL NOT NULL,
  `MaSP` VARCHAR(6) NOT NULL,
  `SoLuong` INT NOT NULL,
  `GiaTien` FLOAT NOT NULL,
  `State` VARCHAR(50) NOT NULL,
  `NgayMua` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (MaTK, MaSP),
  CONSTRAINT C_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE,
  CONSTRAINT C_MaSP_FK FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- HoaDon (invoices)
CREATE TABLE IF NOT EXISTS `HoaDon` (
  `MaHD` INT(6) ZEROFILL AUTO_INCREMENT PRIMARY KEY,
  `MaTK` INT(6) ZEROFILL NOT NULL,
  `SoTien` FLOAT NOT NULL,
  `NgayThanhToan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT HD_MaTK_FK FOREIGN KEY (MaTK) REFERENCES Account(MaTK) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- LS_Mua (order items)
CREATE TABLE IF NOT EXISTS `LS_Mua` (
  `MaHD` INT(6) ZEROFILL NOT NULL,
  `MaTK` INT(6) ZEROFILL NOT NULL,
  `MaSP` VARCHAR(6) NOT NULL,
  `TenSP` VARCHAR(100) NOT NULL,
  `SoLuong` INT NOT NULL,
  `GiaTien` FLOAT NOT NULL,
  `State` VARCHAR(50) NOT NULL,
  `NgayMua` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (MaHD, MaSP),
  CONSTRAINT LS_MaHD_FK FOREIGN KEY (MaHD) REFERENCES HoaDon(MaHD) ON DELETE CASCADE,
  CONSTRAINT LS_MaSP_FK FOREIGN KEY (MaSP) REFERENCES Products(MaSP) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vouchers (structure used by model/m_voucher.php)
CREATE TABLE IF NOT EXISTS `Vouchers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `Code` VARCHAR(100) NOT NULL,
  `Description` TEXT,
  `DiscountPercent` INT DEFAULT NULL,
  `DiscountAmount` DECIMAL(10,2) DEFAULT NULL,
  `ValidFrom` DATE DEFAULT NULL,
  `ValidTo` DATE DEFAULT NULL,
  `Quantity` INT DEFAULT NULL,
  `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- Safety: ensure compatibility with existing installs
ALTER TABLE `Account` ADD COLUMN IF NOT EXISTS `MaLV` INT(1) NOT NULL DEFAULT 0;
ALTER TABLE `Products` ADD COLUMN IF NOT EXISTS `Sold` INT NOT NULL DEFAULT 0;

-- Insert data
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (1, 'Admin', '$2y$10$ALfU4BF6WTkY.Tt1RZ6ZzOyLO1CXUDhMN1sni3.cxY89EgwceJqCm', 'admin@web.com', '0000000000', 'System', 1);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Trương Anh Kiệt', '$2y$10$OxaDV6rsi6ZOPcwB22hVF.swHrtgRRIIyABD/PpercOrUbO39XBwi', 'anhkiet@gmail.com', '0987654321', 'TP Hồ Chí Minh', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Nguyễn Văn Hùng', '$2y$10$QoZufmPkDMYerGgiW8bUruaXRBwu9Y3CLU0i71g9PCk3OTnJunykO', 'hungnv@gmail.com', '0912345678', 'Hà Nội', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Lê Thị Minh', '$2y$10$d.j/pzUxSS/ZJnnrMkekQuY0fJdL/.t7.w5tMQaVss53ieWphAJwO', 'minhl@gmail.com', '0909876543', 'Đà Nẵng', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Phạm Tuấn Kiệt', '$2y$10$2oF4WlvKtE7xcXgpC5cLKu.yK1vobPjzV/1G5nikxS4MpLxawBwni', 'ptkiet@gmail.com', '0977554433', 'Cần Thơ', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Trần Bảo Ngọc', '$2y$10$qhMrK8Yrmvxm2NuKiKj6Le/JWMT6TwzAW9EG3P0pZhhwyXWLKjp3e', 'tbn123@gmail.com', '0933445566', 'Nha Trang', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Vũ Hoàng Long', '$2y$10$Mv686VSMHcuXGROEHVI/huVg6xZOzINM2o8Z2GfFD16TlQQh5pkOy', 'longvh@gmail.com', '0922334455', 'Hải Phòng', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Đặng Nhật Minh', '$2y$10$fQ0EKpZifIv8FbLOY1OqJuG8Di1HV2TISac0vW6WiwUoyQunvRXwa', 'dnminh@gmail.com', '0944556677', 'Bình Dương', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Ngô Thị Kim Liên', '$2y$10$MQxVkWLV.BJO6HwwHVUelufOFBhnMS3dU.frDRJTzGzI.kLcvGW8q', 'ngokl@gmail.com', '0966887788', 'Huế', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Huỳnh Văn Nam', '$2y$10$.lgrmMmaGNxmJs/qZ6MfkOMCxRrqw9Z9CXBYKbWj7VOF1Qns/xkbm', 'namhv@gmail.com', '0955667788', 'Đồng Nai', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Bùi Thị Thanh', '$2y$10$/wVOjGcZxbcddnEP3GGPb.Qskxd5jVTUgYZwAWi6OCklCNLeT7VoS', 'btthanh@gmail.com', '0911223344', 'Quảng Nam', 0);
INSERT INTO `Account` (LevelID, TenTK, Password, Email, SDT, DiaChi, MaLV) VALUES (0, 'Đỗ Minh Khoa', '$2y$10$TFTU1peQKEvyOtHTbA0ElO5U8nxXJMkXCCFpvN17B.zGtYuczfHgW', 'dmkhoa@gmail.com', '0933221100', 'Bạc Liêu', 0);

SET FOREIGN_KEY_CHECKS=0;
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0001', 'ASUS Vivobook 15 X1504ZA-NJ517W', '10/05/2025', 'Laptop', 1000, 13790000, 'I5-1235U/16GB/512GB PCIE/15.6 FHD/WIN11/BẠC', '12 Tháng', './media/image/Product_img/SP0001.webp', 'Laptop Asus, Asus, Văn Phòng, Gaming', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0002', 'ASUS Gaming VivoBook K3605ZC-RP564W', '10/05/2025', 'Laptop', 1000, 17990000, '5-12500H/16GB/512GB PCIE/VGA 4GB RTX3050/16.0', '12 Tháng', './media/image/Product_img/SP0002.webp', 'Laptop Gaming, Win11, Laptop Văn Phòng, OLED', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0003', 'ASUS Vivobook 15 X1502VA-BQ886W', '10/05/2025', 'Laptop', 1000, 16790000, 'I7-13620H/16GB/512GB PCIE/15.6 FHD/WIN11/BẠC', '12 Tháng', './media/image/Product_img/SP0003.webp', 'Asus, Laptop Gaming, RTX 3050, Win11', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0004', 'ASUS Vivobook 14 OLED A1405VA-KM257W', '10/05/2025', 'Laptop', 1000, 16990000, 'I5-13500H/16GB/512GB PCIE/14.0 2.8K OLED/WIN11/ĐEN', '18 Tháng', './media/image/Product_img/SP0004.webp', 'RTX 3050, Laptop Văn Phòng, Laptop Gaming, Asus', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0005', 'ASUS Vivobook 14 OLED A1405ZA-KM264W', '10/05/2025', 'Laptop', 1000, 15490000, 'I5-12500H/16GB/512GB PCEI/14.0 2.8K OLED/WIN11/BẠC', '12 Tháng', './media/image/Product_img/SP0005.webp', 'Win11, OLED, Core i5, RTX 3050', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0006', 'ASUS Vivobook 15 X1504VA-NJ070W', '10/05/2025', 'Laptop', 1000, 13990000, 'I5-1335U/16GB/512GB PCIE/15.6 FHD/WIN11/XANH', '6 Tháng', './media/image/Product_img/SP0006.webp', 'Win11, Core i5, 15.6 inch, Asus', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0007', 'ASUS Vivobook 16 X1605VA-MB1795W', '10/05/2025', 'Laptop', 1000, 16890000, 'I7-13620H/16GB/512GB PCIE/16.0 WUXGA/WIN11BẠC', '12 Tháng', './media/image/Product_img/SP0007.webp', 'Core i5, SSD 512GB, Laptop Văn Phòng, Asus', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0008', 'ASUS Vivobook Go 14 E1404FA-NK177W', '10/05/2025', 'Laptop', 1000, 11890000, 'R5-7520U/16GB/512GB PCIE/14.0 FHD/WIN11/BẠC', '12 Tháng', './media/image/Product_img/SP0008.webp', 'SSD 512GB, Core i5, RTX 3050, Asus', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0009', 'ASUS Vivobook Go 15 E1504FA-NJ454W', '10/05/2025', 'Laptop', 1000, 12290000, 'R5-7520U/16GB/512GB PCIE/15.6 FHD/WIN11/BẠC', '18 Tháng', './media/image/Product_img/SP0009.webp', 'Core i5, Asus, RTX 3050, Win11', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0010', 'Laptop ASUS VivoBook S 16 OLED S5606MA-MX051W', '10/05/2025', 'Laptop', 1000, 24990000, 'U7-155H/16GB/512GB PCIE/16.0 3.2K OLED/WIN11/XANH', '18 Tháng', './media/image/Product_img/SP0010.webp', 'Laptop Gaming, OLED, Laptop Văn Phòng, Core i5', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0011', 'Samsung Galaxy A06 5G 4GB 128GB', '10/05/2025', 'Điện thoại', 1000, 3490000, 'Màn hình 6.5\" HD+, chip MediaTek Helio G35, pin 5000mAh, camera chính 50MP', '12 Tháng', './media/image/Product_img/SP0011.webp', 'Điện Thoại Cao Cấp, Samsung, 5G, Camera 50MP', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0012', 'Samsung Galaxy A26 5G 8GB 128GB', '10/05/2025', 'Điện thoại', 1000, 6690000, 'Màn hình 6.5\" FHD+ 120Hz, chip Exynos 1280, pin 5000mAh, camera chính 50MP', '6 Tháng', './media/image/Product_img/SP0012.webp', 'Pin khủng, Snapdragon, Điện Thoại Cao Cấp, Điện Thoại Giá Rẻ', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0013', 'Samsung Galaxy A36 5G 8GB 128GB', '10/05/2025', 'Điện thoại', 1000, 7790000, 'Màn hình 6.6\" FHD+ 120Hz, chip Exynos 1380, pin 5000mAh, camera chính 50MP', '24 Tháng', './media/image/Product_img/SP0013.webp', '5G, Màn hình lớn, Samsung, Exynos', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0014', 'Samsung Galaxy A56 5G 8GB 128GB', '10/05/2025', 'Điện thoại', 1000, 9490000, 'Màn hình 6.7\" FHD+ 120Hz, chip Exynos 1480, pin 5000mAh, camera chính 50MP', '18 Tháng', './media/image/Product_img/SP0014.webp', 'Màn hình lớn, Exynos, AMOLED, Camera 50MP', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0015', 'Samsung Galaxy S25 5G 12GB 256GB', '10/05/2025', 'Điện thoại', 1000, 19690000, 'Màn hình 6.1\" Dynamic AMOLED 2X, chip Snapdragon 8 Gen 2, pin 3900mAh, camera chính 50MP', '6 Tháng', './media/image/Product_img/SP0015.webp', 'Samsung, Exynos, Pin khủng, AMOLED', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0016', 'Samsung Galaxy S25 Plus 256GB', '10/05/2025', 'Điện thoại', 1000, 21990000, 'Màn hình 6.6\" Dynamic AMOLED 2X, chip Snapdragon 8 Gen 2, pin 4700mAh, camera chính 50MP', '6 Tháng', './media/image/Product_img/SP0016.webp', 'Pin khủng, AMOLED, Exynos, Màn hình lớn', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0017', 'Samsung Galaxy S25 Ultra 12GB 512GB', '10/05/2025', 'Điện thoại', 1000, 30890000, 'Màn hình 6.8\" Dynamic AMOLED 2X, chip Snapdragon 8 Gen 2, pin 5000mAh, camera chính 200MP', '24 Tháng', './media/image/Product_img/SP0017.webp', 'Samsung, Điện Thoại Giá Rẻ, Điện Thoại Cao Cấp, Exynos', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0018', 'Samsung Galaxy Z Flip6 12GB 256GB', '10/05/2025', 'Điện thoại', 1000, 19990000, 'Màn hình gập 6.7\" Dynamic AMOLED 2X, chip Snapdragon 8 Gen 2, pin 3700mAh, camera chính 12MP', '12 Tháng', './media/image/Product_img/SP0018.webp', 'Camera 50MP, Điện Thoại Giá Rẻ, Samsung, Màn hình lớn', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0019', 'Samsung Galaxy Z Fold6 12GB 256GB', '10/05/2025', 'Điện thoại', 1000, 36190000, 'Màn hình gập 7.6\" Dynamic AMOLED 2X, chip Snapdragon 8 Gen 2, pin 4400mAh, camera chính 50MP', '24 Tháng', './media/image/Product_img/SP0019.webp', 'Exynos, 5G, Điện Thoại Giá Rẻ, Màn hình lớn', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0020', 'Smart Tivi Samsung QLED 4K 55 inch 2024 (55Q60D)', '10/05/2025', 'Tivi', 1000, 13490000, 'Màn hình QLED 4K 55 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '12 Tháng', './media/image/Product_img/SP0020.webp', 'Tivi Samsung, QLED, Smart Tivi, Tivi 65 inch', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0021', 'Smart Tivi Samsung UHD 4K 55 inch 2024 (55DU8000)', '10/05/2025', 'Tivi', 1000, 12990000, 'Màn hình UHD 4K 55 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '24 Tháng', './media/image/Product_img/SP0021.webp', 'QLED, Tivi 4K, HDR, Tần số quét cao', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0022', 'Smart Tivi Samsung UHD 4K 43 inch 2024 (43DU7700)', '10/05/2025', 'Tivi', 1000, 6980000, 'Màn hình UHD 4K 43 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '24 Tháng', './media/image/Product_img/SP0022.webp', 'Tivi 55 inch, Tizen, Tivi Samsung, UHD', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0023', 'Smart Tivi Samsung UHD 4K 65 inch 2024 (65DU8000)', '10/05/2025', 'Tivi', 1000, 13990000, 'Màn hình UHD 4K 65 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '18 Tháng', './media/image/Product_img/SP0023.webp', 'Smart Tivi, UHD, QLED, Tivi Samsung', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0024', 'Smart Tivi Samsung QLED 4K 55 inch 2024 (55Q70D)', '10/05/2025', 'Tivi', 1000, 14990000, 'Màn hình QLED 4K 55 inch, tần số quét 120Hz, hỗ trợ HDR, hệ điều hành Tizen', '6 Tháng', './media/image/Product_img/SP0024.webp', 'Tần số quét cao, Smart Tivi, UHD, Tizen', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0025', 'Smart Tivi Samsung UHD 4K 50 inch 2024 (50DU8000)', '10/05/2025', 'Tivi', 1000, 9590000, 'Màn hình UHD 4K 50 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '12 Tháng', './media/image/Product_img/SP0025.webp', 'Tivi 55 inch, Tizen, Tần số quét cao, UHD', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0026', 'Smart Tivi Samsung QLED 4K 65 inch 2024 (65Q70D)', '10/05/2025', 'Tivi', 1000, 17390000, 'Màn hình QLED 4K 65 inch, tần số quét 120Hz, hỗ trợ HDR, hệ điều hành Tizen', '18 Tháng', './media/image/Product_img/SP0026.webp', 'Tần số quét cao, Tivi 65 inch, Tivi Samsung, UHD', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0027', 'Smart Tivi Samsung The Serif QLED 4K 65 inch 2024 (65LS01D)', '10/05/2025', 'Tivi', 1000, 22890000, 'Màn hình QLED 4K 65 inch, thiết kế The Serif độc đáo, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '24 Tháng', './media/image/Product_img/SP0027.webp', 'Smart Tivi, Tivi 55 inch, Tivi 4K, HDR', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0028', 'Smart Tivi Samsung QLED 4K 75 inch 2024 (75Q60D)', '10/05/2025', 'Tivi', 1000, 22890000, 'Màn hình QLED 4K 75 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '6 Tháng', './media/image/Product_img/SP0028.webp', 'Smart Tivi, Tivi 55 inch, Tizen, HDR', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0029', 'Smart Tivi Samsung UHD 4K 65 inch 2024 (65DU7700)', '10/05/2025', 'Tivi', 1000, 11890000, 'Màn hình UHD 4K 65 inch, tần số quét 60Hz, hỗ trợ HDR, hệ điều hành Tizen', '18 Tháng', './media/image/Product_img/SP0029.webp', 'Tivi 55 inch, HDR, QLED, Tần số quét cao', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0030', 'PC CPS Đồ Hoạ D02 i5 12400F / 16GB - 500GB / RTX 3060', '10/05/2025', 'Máy tính để bàn', 1000, 17890000, 'CPU Intel Core i5-12400F, RAM 16GB, SSD 500GB, VGA NVIDIA RTX 3060', '24 Tháng', './media/image/Product_img/SP0030.webp', 'RTX 3060, i7, Có màn hình, PC Gaming', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0031', 'PC CPS Gaming G06 i5 12400F / 16GB - 256GB / RTX 3060', '10/05/2025', 'Máy tính để bàn', 1000, 17090000, 'CPU Intel Core i5-12400F, RAM 16GB, SSD 256GB, VGA NVIDIA RTX 3060', '12 Tháng', './media/image/Product_img/SP0031.webp', 'Máy tính bàn, PC Gaming, RTX 3060, PC Văn Phòng', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0032', 'PC CPS X ASUS Gaming Intel i3 Gen 12 Kèm màn hình', '10/05/2025', 'Máy tính để bàn', 1000, 12290000, 'CPU Intel Core i3 Gen 12, RAM 8GB, SSD 256GB, VGA NVIDIA GTX 1650, kèm màn hình', '24 Tháng', './media/image/Product_img/SP0032.webp', 'Máy tính bàn, PC Gaming, Ryzen 3, GTX 1650', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0033', 'PC CPS X ASUS Gaming Intel i5 Gen 14 Kèm màn hình', '10/05/2025', 'Máy tính để bàn', 1000, 19990000, 'CPU Intel Core i5 Gen 14, RAM 16GB, SSD 512GB, VGA NVIDIA RTX 3050, kèm màn hình', '6 Tháng', './media/image/Product_img/SP0033.webp', 'PC Văn Phòng, RTX 3060, Máy tính bàn, GTX 1650', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0034', 'PC CPS Văn Phòng AMD R3 3200G Kèm Màn Hình', '10/05/2025', 'Máy tính để bàn', 1000, 6690000, 'CPU AMD Ryzen 3 3200G, RAM 8GB, SSD 256GB, VGA tích hợp, kèm màn hình', '18 Tháng', './media/image/Product_img/SP0034.webp', 'i5, RTX 3060, GTX 1650, PC Văn Phòng', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0035', 'PC CPS Văn Phòng AMD R5 4600G Kèm màn hình', '10/05/2025', 'Máy tính để bàn', 1000, 7790000, 'CPU AMD Ryzen 5 4600G, RAM 8GB, SSD 256GB, VGA tích hợp, kèm màn hình', '24 Tháng', './media/image/Product_img/SP0035.webp', 'PC Văn Phòng, GTX 1650, Ryzen 5, i5', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0036', 'PC CPS Văn Phòng AMD R5 5600GT Kèm Màn Hình', '10/05/2025', 'Máy tính để bàn', 1000, 8590000, 'CPU AMD Ryzen 5 5600GT, RAM 8GB, SSD 256GB, VGA tích hợp, kèm màn hình', '18 Tháng', './media/image/Product_img/SP0036.webp', 'Máy tính bàn, PC Gaming, Ryzen 5, RTX 3060', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0037', 'PC CPS X MSI Gaming Intel i3 Gen 12 Kèm màn hình', '10/05/2025', 'Máy tính để bàn', 1000, 13690000, 'CPU Intel Core i3 Gen 12, RAM 8GB, SSD 256GB, VGA NVIDIA GTX 1650, kèm màn hình', '12 Tháng', './media/image/Product_img/SP0037.webp', 'Máy tính bàn, RTX 3060, i7, Ryzen 3', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0038', 'PC CPS X MSI Gaming AMD R5 Kèm màn hình', '10/05/2025', 'Máy tính để bàn', 1000, 14890000, 'CPU AMD Ryzen 5, RAM 16GB, SSD 512GB, VGA NVIDIA GTX 1650, kèm màn hình', '24 Tháng', './media/image/Product_img/SP0038.webp', 'Có màn hình, i5, Máy tính bàn, GTX 1650', 1, 0);
INSERT INTO `Products` (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK, Sold) VALUES ('SP0039', 'PC CPS Quantum Blaze 5070 Ti', '10/05/2025', 'Máy tính để bàn', 1000, 69990000, 'CPU Intel Core i7, RAM 32GB, SSD 1TB, VGA NVIDIA RTX 4070 Ti', '12 Tháng', './media/image/Product_img/SP0039.webp', 'RTX 3060, GTX 1650, Ryzen 3, i5', 1, 0);
INSERT INTO `Cart` (MaTK, MaSP, SoLuong, GiaTien, State) VALUES (1, 'SP0001', 2, 500000, 'Đang giao');
INSERT INTO `Cart` (MaTK, MaSP, SoLuong, GiaTien, State) VALUES (2, 'SP0002', 1, 750000, 'Chờ lấy hàng');
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (1, 3, 200000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (2, 4, 350000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (3, 5, 150000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (4, 6, 450000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (5, 7, 500000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (6, 8, 600000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (7, 1, 850000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (8, 2, 900000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (9, 3, 700000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (10, 4, 300000);
INSERT INTO `HoaDon` (MaHD, MaTK, SoTien) VALUES (11, 5, 400000);
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (1, 1, 'SP0001', 'ASUS Vivobook 15 X1504ZA-NJ517W', 2, 'Đã giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (2, 2, 'SP0002', 'ASUS Gaming VivoBook K3605ZC-RP564W', 1, 'Đang giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (3, 3, 'SP0003', 'ASUS Vivobook 15 X1502VA-BQ886W', 3, 'Đã giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (4, 1, 'SP0004', 'ASUS Vivobook 14 OLED A1405VA-KM257W', 1, 'Chờ lấy hàng');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (5, 2, 'SP0001', 'ASUS Vivobook 15 X1504ZA-NJ517W', 4, 'Đã giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (6, 3, 'SP0005', 'ASUS Vivobook 14 OLED A1405ZA-KM264W', 2, 'Đang giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (7, 2, 'SP0002', 'ASUS Gaming VivoBook K3605ZC-RP564W', 2, 'Đã hủy');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (8, 1, 'SP0003', 'ASUS Vivobook 15 X1502VA-BQ886W', 1, 'Đã giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (9, 3, 'SP0006', 'ASUS Vivobook 15 X1504VA-NJ070W', 2, 'Chờ lấy hàng');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (10, 2, 'SP0004', 'ASUS Vivobook 14 OLED A1405VA-KM257W', 3, 'Đã giao');
INSERT INTO `LS_Mua` (MaHD, MaTK, MaSP, TenSP, SoLuong, State) VALUES (11, 1, 'SP0002', 'ASUS Gaming VivoBook K3605ZC-RP564W', 1, 'Đang giao');
INSERT INTO `Vouchers` (Code, Description, DiscountAmount) VALUES ('VCNamMoi', '', 50);
INSERT INTO `Vouchers` (Code, Description, DiscountAmount) VALUES ('VCMungTet', '', 60);
INSERT INTO `Vouchers` (Code, Description, DiscountAmount) VALUES ('VC30T4', '', 20);
SET FOREIGN_KEY_CHECKS=1;
