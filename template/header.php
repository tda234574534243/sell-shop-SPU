<?php
    include_once(__DIR__ . '/../model/m_giohang.php');
    include_once(__DIR__ . '/../model/m_account.php');

    $cart = new M_giohang();
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $isLoggedIn = isset($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['levelID']) && $_SESSION['levelID'] == 1;

    $maKH = $_SESSION['user_id'] ?? 0;
    $currentPage = basename($_SERVER['PHP_SELF']);

    $currentAccount = null;
    if ($isLoggedIn) {
        $accModel = new M_account();
        $accRes = $accModel->getAccount($maKH);
        if ($accRes && $accRes->num_rows > 0) $currentAccount = $accRes->fetch_assoc();
    }

    include_once(__DIR__ . '/../model/m_notification.php');
    $notifModel = new M_notification();
    $notifCount = $notifModel->countActive($isLoggedIn ? $maKH : null);
    $notifList = $notifModel->getActive(5, $isLoggedIn ? $maKH : null);

    if ($isLoggedIn) {
        $result = $cart->getCartItems($maKH);
        if ($result && $result->num_rows > 0) {
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row; 
            }
        } else {
            $cartItems = [];
        }
    } else {
        $cartItems = [];
    }
    $totalCartQty = 0;
    if (!empty($cartItems)) {
        foreach ($cartItems as $ci) {
            $totalCartQty += isset($ci['SoLuong']) ? (int)$ci['SoLuong'] : 0;
        }
    }
?>

