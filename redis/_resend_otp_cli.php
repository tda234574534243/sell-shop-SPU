<?php
// Resend OTP to an email (CLI helper)
$email = 'projectmap1234@gmail.com';
require_once __DIR__ . '/redis_helper.php';
require_once __DIR__ . '/email_helper.php';
require_once __DIR__ . '/../model/m_account.php';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid email\n"; exit(1);
}

$acc = new M_account();
$res = $acc->findAccountByEmail($email);
if (!$res || $res->num_rows == 0) {
    echo "account not found\n"; exit(1);
}

$otp = random_int(100000, 999999);
$rh = new RedisHelper();
$key = 'otp:' . md5(strtolower($email));
$ok = $rh->set($key, (string)$otp, 60*10);

$eh = new EmailHelper();
$subject = 'Mã OTP đặt lại mật khẩu';
$body = "<p>Mã OTP của bạn là: <strong>{$otp}</strong></p><p>Mã có hiệu lực trong 10 phút.</p>";
$sent = $eh->send($email, $subject, $body);

echo "OTP: {$otp}\n";
echo "Stored: " . ($ok ? 'yes' : 'no') . "\n";
echo "Sent: " . ($sent ? 'yes' : 'no') . "\n";

if (!$sent) {
    echo "Note: SMTP send failed; check SMTP config or mail log.\n";
}

?>
