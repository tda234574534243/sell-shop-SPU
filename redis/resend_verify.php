<?php
// Resend verification link for an email address
require_once __DIR__ . '/redis_helper.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/../model/m_account.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = trim($_GET['email'] ?? '');
} else {
    $email = trim($_POST['email'] ?? '');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../signIn.php?message=' . urlencode('Email không hợp lệ') . '&status=error'); exit;
}

$acc = new M_account();
$res = $acc->findAccountByEmail($email);
if (!$res || $res->num_rows == 0) {
    header('Location: ../signIn.php?message=' . urlencode('Không tìm thấy tài khoản') . '&status=error'); exit;
}

$token = bin2hex(random_bytes(16));
$rh = new RedisHelper();
$rh->set('verify:' . $token, $email, 60*60*24);

// build verify url
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
$basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
$verifyUrl = $scheme . '://' . $host . rtrim($basePath, '/') . '/redis/verify.php?token=' . $token;

$eh = new EmailHelper();
$subject = 'Xác thực email của bạn';
$body = "<p>Xin chào,</p><p>Vui lòng bấm vào liên kết sau để xác thực email của bạn:</p><p><a href=\"{$verifyUrl}\">Xác thực email</a></p><p>Liên kết có hiệu lực 24 giờ.</p>";
@ $eh->send($email, $subject, $body);

header('Location: ../signIn.php?message=' . urlencode('Đã gửi lại email xác thực. Vui lòng kiểm tra hộp thư.') . '&status=success'); exit;

?>
