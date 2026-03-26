<?php
require('../model/m_account.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Lấy dữ liệu từ form
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Kiểm tra đầu vào
if (empty($email) || empty($password)) {
    die('Vui lòng nhập đầy đủ thông tin.');
}

// Kiểm tra định dạng email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email không hợp lệ.');
}

$acc = new M_account();
$result = $acc->findAccount($email, $password);

if ($result && $result->num_rows > 0) {
    $account = $result->fetch_assoc();

    // Lưu thông tin vào session
    $_SESSION['user_id'] = $account["MaTK"];
    $_SESSION['username'] =  $account['TenTK'];
    $_SESSION['levelID'] = $account['LevelID'];

    // Điều hướng theo quyền
    if ($account['LevelID'] == 1) {
        header('Location: ../admin/analystic_product.php');
    } else {
        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Đăng nhập thành công!',
            'type' => 'success',
            'duration' => 3000
        ];
        header('Location: ../index.php');
    }
    exit();
} else {
    header('Location: ../signIn.php?error=invalid');
    exit();
}

?>