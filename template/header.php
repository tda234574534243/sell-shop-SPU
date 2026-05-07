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

    // load current user's account info (for avatar)
    $currentAccount = null;
    if ($isLoggedIn) {
        $accModel = new M_account();
        $accRes = $accModel->getAccount($maKH);
        if ($accRes && $accRes->num_rows > 0) $currentAccount = $accRes->fetch_assoc();
    }

    // notifications for header bell
    include_once(__DIR__ . '/../model/m_notification.php');
    $notifModel = new M_notification();
    $notifCount = $notifModel->countActive();
    $notifList = $notifModel->getActive(5);

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


<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="/sell-shop-SPU/media/image/other/logo.png" alt="logo" style="height:36px;object-fit:contain;margin-right:10px;" onerror="this.style.display='none'">
            <span class="brand-text">Sup3rDup3rShop</span>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item <?= ($currentPage === 'index.php') ? 'active' : '' ?>"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item <?= ($currentPage === 'introduce.php') ? 'active' : '' ?>"><a class="nav-link" href="introduce.php">Giới thiệu</a></li>
                <li class="nav-item <?= ($currentPage === 'contact.php') ? 'active' : '' ?>"><a class="nav-link" href="contact.php">Liên hệ</a></li>
                <?php if ($isLoggedIn && $isAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Quản lý</a>
                        <div class="dropdown-menu" aria-labelledby="adminDropdown2">
                            <a class="dropdown-item" href="admin/page-builder.php"><i class="fas fa-wand-magic-sparkles"></i> Page Builder</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="admin/analystic_product.php">Sản phẩm</a>
                            <a class="dropdown-item" href="admin/analystic_customer.php">Khách hàng</a>
                            <a class="dropdown-item" href="admin/notifications.php">Thông báo</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>

            <form class="form-inline d-none d-lg-flex mr-3" method="GET" action="searchProduct.php">
                <input class="form-control" type="search" name="query" placeholder="Bạn cần tìm gì?" aria-label="Search">
            </form>

            <ul class="navbar-nav ml-auto align-items-center">
                <!-- Wishlist -->
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="wishlist.php" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <?php if ($isLoggedIn) { include_once(__DIR__ . '/../model/m_wishlist.php'); $mw = new M_wishlist(); $ws = $mw->countByUser($maKH); if ($ws>0) echo '<span class="badge badge-danger rounded-pill" style="position:absolute;top:-6px;right:-6px;font-size:11px;">'.($ws>99?'99+':$ws).'</span>'; } ?>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link position-relative" href="#" id="notifDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Thông báo">
                        <i class="fas fa-bell"></i>
                        <?php if ($notifCount>0) echo '<span class="badge badge-danger rounded-pill" style="position:absolute;top:-6px;right:-6px;font-size:11px;">'.($notifCount>99?'99+':$notifCount).'</span>'; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notifDropdown2" style="min-width:320px;">
                        <h6 class="dropdown-header">Thông báo mới</h6>
                        <?php if ($notifList && $notifList->num_rows>0): while($n = $notifList->fetch_assoc()): ?>
                            <a class="dropdown-item" href="/sell-shop-SPU/notification_detail.php?id=<?= $n['id'] ?>"><?= htmlspecialchars($n['Title']) ?><br><small class="text-muted"><?= substr(strip_tags($n['Content']),0,60) ?></small></a>
                        <?php endwhile; else: ?>
                            <div class="dropdown-item text-muted">Không có thông báo</div>
                        <?php endif; ?>
                        <?php if ($isAdmin) echo '<div class="dropdown-divider"></div><a class="dropdown-item text-center" href="admin/notifications.php">Quản lý thông báo</a>'; ?>
                    </div>
                </li>

                <!-- Cart -->
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="payProduct.php" title="Giỏ hàng">
                        <i class="fas fa-shopping-basket"></i>
                        <?php if ($totalCartQty > 0): ?><span class="badge badge-danger rounded-pill" style="position:absolute;top:-6px;right:-6px;font-size:11px;"><?php echo $totalCartQty>99? '99+': $totalCartQty; ?></span><?php endif; ?>
                    </a>
                </li>

                <!-- User / Auth -->
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php $hdrAvatar = ($currentAccount && !empty($currentAccount['Avatar'])) ? $currentAccount['Avatar'] : 'media/image/avatars/default.png'; ?>
                            <img src="<?= htmlspecialchars($hdrAvatar) ?>" alt="avatar" style="width:36px;height:36px;object-fit:cover;border-radius:50%;margin-right:8px;">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown2">
                            <a class="dropdown-item" href="user.php">Thông tin tài khoản</a>
                            <a class="dropdown-item" href="wishlist.php">Yêu thích</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="controller/c_logout.php">Đăng xuất</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="signin.php">Đăng nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="signup.php">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


<div class="cartTab">
    <header class="cartTab-header">
        <span class="close-cartTab">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
            </svg>
        </span>
    </header>
    <form action="/sell-shop-SPU/payProduct.php" method="POST">
    <div class="listCart">
        <?php if (count($cartItems) > 0): ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-details">
                        <p><?= htmlspecialchars($item['TenSP']) ?></p>
                        <p>Giá: $<?= number_format($item['GiaTien'], 2) ?></p>
                        <p>Số lượng: <?= $item['SoLuong'] ?></p>
                    </div>
                    <?php if (isset($item['MaSP'])): ?>
                        <a href="controller/c_removeCart.php?action=remove&id=<?= $item['MaSP'] ?>&return_url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="remove-item">Xóa</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="cart-item">Giỏ hàng đang trống.</p>
        <?php endif; ?>
    </div>
    <?php if (count($cartItems) > 0): ?>
    <!-- Add Checkout Button -->
    <div class="checkout-section">
        <button class="checkout-button">Thanh Toán</button>
    </div>
    <?php endif; ?>
    </form>
</div>
