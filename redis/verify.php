<?php
require_once __DIR__ . '/redis_helper.php';
require_once __DIR__ . '/../model/m_account.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: ../signIn.php'); exit;
}

$rh = new RedisHelper();
$key = 'verify:' . $token;
$val = $rh->get($key);
if (!$val) {
    // token expired or not found
    header('Location: ../signIn.php?message=' . urlencode('Liên kết xác thực không hợp lệ hoặc đã hết hạn') . '&status=error'); exit;
}

$email = $val; // we stored email as value
$acc = new M_account();
$conn = $acc->getConnection();

// Ensure Verified column exists
$colCheck = $conn->query("SHOW COLUMNS FROM account LIKE 'Verified'");
if ($colCheck && $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE account ADD COLUMN Verified tinyint(1) NOT NULL DEFAULT 0");
}

$stmt = $conn->prepare("UPDATE account SET Verified = 1 WHERE Email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();

$rh->del($key);

header('Location: ../signIn.php?message=' . urlencode('Xác thực thành công. Bạn có thể đăng nhập bây giờ.') . '&status=success');
exit;

?>
