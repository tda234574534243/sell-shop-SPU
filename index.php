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
    $sideNotifs = $nm->getActive(5);
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
                    <?php foreach ($centerBlocks as $block): ?>
                        <div class="mb-4"><?= renderBlock($block) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="slider-default mb-4 rounded-3 overflow-hidden shadow-sm">
                        <div class="slider" style="--width: 100px; --height: 50px; --quantity: 10;">
                            <div class="list">
                                <?php for($i=1; $i<=10; $i++): ?>
                                    <div class="item" style="--position: <?= $i ?>"><img src="./media/image/Slider/slider1_<?= $i ?>.png" alt=""></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="search-section my-4">
                <form class="input-group shadow-sm" method="GET" action="searchProduct.php">
                    <input type="search" name="query" class="form-control form-control-lg border-primary" placeholder="Nhập tên linh kiện, thiết bị..." required>
                    <button class="btn btn-primary px-4 fw-bold" type="submit">TÌM KIẾM</button>
                </form>
            </div>

            <?php if (empty($centerBlocks)): ?>
                <?php
                    $db->setQuery("SELECT * FROM products ORDER BY Sold DESC LIMIT 8");
                    $popular = $db->excuteQuery();
                ?>
                <div class="popular-products py-3">
                    <h4 class="mb-3 fw-bold text-uppercase" style="font-size: 1.1rem;">🔥 Top sản phẩm bán chạy</h4>
                    <div class="row g-3">
                        <?php while ($p = $popular->fetch_assoc()): ?>
                            <div class="col-6 col-md-3">
                                <div class="card h-100 shadow-sm border-0 product-card-hover transition">
                                    <img src="<?= $p['ImageSP'] ?>" class="card-img-top p-2" alt="..." style="object-fit: contain; height: 160px;">
                                    <div class="card-body p-2 d-flex flex-column text-center">
                                        <p class="card-title small mb-1 fw-bold text-truncate-2"><?= htmlspecialchars($p['TenSP']) ?></p>
                                        <p class="text-danger small mb-2 fw-bold"><?= number_format($p['GiaTien'],0,',','.') ?>đ</p>
                                                <div class="d-flex gap-2 mt-auto justify-content-center align-items-center">
                                                    <a href="product_detail.php?id=<?= $p['MaSP'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">Chi tiết</a>
                                                    <form method="post" action="controller/c_addToCart.php" style="display:inline-block;">
                                                        <input type="hidden" name="product_id" value="<?= $p['MaSP'] ?>">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill">Thêm</button>
                                                    </form>
                                                    <?php $fav = ($isLoggedIn) ? (new M_wishlist())->isFavorited($maKH, $p['MaSP']) : false; ?>
                                                    <button type="button" class="btn btn-sm btn-light fav-btn" data-product-id="<?= $p['MaSP'] ?>" data-favorited="<?= $fav?1:0 ?>" title="Yêu thích">
                                                        <i class="fas fa-heart text-<?= $fav? 'danger':'muted' ?>"></i>
                                                    </button>
                                                </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <aside class="col-lg-2 d-none d-lg-block">
            <div class="side-promo">


                <div class="promo-card shadow-sm border-0 mb-3 p-3 rounded bg-white">
                    <h6 class="fw-bold mb-3 small text-uppercase"><i class="fas fa-bolt me-2 text-warning"></i>Có thể bạn thích</h6>
                    <div class="random-list">
                        <?php if($randomProducts && $randomProducts->num_rows > 0): while($rp = $randomProducts->fetch_assoc()): ?>
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom last-child-border-0">
                                <img src="<?= $rp['ImageSP'] ?>" class="rounded" style="width: 45px; height: 45px; object-fit: contain; background: #f8f9fa;">
                                <div class="ms-2" style="min-width: 0;">
                                    <a href="product_detail.php?id=<?= $rp['MaSP'] ?>" class="small fw-bold text-dark text-decoration-none text-truncate d-block" style="font-size: 11px;"><?= htmlspecialchars($rp['TenSP']) ?></a>
                                    <div class="text-danger fw-bold" style="font-size: 10px;"><?= number_format($rp['GiaTien'],0,',','.') ?>đ</div>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <div class="promo-card bg-white p-3 shadow-sm rounded mb-3 border-start border-4 border-info">
                    <h6 class="fw-bold small mb-2 text-uppercase text-info">Danh mục hot</h6>
                    <nav class="nav flex-column small">
                        <a class="nav-link p-1 text-dark" href="searchProduct.php?query=phukien" style="font-size: 12px;"><i class="fas fa-caret-right me-1 opacity-50"></i> Phụ kiện PC</a>
                        <a class="nav-link p-1 text-dark" href="searchProduct.php?query=loa" style="font-size: 12px;"><i class="fas fa-caret-right me-1 opacity-50"></i> Âm thanh</a>
                        <a class="nav-link p-1 text-dark" href="searchProduct.php?query=smartwatch" style="font-size: 12px;"><i class="fas fa-caret-right me-1 opacity-50"></i> Smartwatch</a>
                    </nav>
                </div>
                
                <div class="promo-card p-3 rounded shadow-sm border-0 text-white text-center" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
                    <i class="fas fa-user-shield mb-2" style="font-size: 1.5rem;"></i>
                    <h6 class="fw-bold mb-1 small">IT PRO MEMBER</h6>
                    <p style="font-size: 9px;" class="opacity-75 mb-2">Đặc quyền cho sinh viên ngành CNTT.</p>
                    <a href="signUp.php" class="btn btn-sm btn-light w-100 py-1 fw-bold text-primary rounded-pill" style="font-size: 10px;">THAM GIA</a>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php if ($showBanner): ?>
    <div id="welcome-banner" style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:2000;">
        <div class="banner-content shadow-lg" style="background:#fff;max-width:700px;width:90%;padding:30px;border-radius:20px;position:relative; animation: slideIn 0.5s ease-out;">
            <button id="close-banner" style="position:absolute;right:20px;top:15px;border:none;background:none;font-size:25px;cursor:pointer;color:#ccc;">&times;</button>
            <div class="row align-items-center">
                <div class="col-md-5 d-none d-md-block"><img src="media/image/Slider/slider1_1.png" class="img-fluid rounded shadow-sm"></div>
                <div class="col-md-7">
                    <h3 class="fw-bold text-primary">Kazuhi ơi!</h3>
                    <p class="text-muted small">Hệ thống vừa cập nhật thêm linh kiện mới cho shop của bạn. Kiểm tra ngay nhé!</p>
                    <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold small" onclick="document.getElementById('welcome-banner').style.display='none'">KHÁM PHÁ NGAY</button>
                </div>
            </div>
        </div>
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:-1; backdrop-filter: blur(3px);"></div>
    </div>
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