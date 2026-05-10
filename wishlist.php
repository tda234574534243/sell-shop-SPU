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
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8" style="min-height:60vh;">
    <h3 class="text-2xl font-montserrat font-bold text-slate-100 mb-6">Sản phẩm yêu thích</h3>
    <?php if ($results && $results->num_rows>0): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while($r = $results->fetch_assoc()): ?>
                <div>
                    <div class="soft-shadow glass-effect rounded-2xl overflow-hidden flex flex-col h-full">
                        <div class="w-full h-40 bg-slate-900 flex items-center justify-center">
                            <img src="<?= $r['ImageSP'] ?>" class="object-contain h-full w-full p-3" alt="<?= htmlspecialchars($r['TenSP']) ?>">
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="font-semibold text-sm text-slate-100 mb-1 line-clamp-2"><?= htmlspecialchars($r['TenSP']) ?></div>
                            <div class="text-rose-400 font-bold text-sm mb-3"><?= number_format($r['GiaTien'],0,',','.') ?>đ</div>
                            <div class="mt-auto flex items-center justify-center gap-2">
                                <a href="product_detail.php?id=<?= $r['MaSP'] ?>" class="px-3 py-2 rounded-lg border border-indigo-500 text-indigo-300">Chi tiết</a>
                                <form method="post" action="controller/c_addToCart.php" style="display:inline-block;">
                                    <input type="hidden" name="product_id" value="<?= $r['MaSP'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white" type="submit">Thêm vào giỏ</button>
                                </form>
                                <button type="button" class="px-2 py-2 rounded-lg border border-slate-700 text-rose-400 fav-btn" data-product-id="<?= $r['MaSP'] ?>" data-favorited="1" title="Bỏ yêu thích">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-slate-400">Bạn chưa có sản phẩm yêu thích nào.</p>
    <?php endif; ?>
</div>

<?php include('template/footer.php'); ?>
