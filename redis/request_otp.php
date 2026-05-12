<?php
require_once __DIR__ . '/redis_helper.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/../model/m_account.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot.php'); exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: forgot.php?error=invalid'); exit;
}

$acc = new M_account();
$res = $acc->findAccountByEmail($email);
if (!$res || $res->num_rows == 0) {
    header('Location: forgot.php?error=notfound'); exit;
}

$otp = random_int(100000, 999999);
$rh = new RedisHelper();
$key = 'otp:' . md5(strtolower($email));
$rh->set($key, (string)$otp, 60*10); // 10 minutes

$eh = new EmailHelper('no-reply@yourdomain.local','Sup3rDup3r');
$subject = 'Mã OTP đặt lại mật khẩu';
$body = "<p>Mã OTP của bạn là: <strong>{$otp}</strong></p><p>Mã có hiệu lực trong 10 phút.</p>";
@ $eh->send($email, $subject, $body);

header('Location: reset_password.php?sent=1&email=' . urlencode($email));
exit;

?>
