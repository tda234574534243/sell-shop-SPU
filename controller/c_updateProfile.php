<?php
    session_start();
    include('../model/m_account.php');

    $maKH = $_SESSION['user_id'] ?? 0;
    if ($maKH <= 0) die("Không xác định người dùng.");

    $hoTen = $_POST['HoTen'] ?? '';
    $email = $_POST['Email'] ?? '';
    $sdt = $_POST['SDT'] ?? '';
    $diaChi = $_POST['DiaChi'] ?? '';

    $acc = new M_account();
    if ($acc->updateAccount($maKH, $hoTen, $email, $sdt, $diaChi)) {
        $_SESSION['username'] = $hoTen;
        $_SESSION['email'] = $email;
        $_SESSION['sdt'] = $sdt;
        $_SESSION['diachi'] = $diaChi;
    } else {
        die("Cập nhật thông tin thất bại.");
    }
    $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Cập nhật thông tin thành công!',
            'type' => 'success',
            'duration' => 3000
    ];
    header("Location: ../user.php");
exit;
