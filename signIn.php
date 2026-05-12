<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng Nhập - Sup3rDup3r Premium</title>
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
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <img src="./media/image/other/logo.png" alt="logo" class="w-20 h-20 mx-auto mb-4" onerror="this.style.display='none'">
            <h1 class="font-montserrat text-3xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent mb-2">Sup3rDup3r</h1>
            <p class="text-slate-400 text-sm">Đăng nhập tài khoản của bạn</p>
        </div>

        <!-- Sign In Form -->
        <form method="POST" action="controller/c_signIn.php" class="space-y-4">
            <!-- Email Input -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Email</label>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-indigo-400 mr-3 text-sm"></i>
                    <input type="email" name="email" placeholder="your@email.com" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-2">Mật Khẩu</label>
                <div class="flex items-center">
                    <i class="fas fa-lock text-indigo-400 mr-3 text-sm"></i>
                    <input type="password" name="password" placeholder="••••••••" required class="input-glass flex-1 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                </div>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                <div class="p-3 rounded-lg bg-rose-500/20 border border-rose-500/50">
                    <p class="text-rose-400 text-sm font-semibold">❌ Đăng nhập thất bại. Kiểm tra lại email/mật khẩu.</p>
                </div>
            <?php endif; ?>

            <!-- Login Button -->
            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg mt-6">
                <i class="fas fa-sign-in-alt mr-2"></i>Đăng Nhập
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="/sell-shop-SPU/redis/forgot.php" class="text-sm text-indigo-300 hover:text-indigo-200">Quên mật khẩu?</a>
        </div>

        <?php if (isset($_GET['unverified']) && $_GET['unverified'] == '1'): ?>
            <div class="mt-4 p-4 rounded-lg bg-yellow-500/10 border border-yellow-400/20 text-center">
                <p class="text-yellow-300 text-sm mb-2">Vui lòng kiểm tra mail để xác thực đăng ký.</p>
                <?php $e = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>
                <a href="/sell-shop-SPU/redis/resend_verify.php?email=<?php echo urlencode($e); ?>" class="inline-block px-4 py-2 rounded-lg bg-yellow-500 text-yellow-900 font-semibold text-sm">Gửi lại email xác thực</a>
            </div>
        <?php endif; ?>

        <!-- Sign Up Link -->
        <div class="mt-6 text-center border-t border-slate-700/30 pt-6">
            <p class="text-slate-400 text-sm mb-2">Chưa có tài khoản?</p>
            <a href="signUp.php" class="px-6 py-2 rounded-xl glass-form border border-indigo-500/50 text-indigo-300 hover:text-indigo-200 hover:bg-indigo-500/20 transition font-semibold text-sm inline-block">
                Tạo tài khoản mới
            </a>
        </div>
    </div>

    <?php include 'template/toastMess.php'; ?>
</body>
</html>