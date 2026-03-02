<?php 
    require_once("m_database.php");
    
    class M_init extends M_database {   
        public function Create_Structure() {
            $sql_account = "Create Table If Not Exists Account (
                LevelID int(1) Not Null Default 0,
                MaTK int(6) Zerofill Unsigned Auto_Increment Primary Key ,
                TenTK varchar(100) Not Null,
                Password varchar(10) Not Null,
                Email varchar(100) Unique Not Null,
                SDT varchar(10) Unique Not Null,
                DiaChi varchar(100) Not Null
            )";
            $this->setQuery($sql_account);
            $this->excuteQuery();

            $sql_product = "Create Table If Not Exists Products (
                MaSP varchar(6) Primary Key,
                TenSP varchar(50) Unique Not Null,
                NSX varchar(15) Not Null,
                PhanLoai varchar(100) Not Null,
                SoLuong int Not Null,
                GiaTien float Not Null,
                MoTa varchar(100) Not Null,
                BaoHanh varchar(100) Not Null,
                ImageSP varchar(100) Not Null,
                TagName varchar(100) Not Null,
                MaTK int(6) Zerofill,
                Constraint P_MaTK_FK Foreign Key (MaTK) References Account(MaTK) On Delete Cascade
            )";
            $this->setQuery($sql_product);
            $this->excuteQuery();

            $sql_Carts = "Create Table If Not Exists Cart (
                MaTK int(6) Zerofill Not Null,
                MaSP varchar(6) Not Null,
                SoLuong int Not Null,
                GiaTien float Not Null,
                State varchar(50) Not Null,
                NgayMua TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                Primary Key (MaTK, MaSP),
                Constraint C_MaTK_FK Foreign Key (MaTK) References Account(MaTK) On Delete cascade,
                Constraint C_MaSP_FK Foreign Key (MaSP) References Products(MaSP) On Delete cascade
                )";
            $this->setQuery($sql_Carts);
            $this->excuteQuery();

             $sql_HD = "Create Table If Not Exists HoaDon (
                MaHD int(6) Zerofill Auto_Increment Primary Key,
                MaTK int(6) Zerofill Not Null,
                SoTien float Not Null,
                NgayThanhToan TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                Constraint HD_MaTK_FK Foreign Key (MaTK) References Account(MaTK) On Delete cascade
                )";
            $this->setQuery($sql_HD);
            $this->excuteQuery();

            $sql_LS_Mua = "Create Table If Not Exists LS_Mua (
            MaHD int(6) Zerofill Not Null,
            MaTK int(6) Zerofill Not Null,
            MaSP varchar(6) Not Null,
            TenSP varchar(100) Not Null,
            SoLuong int Not Null,
            GiaTien float Not Null,
            State varchar(50) Not Null,
            NgayMua TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            primary key (MaHD, MaSP),
            Constraint LS_MaHD_FK Foreign Key (MaHD) References HoaDon(MaHD) On Delete cascade,
            Constraint LS_MaSP_FK Foreign Key (MaSP) References Products(MaSP) On Delete cascade
            )";

            $this->setQuery($sql_LS_Mua);
            $this->excuteQuery();

            $sql_Voucher = "Create Table If Not Exists Vouchers (
                MaV varchar(10) Not Null Primary Key,
                Discount float Not Null
                )";
            $this->setQuery($sql_Voucher);
            $this->excuteQuery();
        }
        public function Insert_Data() {
        // Đọc file accounts.json
        $jsonAccounts = file_get_contents('../public/Data/accounts.json');
        $accounts = json_decode($jsonAccounts, true);

        foreach ($accounts as $acc) {
            $LevelID = $acc['LevelID'];
            $TenTK = addslashes($acc['TenTK']);
            $Password = $acc['Password'];
            $Email = $acc['Email'];
            $SDT = $acc['SDT'];
            $DiaChi = addslashes($acc['DiaChi']);

            $sql = "INSERT INTO Account (LevelID, TenTK, Password, Email, SDT, DiaChi)
                    VALUES ($LevelID, '$TenTK', '$Password', '$Email', '$SDT', '$DiaChi')";
            $this->setQuery($sql);
            $this->excuteQuery();
        }

        // Đọc file products.json
        $jsonProducts = file_get_contents('../public/Data/products.json');
        $products = json_decode($jsonProducts, true);

        foreach ($products as $product) {
            $MaSP = $product['MaSP'];
            $TenSP = addslashes($product['TenSP']);
            $NSX = $product['NSX'];
            $PhanLoai = addslashes($product['PhanLoai']);
            $SoLuong = (int)$product['SoLuong'];
            $GiaTien = (float)$product['GiaTien'];
            $MoTa = addslashes($product['MoTa']);
            $BaoHanh = addslashes($product['BaoHanh']);
            $ImageSP = addslashes($product['ImageSP']);
            $TagName = addslashes($product['TagName']);
            // $Sold = (int)$product['Sold'];
            $MaTK = $product['MaTK'];

            $sql = "INSERT INTO Products (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, TagName, MaTK)
                    VALUES ('$MaSP', '$TenSP', '$NSX', '$PhanLoai', $SoLuong, $GiaTien, '$MoTa', '$BaoHanh', '$ImageSP', '$TagName', '$MaTK')";
            $this->setQuery($sql);
            $this->excuteQuery();
        }

        // Đọc file cart.json
        $jsonCart = file_get_contents('../public/Data/carts.json');
        $carts = json_decode($jsonCart, true);

        foreach ($carts as $item) {
            $MaTK = $item['MaTK'];
            $MaSP = $item['MaSP'];
            $SoLuong = $item['SoLuong'];
            $GiaTien = $item['GiaTien'];
            $State = $item['State'];

            $sql = "INSERT INTO Cart (MaTK, MaSP, SoLuong, GiaTien, State)
                    VALUES ('$MaTK', '$MaSP', $SoLuong, $GiaTien, '$State')";
            $this->setQuery($sql);
            $this->excuteQuery();
        }

        // Đọc file hoadon.json
        $jsonHD = file_get_contents('../public/Data/hoadon.json');
        $hoadons = json_decode($jsonHD, true);

        foreach ($hoadons as $hd) {
            $MaHD = $hd['MaHD'];
            $MaTK = $hd['MaTK'];
            $SoTien = $hd['SoTien'];

            $sql = "INSERT INTO HoaDon (MaHD, MaTK, SoTien)
                    VALUES ('$MaHD', '$MaTK', $SoTien)";
            $this->setQuery($sql);
            $this->excuteQuery();
        }

        // Đọc file ls_mua.json
        $jsonLS = file_get_contents('../public/Data/ls_mua.json');
        $lsmuas = json_decode($jsonLS, true);

        foreach ($lsmuas as $ls) {
            $MaHD = $ls['MaHD'];
            $MaTK = $ls['MaTK'];
            $MaSP = $ls['MaSP'];
            $SoLuong = $ls['SoLuong'];
            $State = $ls['State'];

            $sql = "INSERT INTO LS_Mua (MaHD, MaTK, MaSP, SoLuong, State)
                    VALUES ('$MaHD', '$MaTK', '$MaSP', $SoLuong, '$State')";
            $this->setQuery($sql);
            $this->excuteQuery();
        }

        // Đọc file vouchers.json
        $jsonVouchers = file_get_contents('../public/Data/vouchers.json');
        $vouchers = json_decode($jsonVouchers, true);

        foreach ($vouchers as $vc) {
            $MaV = $vc['MaV'];
            $Discount = $vc['Discount'];

            $sql = "INSERT INTO Vouchers (MaV, Discount)
                    VALUES ('$MaV', $Discount)";
            $this->setQuery($sql);
            $this->excuteQuery();
        }
    }

    }
    $myInit = new M_init();
    $myInit->Create_Structure();
    $myInit->Insert_Data();

    echo "Cơ sở dữ liệu đã được tạo thành công!";
?>