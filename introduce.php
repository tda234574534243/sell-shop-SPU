<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    // Load page builder
    include_once 'model/m_pagebuilder.php';
    include_once 'model/m_database.php';
    include_once 'model/m_notification.php';
    include_once 'model/m_voucher.php';
    include_once 'helper/block_renderer.php';

    $db = new M_database();
    $pageBuilder = new M_pagebuilder();
    $nm = new M_notification();
    $vm = new M_voucher();

    // Page slug
    $pageSlug = 'introduce';
    
    // Kiểm tra quyền truy cập trang
    if (!$pageBuilder->canAccessPage($pageSlug)) {
        die('404 - Trang không tồn tại hoặc bạn không có quyền truy cập');
    }
    
    // Load blocks từ page builder
    $leftBlocks = $pageBuilder->getBlocksBySection($pageSlug, 'left');
    $centerBlocks = $pageBuilder->getBlocksBySection($pageSlug, 'center');
    $rightBlocks = $pageBuilder->getBlocksBySection($pageSlug, 'right');
    
    // Default data
    $sideNotifs = $nm->getActive(5);
    $sideVouchers = $vm->getAll(5);
    
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
                <?php if (!empty($centerBlocks)): ?>
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="soft-shadow glass-effect rounded-2xl p-6 overflow-hidden border border-indigo-500/10"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Content -->
                    <div class="soft-shadow glass-effect rounded-2xl p-6 border border-indigo-500/20">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="fas fa-info-circle text-3xl text-indigo-400"></i>
                            <h2 class="font-montserrat text-2xl font-bold text-slate-100">Giới Thiệu Về Chúng Tôi</h2>
                        </div>
                        <p class="text-slate-300 mb-4">Sử dụng Page Builder trong Admin để thêm nội dung cho trang này.</p>
                        <p class="text-slate-400 text-sm">
                            <i class="fas fa-arrow-right text-indigo-400 mr-2"></i>
                            Vào <strong>Admin > Quản lý trang > Giới thiệu</strong> để bắt đầu chỉnh sửa.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="col-span-12 lg:col-span-3">
            <div class="space-y-4">
                <!-- Cart Widget -->
                <div class="soft-shadow glass-effect rounded-2xl p-4 border border-indigo-500/20">
                    <h6 class="font-montserrat font-bold text-slate-100 mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-indigo-400"></i> Giỏ Hàng
                    </h6>
                    <div class="space-y-3">
                        <?php if($totalCartQty > 0): ?>
                            <p class="text-sm text-slate-300 mb-2">
                                Bạn đang có <strong class="text-rose-400"><?= $totalCartQty ?></strong> sản phẩm.
                            </p>
                            <a href="cart.php" class="block px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-center font-semibold text-sm transition">
                                Thanh Toán
                            </a>
                        <?php else: ?>
                            <p class="text-xs text-slate-400">Chưa có sản phẩm trong giỏ hàng.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div class="soft-shadow glass-effect rounded-2xl p-4 border border-slate-700/30"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include('template/footer.php') ?>
