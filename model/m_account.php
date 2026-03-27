<?php
require_once("m_database.php");
class M_account extends M_database
{
    public function findAccount($email, $password)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM account WHERE Email = ? AND Password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function findAccountByEmail($email)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM account WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAccount($maTK)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM account WHERE MaTK = ?");
        $stmt->bind_param("i", $maTK);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function isUserExist($email, $phone)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("SELECT * FROM account WHERE Email = ? OR SDT = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        return ($result && $result->num_rows > 0);
    }

    public function insertAccount($tenTK, $email, $phone, $diaChi, $password)
    {
        $conn = $this->getConnection();
        $level = 0; // mặc định user

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO account (TenTK, Email, SDT, DiaChi, Password, LevelID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $tenTK, $email, $phone, $diaChi, $passwordHash, $level);
        return $stmt->execute();
    }

    public function updateAccount($maTK, $tenTK, $email, $phone, $diaChi)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE account SET TenTK = ?, Email = ?, SDT = ?, DiaChi = ? WHERE MaTK = ?");
        $stmt->bind_param("ssssi", $tenTK, $email, $phone, $diaChi, $maTK);
        return $stmt->execute();
    }

    // Cập nhật profile: thông tin cơ bản, avatar tùy chọn, và thay đổi mật khẩu nếu có
    // Trả về true nếu thành công, false nếu lỗi DB, và 'wrong_password' nếu mật khẩu hiện tại không đúng
    public function updateProfile($maTK, $tenTK, $email, $phone, $diaChi, $avatarPath = null, $currentPassword = null, $newPassword = null)
    {
        $conn = $this->getConnection();

        // Nếu có yêu cầu đổi mật khẩu
        $changePassword = false;
        if (!empty($newPassword)) {
            // kiểm tra current password
            $stmt = $conn->prepare("SELECT Password FROM account WHERE MaTK = ?");
            if (!$stmt) return false;
            $stmt->bind_param('i', $maTK);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $hash = $row['Password'] ?? null;
            if (!$hash || !password_verify($currentPassword ?? '', $hash)) {
                return 'wrong_password';
            }
            $changePassword = true;
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Xây dựng câu lệnh UPDATE động
        $fields = [
            'TenTK' => $tenTK,
            'Email' => $email,
            'SDT' => $phone,
            'DiaChi' => $diaChi,
        ];
        if (!empty($avatarPath)) $fields['Avatar'] = $avatarPath;
        if ($changePassword) $fields['Password'] = $newHash;

        $setParts = [];
        $types = '';
        $values = [];
        foreach ($fields as $k => $v) {
            $setParts[] = "$k = ?";
            $types .= 's';
            $values[] = $v;
        }
        // MaTK cuối cùng là int
        $types .= 'i';
        $values[] = $maTK;

        $sql = "UPDATE account SET " . implode(', ', $setParts) . " WHERE MaTK = ?";

        // Nếu có trường Avatar mới và cột chưa tồn tại, cố gắng thêm cột
        if (array_key_exists('Avatar', $fields)) {
            $colCheck = $conn->query("SHOW COLUMNS FROM account LIKE 'Avatar'");
            if ($colCheck && $colCheck->num_rows == 0) {
                $conn->query("ALTER TABLE account ADD COLUMN Avatar varchar(255) NULL");
            }
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        // bind params dynamically
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($values); $i++) {
            $bind_names[] = &$values[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        $ok = $stmt->execute();
        return $ok;
    }
}

?>
