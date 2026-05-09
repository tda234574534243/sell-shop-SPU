<?php
    if (session_status() == PHP_SESSION_NONE) session_start();
    $maTK = $_SESSION['user_id'] ?? 0;
    if (!$maTK) {
        $_SESSION['toast'] = ['title'=>'Lỗi','message'=>'Vui lòng đăng nhập để xem yêu thích','type'=>'error'];
        header('Location: signIn.php'); exit;
    }
    include('template/head.php');
    include('template/header.php');
    include_once 'model/m_wishlist.php';
    include_once 'model/m_giohang.php';
    $mw = new M_wishlist();
    $results = $mw->getByUser($maTK);
?>
<div class="container py-5" style="min-height:60vh;">
    <h3 class="mb-3">Sản phẩm yêu thích</h3>
    <?php if ($results && $results->num_rows>0): ?>
        <div class="row g-3">
            <?php while($r = $results->fetch_assoc()): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <img src="<?= $r['ImageSP'] ?>" class="card-img-top p-2" style="object-fit:contain;height:160px;">
                        <div class="card-body d-flex flex-column text-center">
                            <div class="fw-bold small text-truncate-2"><?= htmlspecialchars($r['TenSP']) ?></div>
                            <div class="text-danger fw-bold small mb-2"><?= number_format($r['GiaTien'],0,',','.') ?>đ</div>
                            <div class="mt-auto d-flex gap-2 justify-content-center">
                                <a href="product_detail.php?id=<?= $r['MaSP'] ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                <form method="post" action="controller/c_addToCart.php">
                                    <input type="hidden" name="product_id" value="<?= $r['MaSP'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="btn btn-sm btn-primary" type="submit">Thêm vào giỏ</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-light fav-btn" data-product-id="<?= $r['MaSP'] ?>" data-favorited="1" title="Bỏ yêu thích">
                                    <i class="fas fa-heart text-danger"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Bạn chưa có sản phẩm yêu thích nào.</p>
    <?php endif; ?>
</div>

<?php include('template/footer.php'); ?>
