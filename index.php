<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>
<?php
    if (session_status() == PHP_SESSION_NONE) session_start();
    $showBanner = false;
    if (!empty($_SESSION['show_welcome_banner'])) {
        $showBanner = true;
        // unset so it only shows once after login
        unset($_SESSION['show_welcome_banner']);
    }
    
    // Load homepage customization
    include_once 'model/m_database.php';
    include_once 'model/m_pagebuilder.php';
    include_once 'helper/block_renderer.php';
    $pageBuilder = new M_pagebuilder();
    $leftBlocks = $pageBuilder->getBlocksBySection('homepage', 'left');
    $centerBlocks = $pageBuilder->getBlocksBySection('homepage', 'center');
    $rightBlocks = $pageBuilder->getBlocksBySection('homepage', 'right');
?>
<div class="container-fluid">
    <div class="row gx-4">
        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <!-- LEFT SIDEBAR BLOCKS -->
                <?php if (count($leftBlocks) > 0): ?>
                    <?php foreach ($leftBlocks as $block): ?>
                        <?= renderBlock($block) ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Left Sidebar Content -->
                    <?php
                        include_once 'model/m_notification.php';
                        include_once 'model/m_voucher.php';
                        $nm = new M_notification();
                        $vm = new M_voucher();
                        $sideNotifs = $nm->getActive(5);
                        $sideVouchers = $vm->getAll(5);
                    ?>
                    <?php if ($sideNotifs && $sideNotifs->num_rows>0): while($s = $sideNotifs->fetch_assoc()): ?>
                        <div class="promo-card">
                            <h6><?= htmlspecialchars($s['Title']) ?></h6>
                            <p><?= nl2br(htmlspecialchars(substr($s['Content'],0,120))) ?></p>
                            <a href="notification_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Xem</a>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="promo-card"><h6>Ưu đãi hôm nay</h6><p>Giảm giá &amp; khuyến mãi mới.</p></div>
                    <?php endif; ?>

                    <?php if ($sideVouchers && $sideVouchers->num_rows>0): ?>
                        <div class="promo-card mt-3">
                            <h6>Voucher nổi bật</h6>
                            <?php while($vv = $sideVouchers->fetch_assoc()): ?>
                                <div style="margin-bottom:8px;">
                                    <strong><?= htmlspecialchars($vv['Code']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($vv['Description'],0,60)) ?></small>
                                    <div><a href="voucher_detail.php?id=<?= $vv['id'] ?>" class="btn btn-sm btn-link">Chi tiết</a></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </aside>

        <main class="col-12 col-lg-8">
            <div class="main__container">
                <!-- CENTER BLOCKS -->
                <?php if (count($centerBlocks) > 0): ?>
                    <?php foreach ($centerBlocks as $block): ?>
                        <?= renderBlock($block) ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback: Default slider if no blocks -->
                    <div>
                        <div class="slider" style="
                            --width: 100px;
                            --height: 50px;
                            --quantity: 10;
                        ">
                            <div class="list">
                                <div class="item" style="--position: 1"><img src="./media/image/Slider/slider1_1.png" alt=""></div>
                                <div class="item" style="--position: 2"><img src="./media/image/Slider/slider1_2.png" alt=""></div>
                                <div class="item" style="--position: 3"><img src="./media/image/Slider/slider1_3.png" alt=""></div>
                                <div class="item" style="--position: 4"><img src="./media/image/Slider/slider1_4.png" alt=""></div>
                                <div class="item" style="--position: 5"><img src="./media/image/Slider/slider1_5.png" alt=""></div>
                                <div class="item" style="--position: 6"><img src="./media/image/Slider/slider1_6.png" alt=""></div>
                                <div class="item" style="--position: 7"><img src="./media/image/Slider/slider1_7.png" alt=""></div>
                                <div class="item" style="--position: 8"><img src="./media/image/Slider/slider1_8.png" alt=""></div>
                                <div class="item" style="--position: 9"><img src="./media/image/Slider/slider1_9.png" alt=""></div>
                                <div class="item" style="--position: 10"><img src="./media/image/Slider/slider1_10.png" alt=""></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">
                <?php if (!empty($rightBlocks)): ?>
                    <?php foreach ($rightBlocks as $block): ?>
                        <div class="promo-card mb-3">
                            <?php
                                $blockType = $block['type'] ?? 'text';
                                $blockData = $block['data'] ?? [];
                                
                                if ($blockType === 'banner' && !empty($blockData['image'])): ?>
                                    <img src="<?= htmlspecialchars($blockData['image']) ?>" alt="<?= htmlspecialchars($blockData['title'] ?? '') ?>" class="img-fluid rounded mb-2">
                                    <?php if (!empty($blockData['title'])): ?>
                                        <h5><?= htmlspecialchars($blockData['title']) ?></h5>
                                    <?php endif;
                                    if (!empty($blockData['description'])): ?>
                                        <p class="small"><?= htmlspecialchars($blockData['description']) ?></p>
                                    <?php endif;
                                
                                elseif ($blockType === 'text'): ?>
                                    <?php if (!empty($blockData['title'])): ?>
                                        <h5><?= htmlspecialchars($blockData['title']) ?></h5>
                                    <?php endif;
                                    if (!empty($blockData['content'])): ?>
                                        <p class="small"><?= htmlspecialchars($blockData['content']) ?></p>
                                    <?php endif;
                                
                                elseif ($blockType === 'html' && !empty($blockData['content'])): ?>
                                    <div class="html-block"><?= $blockData['content'] ?></div>
                                
                                elseif ($blockType === 'announcement'): ?>
                                    <div class="alert alert-info mb-0">
                                        <?php if (!empty($blockData['title'])): ?>
                                            <h6><?= htmlspecialchars($blockData['title']) ?></h6>
                                        <?php endif;
                                        if (!empty($blockData['message'])): ?>
                                            <p class="small mb-0"><?= htmlspecialchars($blockData['message']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default Right Sidebar Content -->
                    <div class="promo-card">
                        <h5>Top danh mục</h5>
                        <ul class="list-unstyled mb-0">
                            <li><a href="searchProduct.php?query=phukien">Phụ kiện</a></li>
                            <li><a href="searchProduct.php?query=loa">Loa &amp; Âm thanh</a></li>
                            <li><a href="searchProduct.php?query=smartwatch">Đồng hồ thông minh</a></li>
                        </ul>
                    </div>
                    <div class="promo-card mt-3">
                        <h6>Đăng ký nhận tin</h6>
                        <p>Nhận mã giảm giá qua email.</p>
                        <a href="signUp.php" class="btn btn-sm btn-outline-primary">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>
<?php if ($showBanner): ?>
    <div id="welcome-banner" style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:1050;">
        <div style="background:#fff;max-width:960px;width:92%;padding:24px;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.45);position:relative;z-index:1060;">
            <button id="close-banner" style="position:absolute;right:12px;top:12px;border:none;background:transparent;font-size:28px;line-height:1;cursor:pointer;">×</button>
            <div style="display:flex;gap:20px;align-items:center;">
                <img src="media/image/Slider/slider1_1.png" alt="banner" style="width:260px;height:260px;object-fit:cover;border-radius:6px;">
                <div>
                    <h2 style="margin-top:0;">Chào mừng, <?= htmlspecialchars($_SESSION['username'] ?? 'Khách') ?>!</h2>
                    <p style="font-size:16px;color:#444;">Cảm ơn bạn đã đăng nhập. Xem ngay các ưu đãi và sản phẩm mới nhất của chúng tôi.</p>
                    <div style="margin-top:12px;"><a href="index.php#" class="btn btn-primary">Khám phá</a></div>
                </div>
            </div>
        </div>
        <div id="banner-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:1055;"></div>
    </div>
    <script>
        (function(){
            var close = document.getElementById('close-banner');
            if (close) close.addEventListener('click', function(){
                var b = document.getElementById('welcome-banner'); if (b) b.style.display='none';
            });
        })();
    </script>
<?php endif; ?>
<!-- Prominent search area -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form class="input-group" method="GET" action="searchProduct.php">
                <input type="search" name="query" class="form-control form-control-lg" placeholder="Tìm sản phẩm, mã, phân loại..." required>
                <button class="btn btn-primary btn-lg" type="submit">Tìm kiếm</button>
            </form>
        </div>
    </div>
</div>

<!-- Popular products (top sold) -->
<?php if (count($pageBlocks) == 0): ?>
    <?php
        $db = new M_database();
        $db->setQuery("SELECT * FROM products ORDER BY Sold DESC LIMIT 8");
        $popular = $db->excuteQuery();
    ?>
    <?php if ($popular && $popular->num_rows > 0): ?>
    <div class="container py-4" id="products">
        <h3 class="mb-3">Sản phẩm nổi bật</h3>
        <div class="row">
            <?php while ($p = $popular->fetch_assoc()): ?>
                <div class="col-6 col-md-3 mb-3">
                    <div class="card h-100">
                        <img src="<?= $p['ImageSP'] ?>" class="card-img-top" alt="<?= htmlspecialchars($p['TenSP']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mb-1" style="font-size:14px"><?= htmlspecialchars($p['TenSP']) ?></h6>
                            <p class="text-danger fw-bold mb-2" style="font-size:14px"><?= number_format($p['GiaTien'],0,',','.') ?>đ</p>
                            <a href="product_detail.php?id=<?= $p['MaSP'] ?>" class="btn btn-sm btn-outline-primary mt-auto">Xem</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php include('template/productList.php'); ?>
<?php include('template/footer.php') ?>
