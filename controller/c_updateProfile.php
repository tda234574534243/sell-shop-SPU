<?php
    session_start();
    include('../model/m_account.php');

    $maKH = $_SESSION['user_id'] ?? 0;
    if ($maKH <= 0) die("Không xác định người dùng.");

    $hoTen = $_POST['HoTen'] ?? '';
    // Do NOT trust client-submitted email. Load existing email from DB and ignore changes from POST.
    $accCheck = new M_account();
    $existing = $accCheck->getAccount($maKH);
    $existingRow = ($existing && $existing->num_rows>0) ? $existing->fetch_assoc() : null;
    $email = $existingRow['Email'] ?? '';
    $sdt = $_POST['SDT'] ?? '';
    $diaChi = $_POST['DiaChi'] ?? '';

    // Password change fields
    $currentPassword = $_POST['CurrentPassword'] ?? null;
    $newPassword = $_POST['NewPassword'] ?? null;
    $confirmPassword = $_POST['ConfirmPassword'] ?? null;

    // Nếu nhập mật khẩu mới, kiểm tra xác nhận
    if (!empty($newPassword) && ($newPassword !== $confirmPassword)) {
        $_SESSION['toast'] = [
            'title' => 'Lỗi',
            'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.',
            'type' => 'error',
            'duration' => 3000
        ];
        header("Location: ../user.php");
        exit;
    }

    // Handle avatar upload if provided
    $avatarDbPath = null;
    if (isset($_FILES['Avatar']) && $_FILES['Avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['Avatar']['tmp_name'];
        $fileName = $_FILES['Avatar']['name'];
        $fileType = mime_content_type($fileTmp);
        $allowed = ['image/jpeg','image/png','image/gif'];
        if (in_array($fileType, $allowed)) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadDir = __DIR__ . '/../media/image/avatars';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $newName = 'avatar_' . $maKH . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $newName;
            if (move_uploaded_file($fileTmp, $destPath)) {
                // Path to store in DB (relative to project root)
                $avatarDbPath = 'media/image/avatars/' . $newName;
            }
        }
    }

    $acc = new M_account();
    if (file_exists(__DIR__ . '/../helper/logger.php')) require_once __DIR__ . '/../helper/logger.php';
    // If user attempted to change email in the POST payload, log it and ignore the change.
    $postedEmail = $_POST['Email'] ?? '';
    if (!empty($postedEmail) && $postedEmail !== $email) {
        if (file_exists(__DIR__ . '/../helper/logger.php')) require_once __DIR__ . '/../helper/logger.php';
        if (function_exists('log_action')) log_action('WARN', 'User attempted client-side email change ignored', ['MaTK'=>$maKH, 'postedEmail'=>$postedEmail, 'actualEmail'=>$email]);
    }

    $res = $acc->updateProfile($maKH, $hoTen, $email, $sdt, $diaChi, $avatarDbPath, $currentPassword, $newPassword);

    if ($res === 'wrong_password') {
        $_SESSION['toast'] = [
            'title' => 'Lỗi',
            'message' => 'Mật khẩu hiện tại không đúng.',
            'type' => 'error',
            'duration' => 3000
        ];
        header("Location: ../user.php");
        exit;
    }

    if ($res) {
        $_SESSION['username'] = $hoTen;
        $_SESSION['email'] = $email;
        $_SESSION['sdt'] = $sdt;
        $_SESSION['diachi'] = $diaChi;
        if ($avatarDbPath) $_SESSION['avatar'] = $avatarDbPath;

        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Cập nhật thông tin thành công!',
            'type' => 'success',
            'duration' => 3000
        ];
        if (function_exists('log_action')) {
            $changes = ['HoTen' => $hoTen, 'Email' => $email, 'SDT' => $sdt, 'DiaChi' => $diaChi];
            if ($avatarDbPath) $changes['Avatar'] = $avatarDbPath;
            if (!empty($newPassword)) $changes['PasswordChanged'] = true;
            log_action('INFO', 'Profile updated', ['MaTK' => $maKH, 'changes' => $changes]);
        }
    } else {
        $_SESSION['toast'] = [
            'title' => 'Lỗi',
            'message' => 'Cập nhật thông tin thất bại.',
            'type' => 'error',
            'duration' => 3000
        ];
    }
    header("Location: ../user.php");
exit;