<!-- Modern Luxury Navigation -->
<nav class="glass-effect sticky top-0 z-50 border-b border-indigo-500/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-3">
                <img src="/sell-shop-SPU/media/image/other/logo.png" alt="logo" class="h-10 w-auto" onerror="this.style.display='none'">
                <a href="index.php" class="font-montserrat text-2xl font-bold bg-gradient-to-r from-indigo-400 to-rose-400 bg-clip-text text-transparent hover:opacity-80 transition">Sup3rDup3r</a>
            </div>

            <!-- Center Menu -->
            <div class="hidden md:flex gap-8">
                <a href="index.php" class="<?= ($currentPage === 'index.php') ? 'text-indigo-400' : 'text-slate-300' ?> hover:text-indigo-400 font-medium transition text-sm">Trang Chủ</a>
                <a href="introduce.php" class="<?= ($currentPage === 'introduce.php') ? 'text-indigo-400' : 'text-slate-300' ?> hover:text-indigo-400 font-medium transition text-sm">Giới Thiệu</a>
                <a href="contact.php" class="<?= ($currentPage === 'contact.php') ? 'text-indigo-400' : 'text-slate-300' ?> hover:text-indigo-400 font-medium transition text-sm">Liên Hệ</a>
                <a href="track-order.php" class="<?= ($currentPage === 'track-order.php') ? 'text-indigo-400' : 'text-slate-300' ?> hover:text-indigo-400 font-medium transition text-sm">Theo Dõi</a>
                <?php if ($isLoggedIn && $isAdmin): ?>
                    <div class="relative group">
                        <button class="text-slate-300 hover:text-indigo-400 font-medium transition text-sm flex gap-1">Quản Lý <i class="fas fa-chevron-down text-xs mt-1"></i></button>
                        <div class="absolute hidden group-hover:block glass-effect rounded-xl p-2 min-w-48 shadow-xl">
                            <a href="admin/page-builder.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm"><i class="fas fa-wand-magic-sparkles"></i> Builder</a>
                            <a href="admin/analystic_product.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm">Sản Phẩm</a>
                            <a href="admin/analystic_customer.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm">Khách Hàng</a>
                            <a href="admin/notifications.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm">Thông Báo</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Search & Right Icons -->
            <div class="flex items-center gap-6">
                <form method="GET" action="searchProduct.php" class="hidden lg:flex">
                    <input type="search" name="query" placeholder="Tìm kiếm..." class="glass-effect rounded-full px-4 py-2 text-sm placeholder-slate-400 text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" required>
                </form>

                <!-- Icons Group -->
                <div class="flex items-center gap-4">
                    <!-- Wishlist -->
                    <a href="wishlist.php" class="relative text-slate-300 hover:text-rose-400 transition text-xl" title="Yêu Thích">
                        <i class="fas fa-heart"></i>
                        <?php if ($isLoggedIn) { include_once(__DIR__ . '/../model/m_wishlist.php'); $mw = new M_wishlist(); $ws = $mw->countByUser($maKH); if ($ws>0) echo '<span class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">'.($ws>99?'99+':$ws).'</span>'; } ?>
                    </a>

                    <!-- Notifications -->
                    <div class="relative group">
                        <button class="relative text-slate-300 hover:text-indigo-400 transition text-xl" title="Thông Báo">
                            <i class="fas fa-bell"></i>
                            <?php if ($notifCount>0) echo '<span class="absolute -top-2 -right-2 bg-indigo-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">'.($notifCount>99?'99+':$notifCount).'</span>'; ?>
                        </button>
                        <div class="absolute hidden group-hover:block glass-effect rounded-xl p-3 w-80 right-0 shadow-xl top-full mt-2">
                            <p class="font-montserrat font-semibold text-slate-100 mb-2 text-sm">Thông Báo Mới</p>
                            <div class="max-h-64 overflow-y-auto">
                                <?php if ($notifList && $notifList->num_rows>0): while($n = $notifList->fetch_assoc()): ?>
                                    <a href="/sell-shop-SPU/notification_detail.php?id=<?= $n['id'] ?>" class="block px-3 py-2 rounded-lg hover:bg-indigo-500/20 transition text-slate-300 hover:text-indigo-300 text-xs border-b border-slate-700/30">
                                        <p class="font-semibold"><?= htmlspecialchars($n['Title']) ?></p>
                                        <p class="text-slate-400 text-xs"><?= substr(strip_tags($n['Content']),0,60) ?></p>
                                    </a>
                                <?php endwhile; else: ?>
                                    <p class="text-slate-400 text-xs text-center py-4">Không có thông báo</p>
                                <?php endif; ?>
                            </div>
                            <?php if ($isAdmin) echo '<div class="border-t border-slate-700/30 mt-2 pt-2"><a href="admin/notifications.php" class="block text-center text-indigo-400 hover:text-indigo-300 text-xs font-semibold">Quản Lý Thông Báo</a></div>'; ?>
                        </div>
                    </div>

                    <!-- Cart -->
                    <a href="payProduct.php" class="relative text-slate-300 hover:text-indigo-400 transition text-xl" title="Giỏ Hàng">
                        <i class="fas fa-shopping-bag"></i>
                        <?php if ($totalCartQty > 0): ?><span class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $totalCartQty>99? '99+': $totalCartQty; ?></span><?php endif; ?>
                    </a>

                    <!-- User Menu -->
                    <?php if ($isLoggedIn): ?>
                        <div class="relative group">
                            <?php $hdrAvatar = ($currentAccount && !empty($currentAccount['Avatar'])) ? $currentAccount['Avatar'] : 'media/image/avatars/default.png'; ?>
                            <img src="<?= htmlspecialchars($hdrAvatar) ?>" alt="avatar" class="w-9 h-9 rounded-full border-2 border-indigo-400 cursor-pointer hover:border-rose-400 transition">
                            <div class="absolute hidden group-hover:block glass-effect rounded-xl p-2 min-w-40 right-0 shadow-xl top-full mt-2">
                                <a href="user.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm rounded-lg hover:bg-indigo-500/20">Tài Khoản</a>
                                <a href="wishlist.php" class="block px-4 py-2 hover:text-indigo-400 text-slate-300 transition text-sm rounded-lg hover:bg-indigo-500/20">Yêu Thích</a>
                                <div class="border-t border-slate-700/30 my-1"></div>
                                <a href="controller/c_logout.php" class="block px-4 py-2 hover:text-rose-400 text-slate-300 transition text-sm rounded-lg hover:bg-rose-500/10">Đăng Xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="signin.php" class="px-4 py-2 rounded-lg text-slate-300 hover:text-indigo-400 transition text-sm font-semibold">Đăng Nhập</a>
                        <a href="signup.php" class="px-4 py-2 rounded-lg glass-effect border border-indigo-500 text-indigo-300 hover:text-indigo-200 hover:bg-indigo-500/20 transition text-sm font-semibold">Đăng Ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Compact Mobile Search -->
<div class="md:hidden glass-effect border-b border-indigo-500/10 px-4 py-3">
    <form method="GET" action="searchProduct.php">
        <input type="search" name="query" placeholder="Tìm kiếm..." class="w-full glass-effect rounded-lg px-4 py-2 text-sm placeholder-slate-400 text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" required>
    </form>
</div>
