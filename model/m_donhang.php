<?php
require_once("m_database.php");

class DonHangModel extends M_database {
    public function getLichSuMua($start, $limit, $ten_tk, $ten_sp, $ngay_tu, $ngay_den, $ma_hd) {
        // Xây dựng câu truy vấn SQL với các điều kiện lọc
        $query = "
            SELECT 
                ls.MaHD,
                acc.TenTK,
                p.TenSP,
                ls.SoLuong,
                (ls.SoLuong * p.GiaTien) AS TongTien,
                ls.NgayMua
            FROM LS_Mua ls
            JOIN Account acc ON acc.MaTK = ls.MaTK
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE 1=1
        ";

        // Thêm điều kiện lọc vào câu truy vấn
        if ($ten_tk) {
            $query .= " AND acc.TenTK LIKE '%$ten_tk%'";
        }
        if ($ten_sp) {
            $query .= " AND p.TenSP LIKE '%$ten_sp%'";
        }
        if ($ngay_tu) {
            $query .= " AND ls.NgayMua >= '$ngay_tu'";
        }
        if ($ngay_den) {
            $query .= " AND ls.NgayMua <= '$ngay_den'";
        }
        if ($ma_hd) {
            $query .= " AND ls.MaHD LIKE '%$ma_hd%'";
        }

        // Thêm phân trang
        $query .= " ORDER BY ls.NgayMua DESC LIMIT $start, $limit";

        // Thực thi câu truy vấn
        $this->setQuery($query);
        return $this->excuteQuery();
    }

    public function getTotalLichSuMua($ten_tk, $ten_sp, $ngay_tu, $ngay_den, $ma_hd) {
        // Xây dựng câu truy vấn SQL với các điều kiện lọc
        $query = "
            SELECT COUNT(*) as total
            FROM LS_Mua ls
            JOIN Account acc ON acc.MaTK = ls.MaTK
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE 1=1
        ";

        // Thêm điều kiện lọc vào câu truy vấn
        if ($ten_tk) {
            $query .= " AND acc.TenTK LIKE '%$ten_tk%'";
        }
        if ($ten_sp) {
            $query .= " AND p.TenSP LIKE '%$ten_sp%'";
        }
        if ($ngay_tu) {
            $query .= " AND ls.NgayMua >= '$ngay_tu'";
        }
        if ($ngay_den) {
            $query .= " AND ls.NgayMua <= '$ngay_den'";
        }
        if ($ma_hd) {
            $query .= " AND ls.MaHD LIKE '%$ma_hd%'";
        }

        // Thực thi câu truy vấn
        $this->setQuery($query);
        $result = $this->excuteQuery();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}

?>
