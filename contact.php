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
    $pageSlug = 'contact';
    
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

    body {
        font-family: 'Arial', sans-serif;
        background: #f4f7f6;
        margin: 0;
        padding: 0;
    }

    .contact-container {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .contact-container h2 {
        text-align: center;
        color: #ff6600;
        margin-bottom: 30px;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 12px;
        font-size: 16px;
        resize: none;
    }

    .contact-form button {
        background-color: #ff6600;
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .contact-form button:hover {
        background-color: #e65c00;
    }

    .contact-info {
        margin-top: 40px;
        text-align: center;
        color: #333;
    }

    .contact-info h3 {
        margin-bottom: 10px;
        color: #222;
    }

    .contact-info p {
        margin: 4px 0;
    }

    @media (max-width: 768px) {
        .contact-container {
            padding: 20px;
        }

        .contact-form input,
        .contact-form textarea {
            font-size: 15px;
        }
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
                    <!-- Default Banner + Form -->
                    <div class="slider-default mb-4 rounded-3 overflow-hidden shadow-sm">
                        <div class="slider" style="--width: 100px; --height: 50px; --quantity: 4;">
                            <div class="list">
                                <div class="item" style="--position: 1"><img src="./media/image/Slider/slider1_5.png" alt=""></div>
                                <div class="item" style="--position: 2"><img src="./media/image/Slider/slider1_6.png" alt=""></div>
                                <div class="item" style="--position: 3"><img src="./media/image/Slider/slider1_7.png" alt=""></div>
                                <div class="item" style="--position: 4"><img src="./media/image/Slider/slider1_8.png" alt=""></div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-container">
                        <h2>Liên hệ với PSHOP</h2>
                        <form class="contact-form" action="process_contact.php" method="POST">
                            <input type="text" name="name" placeholder="Họ và tên" required>
                            <input type="email" name="email" placeholder="Email của bạn" required>
                            <input type="text" name="subject" placeholder="Chủ đề">
                            <textarea name="message" rows="6" placeholder="Nội dung tin nhắn" required></textarea>
                            <button type="submit">Gửi tin nhắn</button>
                        </form>

                        <div class="contact-info">
                            <h3>Thông tin liên hệ</h3>
                            <p>📍 Địa chỉ: 123 Nguyễn Văn Linh, TP.HCM</p>
                            <p>📞 Hotline: 0909 999 999</p>
                            <p>✉️ Email: contact@pshop.vn</p>
                        </div>
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
