<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng Ký - Sup3rDup3r Premium</title>
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
    <div class="glass-form rounded-3xl p-8 w-full max-w-md shadow-2xl border border-indigo-500/20 max-h-screen overflow-y-auto">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
            <img src="./media/image/other/logo.png" alt="logo" class="w-16 h-16 mx-auto mb-3" onerror="this.style.display='none'">
            <h1 class="font-montserrat text-3xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent mb-1">Sup3rDup3r</h1>
            <p class="text-slate-400 text-sm">Tạo tài khoản mới</p>
        </div>

        <!-- Sign Up Form -->
        <form method="POST" action="controller/c_signUp.php" class="space-y-3">
            <!-- Account Name -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Tên Tài Khoản</label>
                <div class="flex items-center">
                    <i class="fas fa-user text-indigo-400 mr-3 text-sm"></i>
                    <input type="text" name="TenTK" placeholder="Tên người dùng" required class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Email</label>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-indigo-400 mr-3 text-sm"></i>
                    <input type="email" name="Email" placeholder="your@email.com" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Số Điện Thoại</label>
                <div class="flex items-center">
                    <i class="fas fa-phone text-indigo-400 mr-3 text-sm"></i>
                    <input type="text" name="SDT" placeholder="09xxxxxxxx" required class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Địa Chỉ</label>
                <div class="flex items-center">
                    <i class="fas fa-map-marker-alt text-indigo-400 mr-3 text-sm"></i>
                    <input type="text" name="DiaChi" placeholder="Địa chỉ giao hàng" required class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Mật Khẩu</label>
                <div class="flex items-center">
                    <i class="fas fa-lock text-indigo-400 mr-3 text-sm"></i>
                    <input type="password" name="password" placeholder="••••••••" required class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1">Xác Nhận Mật Khẩu</label>
                <div class="flex items-center">
                    <i class="fas fa-lock text-indigo-400 mr-3 text-sm"></i>
                    <input type="password" name="confirnPassword" placeholder="••••••••" required class="input-glass flex-1 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
                </div>
            </div>

            <!-- Error/Success Messages -->
            <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'exists') {
                    echo '<div class="p-3 rounded-lg bg-rose-500/20 border border-rose-500/50"><p class="text-rose-400 text-xs font-semibold">⚠️ Email hoặc số điện thoại đã tồn tại</p></div>';
                } elseif ($_GET['error'] == 'passwordmismatch') {
                    echo '<div class="p-3 rounded-lg bg-rose-500/20 border border-rose-500/50"><p class="text-rose-400 text-xs font-semibold">⚠️ Mật khẩu không khớp</p></div>';
                }
            }
            if (isset($_GET['message']) && isset($_GET['status'])) {
                $bgClass = $_GET['status'] === 'success' ? 'bg-green-500/20 border-green-500/50' : 'bg-rose-500/20 border-rose-500/50';
                $textClass = $_GET['status'] === 'success' ? 'text-green-400' : 'text-rose-400';
                echo '<div class="p-3 rounded-lg ' . $bgClass . ' border"><p class="' . $textClass . ' text-xs font-semibold">' . htmlspecialchars($_GET['message']) . '</p></div>';
                if ($_GET['status'] === 'success') {
                    echo '<script>setTimeout(function() { window.location.href = "./signIn.php"; }, 3000);</script>';
                }
            }
            ?>

            <!-- Sign Up Button -->
            <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold transition transform hover:scale-105 active:scale-95 shadow-lg mt-4 text-sm">
                <i class="fas fa-user-plus mr-2"></i>Tạo Tài Khoản
            </button>
        </form>

        <!-- Sign In Link -->
        <div class="mt-4 text-center border-t border-slate-700/30 pt-4">
            <p class="text-slate-400 text-sm mb-2">Đã có tài khoản?</p>
            <a href="signIn.php" class="px-6 py-2 rounded-xl glass-form border border-indigo-500/50 text-indigo-300 hover:text-indigo-200 hover:bg-indigo-500/20 transition font-semibold text-sm inline-block">
                Đăng Nhập
            </a>
        </div>
    </div>
</body>
</html>