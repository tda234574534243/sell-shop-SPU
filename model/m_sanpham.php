<?php
include_once "m_database.php";

class SanPhamModel extends M_database {
    public function getTotalProducts($keyword = '', $status = '', $masp = '') {
        $sql = "SELECT COUNT(*) as total FROM Products WHERE 1";
        if (!empty($keyword)) {
            $keyword = $this->real_escape_string($keyword);
            $sql .= " AND TenSP LIKE '%$keyword%'";
        }
        if ($status !== '') {
            if ($status == '1') $sql .= " AND SoLuong > 0";
            if ($status == '0') $sql .= " AND SoLuong = 0";
        }
        if (!empty($masp)) {
            $masp = $this->real_escape_string($masp);
            $sql .= " AND MaSP LIKE '%$masp%'";
        }
        $this->setQuery($sql);
        $result = $this->excuteQuery();
        return $result->fetch_assoc()['total'];
    }

    public function isProductExist($masp, $tensp) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE masp = ? OR tensp = ?");
        $stmt->bind_param("ss", $masp, $tensp);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0; // Nếu có ít nhất 1 dòng, nghĩa là trùng
    }

    public function deleteProduct($masp) {
        $masp = $this->real_escape_string($masp);
        $sql = "DELETE FROM Products WHERE MaSP = '$masp'";
        $this->setQuery($sql);
        return $this->excuteQuery();
    }


    public function getProductsByPage($start, $limit, $keyword = '', $status = '', $masp = '', $sort_gia = '', $sort_so_luong = '') {
        $sql = "SELECT * FROM Products WHERE 1";
        if (!empty($keyword)) {
            $keyword = $this->real_escape_string($keyword);
            $sql .= " AND TenSP LIKE '%$keyword%'";
        }
        if ($status !== '') {
            if ($status == '1') $sql .= " AND SoLuong > 0";
            if ($status == '0') $sql .= " AND SoLuong = 0";
        }
        if (!empty($masp)) {
            $masp = $this->real_escape_string($masp);
            $sql .= " AND MaSP LIKE '%$masp%'";
        }

        if ($sort_gia == 'gia_desc') $sql .= " ORDER BY GiaTien DESC";
        else if ($sort_gia == 'gia_asc') $sql .= " ORDER BY GiaTien ASC";
        else if ($sort_so_luong == 'so_luong_desc') $sql .= " ORDER BY SoLuong DESC";
        else if ($sort_so_luong == 'so_luong_asc') $sql .= " ORDER BY SoLuong ASC";
        else $sql .= " ORDER BY MaSP ASC";

        $sql .= " LIMIT $start, $limit";
        $this->setQuery($sql);
        return $this->excuteQuery();
    }

    public function addProduct($masp, $tensp, $nsx, $phanloai, $soluong, $giatien, $mota, $baohanh, $image, $MaTK) {
        if (strpos($image, '../') === 0) {
            $image_path = './' . substr($image, 3); // Cắt bỏ ../ và thêm lại ./
        } else {
            $image_path = $image;
        }

        $sql = "INSERT INTO Products (MaSP, TenSP, NSX, PhanLoai, SoLuong, GiaTien, MoTa, BaoHanh, ImageSP, MaTK) 
                VALUES ('$masp', '$tensp', '$nsx', '$phanloai', '$soluong', '$giatien', '$mota', '$baohanh', '$image_path', '$MaTK')";
        
        $this->setQuery($sql);
        return $this->excuteQuery();
    }
    public function getProductById($masp) {
        $masp = $this->real_escape_string($masp);
        $sql = "SELECT * FROM Products WHERE MaSP = '$masp'";
        $this->setQuery($sql);
        $result = $this->excuteQuery();
        return $result->fetch_assoc();
    }

    public function updateProduct($masp, $tensp, $nsx, $phanloai, $soluong, $giatien, $mota, $baohanh, $image_path = null, $MaTK) {
        if ($image_path) {
            if (strpos($image_path, '../') === 0) {
                $image_path = './' . substr($image_path, 3); // Cắt bỏ ../ và thêm lại ./
            }
        }

        $sql = "UPDATE Products SET 
                TenSP = '$tensp',
                NSX = '$nsx',
                PhanLoai = '$phanloai',
                SoLuong = '$soluong',
                GiaTien = '$giatien',
                MoTa = '$mota',
                BaoHanh = '$baohanh',
                MaTK = '$MaTK'";
        
        if ($image_path) {
            $sql .= ", ImageSP = '$image_path'";
        }
        
        $sql .= " WHERE MaSP = '$masp'";
        
        $this->setQuery($sql);
        return $this->excuteQuery();
    }
}