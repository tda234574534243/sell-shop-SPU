<?php
require_once("m_database.php");

class KhachHangModel extends M_database {
    
    // Lấy danh sách khách hàng với phân trang
    public function getKhachHangs($page, $limit = 5, $search = []) {
        // Tính offset dựa vào trang hiện tại
        $offset = ($page - 1) * $limit;

        // Xây dựng query với các bộ lọc (nếu có)
        $sql = "SELECT * FROM Account WHERE 1";
        
        // Thêm điều kiện lọc vào query nếu có
        if (!empty($search['ten_khach'])) {
            $sql .= " AND TenTK LIKE '%" . $this->real_escape_string($search['ten_khach']) . "%'";
        }
        if (!empty($search['email'])) {
            $sql .= " AND Email LIKE '%" . $this->real_escape_string($search['email']) . "%'";
        }
        if (!empty($search['sdt'])) {
            $sql .= " AND SDT LIKE '%" . $this->real_escape_string($search['sdt']) . "%'";
        }

        $sql .= " LIMIT $offset, $limit"; // Thêm phân trang

        $this->setQuery($sql);
        $result = $this->excuteQuery();

        return $result;
    }

    // Lấy tổng số khách hàng để tính tổng trang
    public function getTotalKhachHangs($search = []) {
        $sql = "SELECT COUNT(*) AS total FROM Account WHERE 1";
        
        // Thêm điều kiện lọc vào query nếu có
        if (!empty($search['ten_khach'])) {
            $sql .= " AND TenTK LIKE '%" . $this->real_escape_string($search['ten_khach']) . "%'";
        }
        if (!empty($search['email'])) {
            $sql .= " AND Email LIKE '%" . $this->real_escape_string($search['email']) . "%'";
        }
        if (!empty($search['sdt'])) {
            $sql .= " AND SDT LIKE '%" . $this->real_escape_string($search['sdt']) . "%'";
        }

        $this->setQuery($sql);
        $result = $this->excuteQuery();

        // Lấy tổng số khách hàng
        $data = $result->fetch_assoc();
        return $data['total'];
    }
    public function xoaKhachHang($maTK) {
        $maTK = intval($maTK); // Đảm bảo kiểu dữ liệu an toàn

        // Kiểm tra nếu người dùng là admin (LevelID = 1)
        $sqlCheckAdmin = "SELECT LevelID FROM Account WHERE MaTK = $maTK";
        $this->setQuery($sqlCheckAdmin);
        $result = $this->excuteQuery();
        
        if ($result) {
            $data = $result->fetch_assoc();
            if ($data['LevelID'] == 1) {  // Nếu là admin thì không cho xóa
                return "Không thể xóa tài khoản admin";
            }
        }

        // Nếu không phải admin, thực hiện xóa
        $sql = "DELETE FROM Account WHERE MaTK = $maTK";
        $this->setQuery($sql);
        return $this->excuteQuery();
    }

    


}
?>
