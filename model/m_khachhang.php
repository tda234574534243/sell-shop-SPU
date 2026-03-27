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
    
    // Lấy LevelID của tài khoản
    public function getLevelByMaTK($maTK) {
        $maTK = intval($maTK);
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT LevelID FROM Account WHERE MaTK = ?");
        if (!$stmt) return null;
        $stmt->bind_param('i', $maTK);
        $ok = $stmt->execute();
        if (!$ok) {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            $message = date('Y-m-d H:i:s') . " | GetLevel Error: " . $conn->error . " | MaTK: " . $maTK . PHP_EOL;
            @error_log($message, 3, $logDir . '/db_errors.log');
            return null;
        }
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return $row['LevelID'] ?? null;
        }
        return null;
    }

    // Cập nhật quyền (LevelID) cho tài khoản
    public function setLevelByMaTK($maTK, $level) {
        $maTK = intval($maTK);
        $level = intval($level);
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE Account SET LevelID = ? WHERE MaTK = ?");
        if (!$stmt) {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            $message = date('Y-m-d H:i:s') . " | Prepare Update Error: " . $conn->error . " | Query: UPDATE Account SET LevelID = $level WHERE MaTK = $maTK" . PHP_EOL;
            @error_log($message, 3, $logDir . '/db_errors.log');
            return false;
        }
        $stmt->bind_param('ii', $level, $maTK);
        $ok = $stmt->execute();
        if (!$ok) {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            $message = date('Y-m-d H:i:s') . " | Execute Update Error: " . $stmt->error . " | Level: $level MaTK: $maTK" . PHP_EOL;
            @error_log($message, 3, $logDir . '/db_errors.log');
            return false;
        }
        return true;
    }

    // Lấy trạng thái khóa (Locked) của tài khoản
    public function getLockedByMaTK($maTK) {
        $maTK = intval($maTK);
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT Locked FROM Account WHERE MaTK = ?");
        if (!$stmt) return null;
        $stmt->bind_param('i', $maTK);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return isset($row['Locked']) ? intval($row['Locked']) : 0;
        }
        return null;
    }

    // Đặt trạng thái khóa cho tài khoản
    public function setLockedByMaTK($maTK, $locked) {
        $maTK = intval($maTK);
        $locked = intval($locked) ? 1 : 0;
        $conn = $this->getConnection();
        // Ensure Locked column exists
        $colCheck = $conn->query("SHOW COLUMNS FROM account LIKE 'Locked'");
        if ($colCheck && $colCheck->num_rows == 0) {
            $conn->query("ALTER TABLE account ADD COLUMN Locked tinyint(1) NOT NULL DEFAULT 0");
        }
        $stmt = $conn->prepare("UPDATE Account SET Locked = ? WHERE MaTK = ?");
        if (!$stmt) return false;
        $stmt->bind_param('ii', $locked, $maTK);
        return $stmt->execute();
    }

    // Cập nhật bởi admin: cho phép admin chỉnh TenTK, Email, SDT, DiaChi, LevelID, Avatar, Password, Locked
    public function adminUpdateAccount($maTK, $data = []) {
        $maTK = intval($maTK);
        $conn = $this->getConnection();

        // Nếu có Avatar cần đảm bảo cột tồn tại
        if (isset($data['Avatar'])) {
            $colCheck = $conn->query("SHOW COLUMNS FROM account LIKE 'Avatar'");
            if ($colCheck && $colCheck->num_rows == 0) {
                $conn->query("ALTER TABLE account ADD COLUMN Avatar varchar(255) NULL");
            }
        }

        $allowedFields = ['TenTK','Email','SDT','DiaChi','LevelID','Avatar','Password','Locked'];
        $setParts = []; $types = ''; $values = [];
        foreach ($allowedFields as $f) {
            if (array_key_exists($f, $data)) {
                $setParts[] = "$f = ?";
                if (in_array($f, ['Locked','LevelID'])) $types .= 'i'; else $types .= 's';
                $values[] = $data[$f];
            }
        }
        if (count($setParts) == 0) return false;
        $types .= 'i';
        $values[] = $maTK;

        $sql = "UPDATE Account SET " . implode(', ', $setParts) . " WHERE MaTK = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $bind_names = [];
        $bind_names[] = $types;
        for ($i=0;$i<count($values);$i++) $bind_names[] = &$values[$i];
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        return $stmt->execute();
    }

    


}
?>
