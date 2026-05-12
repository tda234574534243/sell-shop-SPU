<?php
// Form and handler for resetting password using OTP
require_once __DIR__ . '/redis_helper.php';
require_once __DIR__ . '/../model/m_account.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    if ($new === '' || $new !== $confirm) {
        $error = 'password';
    } else {
        $rh = new RedisHelper();
        $key = 'otp:' . md5(strtolower($email));
        $v = $rh->get($key);
        if (!$v || $v !== $otp) {
            $error = 'invalid';
        } else {
            // update password
            $acc = new M_account();
            $conn = $acc->getConnection();
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE account SET Password = ? WHERE Email = ?");
            $stmt->bind_param('ss', $hash, $email);
            if ($stmt->execute()) {
                $rh->del($key);
                header('Location: ../signIn.php?message=' . urlencode('Đặt lại mật khẩu thành công') . '&status=success'); exit;
            } else {
                $error = 'db';
            }
        }
    }
}

$emailPrefill = $_GET['email'] ?? '';
$sent = isset($_GET['sent']);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt lại mật khẩu - Sell Shop SPU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .heading { font-family: 'Montserrat', sans-serif; }
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glass-form { background: rgba(255, 255, 255, 0.06); backdrop-filter: blur(10px); border: 1px solid rgba(99, 102, 241, 0.2); }
        .input-glass { background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(99, 102, 241, 0.3); }
        .input-glass:focus { background: rgba(255, 255, 255, 0.12); border-color: rgba(99, 102, 241, 0.6); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="glass-form rounded-3xl p-8 w-full max-w-md shadow-2xl border border-indigo-500/20">
        <div class="text-center mb-6">
            <img src="/sell-shop-SPU/media/image/other/logo.png" alt="logo" class="w-20 h-20 mx-auto mb-3" onerror="this.style.display='none'">
            <h2 class="font-montserrat text-2xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent">Đặt lại mật khẩu</h2>
            <p class="text-slate-400 text-sm mt-1">Nhập email, mã OTP và mật khẩu mới của bạn</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="p-3 rounded-lg bg-rose-500/20 border border-rose-500/50 mb-4">
                <p class="text-rose-400 text-sm font-semibold">
                    <?php if($error === 'password') echo 'Mật khẩu trống hoặc không khớp.';
                          elseif($error === 'invalid') echo 'Mã OTP không hợp lệ hoặc đã hết hạn.';
                          elseif($error === 'db') echo 'Lỗi cơ sở dữ liệu, thử lại sau.';
                          else echo htmlspecialchars($error);
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if($sent): ?>
            <div class="p-3 rounded-lg bg-emerald-500/20 border border-emerald-500/50 mb-4">
                <p class="text-emerald-300 text-sm">Mã OTP đã gửi tới <?= htmlspecialchars($emailPrefill) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Email</label>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-indigo-400 mr-3 text-sm"></i>
                    <input name="email" type="email" required value="<?= htmlspecialchars($emailPrefill) ?>" class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" placeholder="your@email.com">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Mã OTP</label>
                <div class="flex items-center">
                    <i class="fas fa-key text-indigo-400 mr-3 text-sm"></i>
                    <input name="otp" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" placeholder="123456">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Mật khẩu mới</label>
                <div class="flex items-center">
                    <i class="fas fa-lock text-indigo-400 mr-3 text-sm"></i>
                    <input name="new_password" type="password" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" placeholder="••••••••">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Xác nhận mật khẩu</label>
                <div class="flex items-center">
                    <i class="fas fa-check text-indigo-400 mr-3 text-sm"></i>
                    <input name="confirm_password" type="password" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg">Đặt lại mật khẩu</button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="/sell-shop-SPU/signIn.php" class="text-sm text-indigo-300 hover:text-indigo-200">Quay lại đăng nhập</a>
        </div>
    </div>

    <?php include __DIR__ . '/../template/toastMess.php'; ?>

</body>
</html>
