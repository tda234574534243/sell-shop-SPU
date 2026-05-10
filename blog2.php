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
    $pageSlug = 'blog2';
    
    // Kiểm tra quyền truy cập trang
    if (!$pageBuilder->canAccessPage($pageSlug)) {
        die("404 - Trang không tồn tại hoặc bạn không có quyền truy cập");
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

<style>
    #prev, #next {
        display: none;
    }
</style>

<main class="py-4">
    <div class="grid grid-cols-1 md:grid-cols-[16rem_1fr_16rem] gap-4 px-4 max-w-7xl mx-auto">
        <!-- Left Sidebar -->
        <aside class="hidden md:block">
            <div class="space-y-3">
                <?php if (!empty($leftBlocks)): foreach ($leftBlocks as $block): ?>
                    <div><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>

                <!-- Notifications Widget -->
                <div class="glass-effect soft-shadow rounded-2xl p-4 border border-slate-700/30">
                    <h6 class="font-bold mb-3 text-sm uppercase text-slate-200"><i class="fas fa-bell text-amber-400 mr-2"></i>Tin mới</h6>
                    <div class="space-y-2">
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <div class="pb-2 border-b border-slate-700/20 last:border-0">
                                <a href="notification_detail.php?id=<?= $s['id'] ?>" class="text-sm text-slate-300 hover:text-indigo-400 font-semibold line-clamp-2"><?= htmlspecialchars($s['Title']) ?></a>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- Vouchers Widget -->
                <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                    <div class="glass-effect soft-shadow rounded-2xl p-4 border border-slate-700/30">
                        <h6 class="font-bold mb-3 text-sm uppercase text-slate-200"><i class="fas fa-ticket-alt text-rose-400 mr-2"></i>Voucher</h6>
                        <div class="space-y-2">
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="p-2 rounded-lg bg-slate-900/40 border border-dashed border-slate-600/30">
                                    <strong class="text-emerald-400 text-xs block"><?= htmlspecialchars($vv['Code']) ?></strong>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="main__container">
                <?php if (!empty($centerBlocks)): ?>
                    <!-- Page Builder Blocks -->
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="mb-4"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Content -->
                    <div class="glass-effect soft-shadow rounded-2xl p-6 border border-indigo-500/20 bg-indigo-600/5">
                        <h4 class="font-montserrat text-2xl font-bold text-indigo-300 mb-2">blog2</h4>
                        <p class="text-slate-300 mb-3">Sử dụng Page Builder trong Admin để thêm nội dung cho trang này.</p>
                        <hr class="border-slate-700/30 my-3">
                        <p class="text-slate-400 text-sm mb-0">Vào <strong class="text-slate-200">Admin > Quản lý trang > blog2</strong> để bắt đầu chỉnh sửa.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="hidden md:block">
            <div class="space-y-3">
                <!-- Right Blocks -->
                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>
            </div>
        </aside>
    </div>
</main>

        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div class="mb-3"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include('template/footer.php') ?>
