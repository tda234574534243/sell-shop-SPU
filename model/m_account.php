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

        $stmt = $conn->prepare("INSERT INTO account (TenTK, Email, SDT, DiaChi, Password, LevelID) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $tenTK, $email, $phone, $diaChi, $password, $level);
        return $stmt->execute();
    }

    public function updateAccount($maTK, $tenTK, $email, $phone, $diaChi)
    {
        $conn = $this->getConnection();
        $stmt = $conn->prepare("UPDATE account SET TenTK = ?, Email = ?, SDT = ?, DiaChi = ? WHERE MaTK = ?");
        $stmt->bind_param("ssssi", $tenTK, $email, $phone, $diaChi, $maTK);
        return $stmt->execute();
    }
}

?>
