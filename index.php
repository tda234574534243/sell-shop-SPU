<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // 1. Quản lý Banner chào mừng
    $showBanner = false;
    if (!empty($_SESSION['show_welcome_banner'])) {
        $showBanner = true;
        unset($_SESSION['show_welcome_banner']);
    }
    
    // 2. Khởi tạo Model & Load Page Builder Data
    include_once 'model/m_database.php';
    include_once 'model/m_pagebuilder.php';
    include_once 'model/m_notification.php';
    include_once 'model/m_voucher.php';
    include_once 'helper/block_renderer.php';
    include_once 'model/m_wishlist.php';

    $db = new M_database(); // Khởi tạo DB dùng chung
    $pageBuilder = new M_pagebuilder();
    $nm = new M_notification();
    $vm = new M_voucher();

    $leftBlocks = $pageBuilder->getBlocksBySection('homepage', 'left');
    $centerBlocks = $pageBuilder->getBlocksBySection('homepage', 'center');
    $rightBlocks = $pageBuilder->getBlocksBySection('homepage', 'right');

    // Dữ liệu hệ thống mặc định
    $maKH = $_SESSION['user_id'] ?? null;
    $sideNotifs = $nm->getActive(5, $maKH);
    $sideVouchers = $vm->getAll(5);

    // Lấy 3 sản phẩm ngẫu nhiên cho Sidebar Phải
    $db->setQuery("SELECT * FROM products ORDER BY RAND() LIMIT 3");
    $randomProducts = $db->excuteQuery();

    // Tính tổng số lượng trong giỏ hàng để cập nhật UI chính xác
    $totalCartQty = 0;
    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $totalCartQty += (isset($item['qty']) ? $item['qty'] : 1);
        }
    }
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-12 gap-6">
        <!-- Left Sidebar -->
        <aside class="col-span-12 lg:col-span-2">
            <div class="space-y-4">
                <?php if (!empty($leftBlocks)): foreach ($leftBlocks as $block): ?>
                    <div class="soft-shadow glass-effect rounded-2xl p-4"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>

                <!-- Notifications Widget -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-indigo-500/20">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-bell text-indigo-400"></i> Tin Mới
                    </h6>
                    <div class="space-y-2">
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <a href="notification_detail.php?id=<?= $s['id'] ?>" class="block text-slate-300 hover:text-indigo-400 text-xs hover:translate-x-1 transition">
                                <p class="font-semibold truncate"><?= htmlspecialchars($s['Title']) ?></p>
                            </a>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- Vouchers Widget -->
                <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                    <div class="soft-shadow glass-effect rounded-2xl p-4 border border-rose-500/20">
                        <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                            <i class="fas fa-gift text-rose-400"></i> Voucher
                        </h6>
                        <div class="space-y-2">
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="p-2 rounded-lg glass-effect border border-dashed border-rose-400/30 hover:border-rose-400/60 transition">
                                    <p class="text-rose-400 font-bold text-xs"><?= htmlspecialchars($vv['Code']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-span-12 lg:col-span-7">
            <div class="space-y-6">
                <!-- Page Builder Blocks or Default Slider -->
                <?php if (!empty($centerBlocks)): ?>
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="soft-shadow glass-effect rounded-2xl p-6 overflow-hidden border border-indigo-500/10"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="soft-shadow glass-effect rounded-2xl overflow-hidden border border-indigo-500/10 h-96 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-image text-5xl text-slate-400 mb-4 block"></i>
                            <p class="text-slate-400 text-sm">Gallery Slider</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Search Section -->
                <div class="soft-shadow glass-effect rounded-2xl p-6 border border-indigo-500/20">
                    <form method="GET" action="searchProduct.php" class="flex gap-2">
                        <input type="search" name="query" placeholder="Tìm kiếm sản phẩm..." class="flex-1 glass-effect rounded-xl px-4 py-3 text-sm placeholder-slate-400 text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" required>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold text-sm transition hover:shadow-lg">Tìm</button>
                    </form>
                </div>

                <!-- Top Products -->
                <?php if (empty($centerBlocks)): ?>
                    <?php
                        $db->setQuery("SELECT * FROM products ORDER BY Sold DESC LIMIT 8");
                        $popular = $db->excuteQuery();
                    ?>
                    <div class="space-y-4">
                        <h3 class="font-montserrat text-2xl font-bold text-slate-100 flex items-center gap-2">
                            <i class="fas fa-fire text-rose-400"></i> Top Sản Phẩm
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php while ($p = $popular->fetch_assoc()): ?>
                                <div class="group soft-shadow glass-effect rounded-2xl overflow-hidden border border-slate-700/30 hover:border-indigo-400/50 transition p-4 hover:deep-shadow">
                                    <div class="relative mb-4 h-40 overflow-hidden rounded-xl bg-slate-800 flex items-center justify-center">
                                        <img src="<?= $p['ImageSP'] ?>" alt="<?= htmlspecialchars($p['TenSP']) ?>" class="h-full w-full object-contain group-hover:scale-110 transition">
                                    </div>
                                    <p class="text-slate-300 font-semibold text-sm line-clamp-2 mb-2"><?= htmlspecialchars($p['TenSP']) ?></p>
                                    <p class="text-rose-400 font-bold text-lg mb-3"><?= number_format($p['GiaTien'],0,',','.') ?>đ</p>
                                    <div class="flex gap-2 text-xs">
                                        <a href="product_detail.php?id=<?= $p['MaSP'] ?>" class="flex-1 px-3 py-2 rounded-lg border border-indigo-500/50 text-indigo-400 hover:bg-indigo-500/20 transition text-center">Chi Tiết</a>
                                        <form method="post" action="controller/c_addToCart.php" style="display:contents;">
                                            <input type="hidden" name="product_id" value="<?= $p['MaSP'] ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white transition">Thêm</button>
                                        </form>
                                        <?php $fav = ($isLoggedIn) ? (new M_wishlist())->isFavorited($maKH, $p['MaSP']) : false; ?>
                                        <button type="button" class="px-3 py-2 rounded-lg border border-slate-600 text-slate-300 hover:text-rose-400 transition fav-btn" data-product-id="<?= $p['MaSP'] ?>" data-favorited="<?= $fav?1:0 ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="col-span-12 lg:col-span-3">
            <div class="space-y-4">
                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div class="soft-shadow glass-effect rounded-2xl p-4 border border-slate-700/30"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>

                <!-- Random Products -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-indigo-500/20">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-star text-amber-400"></i> Đề Xuất
                    </h6>
                    <div class="space-y-3">
                        <?php if($randomProducts && $randomProducts->num_rows > 0): while($rp = $randomProducts->fetch_assoc()): ?>
                            <div class="flex gap-3 p-3 rounded-lg hover:bg-slate-800/50 transition">
                                <img src="<?= $rp['ImageSP'] ?>" alt="<?= htmlspecialchars($rp['TenSP']) ?>" class="w-12 h-12 rounded-lg object-contain bg-slate-800">
                                <div class="flex-1 min-w-0">
                                    <a href="product_detail.php?id=<?= $rp['MaSP'] ?>" class="text-xs font-semibold text-slate-300 hover:text-indigo-400 line-clamp-2 block"><?= htmlspecialchars($rp['TenSP']) ?></a>
                                    <p class="text-rose-400 font-bold text-xs mt-1"><?= number_format($rp['GiaTien'],0,',','.') ?>đ</p>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- Hot Categories -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-slate-700/30">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-fire text-amber-400"></i> Danh Mục Hot
                    </h6>
                    <nav class="space-y-2">
                        <a href="searchProduct.php?query=phukien" class="block text-xs text-slate-400 hover:text-indigo-400 transition hover:translate-x-1"><i class="fas fa-chevron-right text-xs mr-2"></i> Phụ Kiện PC</a>
                        <a href="searchProduct.php?query=loa" class="block text-xs text-slate-400 hover:text-indigo-400 transition hover:translate-x-1"><i class="fas fa-chevron-right text-xs mr-2"></i> Âm Thanh</a>
                        <a href="searchProduct.php?query=smartwatch" class="block text-xs text-slate-400 hover:text-indigo-400 transition hover:translate-x-1"><i class="fas fa-chevron-right text-xs mr-2"></i> Smartwatch</a>
                    </nav>
                </div>
                
                <!-- Special Promo -->
                <div class="soft-shadow rounded-2xl p-4 border border-indigo-500/30 bg-gradient-to-br from-indigo-500/20 to-rose-500/20 text-center">
                    <i class="fas fa-crown text-3xl text-indigo-400 mb-2 block"></i>
                    <h6 class="font-montserrat font-bold text-slate-100 mb-1 text-sm">IT PRO MEMBER</h6>
                    <p class="text-xs text-slate-400 mb-3">Đặc quyền cho sinh viên ngành CNTT.</p>
                    <a href="signUp.php" class="inline-block px-6 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold text-xs transition">THAM GIA</a>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php if ($showBanner): ?>
    <div id="welcome-banner" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm z-40"></div>
        <div class="relative z-50 max-w-3xl w-[92%] soft-shadow glass-effect rounded-2xl overflow-hidden p-6 md:p-8 animate-scaleUp">
            <button id="close-banner" class="absolute top-3 right-3 text-slate-300 hover:text-white text-2xl">&times;</button>
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="w-full md:w-1/3 hidden md:block">
                    <img src="media/image/Slider/slider1_1.png" class="w-full h-40 object-cover rounded-lg" alt="banner">
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-montserrat font-bold text-slate-100 mb-2">Kazuhi ơi!</h3>
                    <p class="text-sm text-slate-300 mb-4">Hệ thống vừa cập nhật thêm linh kiện mới cho shop của bạn. Kiểm tra ngay nhé!</p>
                    <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-semibold" onclick="document.getElementById('welcome-banner').style.display='none'">KHÁM PHÁ NGAY</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        @keyframes scaleUp { from { transform: scale(0.96); opacity: 0 } to { transform: scale(1); opacity: 1 } }
        .animate-scaleUp { animation: scaleUp 0.35s ease-out; }
    </style>
<?php endif; ?>

<style>
    @keyframes slideIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .last-child-border-0:last-child { border-bottom: none !important; }
    .product-card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .transition { transition: all 0.3s ease; }
    .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<script>
    if(document.getElementById('close-banner')) {
        document.getElementById('close-banner').onclick = function() {
            document.getElementById('welcome-banner').style.display = 'none';
        }
    }
</script>

<?php include('template/productList.php'); ?>
<?php include('template/footer.php') ?>