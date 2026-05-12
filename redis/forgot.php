<?php
// Form to request OTP for password reset
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu - Sell Shop SPU</title>
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
            <h2 class="font-montserrat text-2xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent">Yêu cầu mã OTP</h2>
            <p class="text-slate-400 text-sm mt-1">Nhập email của bạn để nhận mã OTP đặt lại mật khẩu</p>
        </div>

        <form method="POST" action="request_otp.php" class="space-y-4">
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Email</label>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-indigo-400 mr-3 text-sm"></i>
                    <input type="email" name="email" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" placeholder="your@email.com">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg">Gửi mã OTP</button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="/sell-shop-SPU/signIn.php" class="text-sm text-indigo-300 hover:text-indigo-200">Quay lại đăng nhập</a>
        </div>
    </div>

    <?php include __DIR__ . '/../template/toastMess.php'; ?>
</body>
</html>
