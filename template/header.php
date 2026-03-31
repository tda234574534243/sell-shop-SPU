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
?>


<nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php" style="font-style:normal">Sup3rDup3rShop</a>
        <form class="form-inline" method="GET" action="searchProduct.php">
            <input class="searchbar d-block d-lg-none form-control mr-sm-2" type="search" name="query" placeholder="Bạn cần tìm gì?" aria-label="Search" required>
        </form>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                 <li class="nav-item">
                    <form class="form-inline" method="GET" action="searchProduct.php">
                        <input class="searchbar form-control mr-sm-2" type="search" name="query" placeholder="Search" aria-label="Search" required>
                    </form>
                </li>
                <li class="nav-item <?= ($currentPage === 'index.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item <?= ($currentPage === 'introduce.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="introduce.php">Giới thiệu</a>
                </li>
                <li class="nav-item <?= ($currentPage === 'contact.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="contact.php">Liên hệ</a>
                </li>

                <?php if ($isLoggedIn): ?>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Quản lý</a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="admin/page-builder.php"><i class="fas fa-wand-magic-sparkles"></i> Page Builder</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/analystic_product.php">Sản phẩm</a></li>
                                <li><a class="dropdown-item" href="admin/analystic_customer.php">Khách hàng</a></li>
                                <li><a class="dropdown-item" href="admin/notifications.php">Thông báo</a></li>
                                <li><a class="dropdown-item" href="admin/vouchers.php">Voucher</a></li>
                                <li><a class="dropdown-item" href="admin/statistic_order.php">Đơn hàng</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item cart-icon d-none d-lg-block">
                            <a class="nav-link" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-basket2" viewBox="0 0 16 16">
                                <path d="M4 10a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0zm3 0a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0zm3 0a1 1 0 1 1 2 0v2a1 1 0 0 1-2 0z"/>
                                <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-.623l-1.844 6.456a.75.75 0 0 1-.722.544H3.69a.75.75 0 0 1-.722-.544L1.123 8H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.243a.5.5 0 0 1 .686-.172zM2.163 8l1.714 6h8.246l1.714-6z"/>
                            </svg>
                            </a>
                        </li>
                        <li class="nav-item cart-sub-icon d-block d-lg-none">
                         <a class="nav-link" href="#">Giỏ hàng</a>
                        </li>
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                                    <path d="M8 16a2 2 0 0 0 1.985-1.75H6.015A2 2 0 0 0 8 16z"/>
                                    <path d="M8 1a4 4 0 0 0-4 4v2.086l-.707.707A1 1 0 0 0 3 10h10a1 1 0 0 0 .707-1.707L13 7.086V5a4 4 0 0 0-4-4z"/>
                                </svg>
                                <?php if ($notifCount > 0): ?><span class="badge bg-danger rounded-pill position-absolute" style="top:-6px;right:-6px;font-size:11px;"><?= $notifCount>99? '99+': $notifCount ?></span><?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width:320px;">
                                <li class="dropdown-header">Thông báo mới</li>
                                <?php if ($notifList && $notifList->num_rows>0): while($n = $notifList->fetch_assoc()): ?>
                                    <li><a class="dropdown-item" href="/sell-shop-SPU/notification_detail.php?id=<?= $n['id'] ?>"><?= htmlspecialchars($n['Title']) ?> <br><small class="text-muted"><?= substr(strip_tags($n['Content']),0,60) ?></small></a></li>
                                <?php endwhile; else: ?>
                                    <li class="dropdown-item text-muted">Không có thông báo</li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="admin/notifications.php">Quản lý thông báo</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <?php $hdrAvatar = ($currentAccount && !empty($currentAccount['Avatar'])) ? $currentAccount['Avatar'] : 'media/image/avatars/default.png'; ?>
                        <img src="<?= htmlspecialchars($hdrAvatar) ?>" alt="avatar" style="width:32px;height:32px;object-fit:cover;border-radius:50%;margin-right:8px;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user.php">Thông tin tài khoản</a></li>
                            <li><a class="dropdown-item" href="controller/c_logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <li class="nav-item d-block d-lg-none">
                        <a class="nav-link" href="user.php">Thông tin tài khoản</a>
                    </li>
                    <li class="nav-item d-block d-lg-none">
                         <a class="nav-link" href="controller/c_logout.php">Đăng xuất</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item <?= ($currentPage === 'signup.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="signup.php">Đăng ký</a>
                    </li>
                    <li class="nav-item <?= ($currentPage === 'signin.php') ? 'active' : '' ?>">
                        <a class="nav-link" href="signin.php">Đăng nhập</a>
                    </li>
                <?php endif; ?>
            </ul>
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
