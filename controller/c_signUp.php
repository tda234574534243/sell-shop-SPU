<?php
require('../model/m_account.php');
if (file_exists(__DIR__ . '/../helper/logger.php')) require_once __DIR__ . '/../helper/logger.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$tenTK = trim($_POST['TenTK'] ?? '');
$email = trim($_POST['Email'] ?? '');
$sdt = trim($_POST['SDT'] ?? '');
$diaChi = trim($_POST['DiaChi'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirnPassword'] ?? '');



// Kiểm tra mật khẩu khớp nhau
if ($password !== $confirmPassword) {
    header("Location: ../signUp.php?error=passwordmismatch");
    exit();
}

$acc = new M_account();

// Kiểm tra tài khoản đã tồn tại
if ($acc->isUserExist($email, $sdt)) {
    header("Location: ../signUp.php?error=exists");
    exit();
}

// Thêm tài khoản
if ($acc->insertAccount($tenTK, $email, $sdt, $diaChi, $password)) {
    // Ensure Verified column exists and mark this new account as unverified (0)
    $conn = $acc->getConnection();
    $colCheck = $conn->query("SHOW COLUMNS FROM account LIKE 'Verified'");
    if ($colCheck && $colCheck->num_rows == 0) {
        $conn->query("ALTER TABLE account ADD COLUMN Verified tinyint(1) NOT NULL DEFAULT 0");
    } else {
        // set explicitly to 0 for this email in case default isn't present
        $stmtSet = $conn->prepare("UPDATE account SET Verified = 0 WHERE Email = ?");
        if ($stmtSet) { $stmtSet->bind_param('s', $email); $stmtSet->execute(); }
    }
    // create verification token and store in redis
    require_once __DIR__ . '/../redis/redis_helper.php';
    require_once __DIR__ . '/../redis/email_helper.php';

    $token = bin2hex(random_bytes(16));
    $rh = new RedisHelper();
    // store email under verify:token for 24 hours
    $rh->set('verify:' . $token, $email, 60*60*24);

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    // Build base path to project root (two levels up from controller)
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // e.g. /sell-shop-SPU
    $verifyUrl = $scheme . '://' . $host . rtrim($basePath, '/') . '/redis/verify.php?token=' . $token;

    $eh = new EmailHelper('no-reply@yourdomain.local', 'Sup3rDup3r');
    $subject = 'Xác thực email của bạn';
    $body = "<p>Xin chào " . htmlspecialchars($tenTK) . ",</p>" .
            "<p>Cảm ơn bạn đã đăng ký. Vui lòng bấm vào liên kết bên dưới để xác thực email:</p>" .
            "<p><a href=\"{$verifyUrl}\">Xác thực email</a></p>" .
            "<p>Liên kết sẽ hết hạn sau 24 giờ.</p>";
    @ $eh->send($email, $subject, $body);

     $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Đăng kí tài khoản thành công! Vui lòng kiểm tra email để xác thực.',
            'type' => 'success',
            'duration' => 5000
    ];
    if (function_exists('log_action')) {
        log_action('INFO', 'New user registered (pending verification)', ['username' => $tenTK, 'email' => $email]);
    }
    header("Location: ../signUp.php?message=Đăng ký thành công. Vui lòng kiểm tra email để xác thực.&status=success");
    exit();
} else {
    header("Location: ../signUp.php?error=insertfail");
    exit();
}