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
    $pageSlug = 'faq';
    
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

<div class="container-fluid py-4">
    <div class="row gx-4">
        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <?php if (!empty($leftBlocks)): foreach ($leftBlocks as $block): ?>
                    <div class="mb-3"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>

                <div class="system-widgets mt-3">
                    <div class="promo-card mb-3 p-3 shadow-sm rounded bg-white border-top border-warning border-3">
                        <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-bell text-warning me-2"></i>Tin mới</h6>
                        <?php if ($sideNotifs && $sideNotifs->num_rows > 0): while($s = $sideNotifs->fetch_assoc()): ?>
                            <div class="mb-2 border-bottom pb-2 last-child-border-0">
                                <a href="notification_detail.php?id=<?= $s['id'] ?>" class="text-decoration-none text-dark small fw-bold d-block text-truncate"><?= htmlspecialchars($s['Title']) ?></a>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>

                    <?php if ($sideVouchers && $sideVouchers->num_rows > 0): ?>
                        <div class="promo-card p-3 shadow-sm rounded bg-white border-top border-danger border-3">
                            <h6 class="fw-bold mb-2 small text-uppercase"><i class="fas fa-ticket-alt text-danger me-2"></i>Voucher</h6>
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div class="mb-2 p-2 rounded bg-light border border-dashed">
                                    <strong class="text-success small d-block"><?= htmlspecialchars($vv['Code']) ?></strong>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="col-12 col-lg-8">
            <div class="main__container">
                <?php if (!empty($centerBlocks)): ?>
                    <!-- Page Builder Blocks -->
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="mb-4"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Content -->
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Câu hỏi thường gặp</h4>
                        <p>Sử dụng Page Builder trong Admin để thêm nội dung cho trang này.</p>
                        <hr>
                        <p class="mb-0">Vào <strong>Admin > Quản lý trang > Câu hỏi thường gặp</strong> để bắt đầu chỉnh sửa.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <div class="promo-card shadow-sm border-0 mb-3 bg-white p-3 rounded border-start border-4 border-primary">
                    <h6 class="fw-bold text-primary small text-uppercase"><i class="fas fa-shopping-basket me-2"></i>Giỏ hàng</h6>
                    <div class="cart-status mt-2">
                        <?php if($totalCartQty > 0): ?>
                            <p class="small mb-2">Bạn đang có <strong class="text-danger"><?= $totalCartQty ?></strong> món.</p>
                            <a href="cart.php" class="btn btn-sm btn-primary w-100 py-1 rounded-pill" style="font-size: 11px;">THANH TOÁN</a>
                        <?php else: ?>
                            <p class="text-muted small mb-0" style="font-size: 11px;">Chưa có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($rightBlocks)): foreach ($rightBlocks as $block): ?>
                    <div class="mb-3"><?= renderBlock($block) ?></div>
                <?php endforeach; endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include('template/footer.php') ?>
