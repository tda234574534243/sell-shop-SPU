<?php include('template/head.php') ?>
<?php include('template/header.php') ?>
<?php include('template/toastMess.php') ?>

<?php
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    $db = new M_database();
    $db->setQuery("SELECT * FROM products WHERE MaSP = '$id'");
    $result = $db->excuteQuery();

    $product = $result ? $result->fetch_assoc() : null;
    $db->close();

    if (!$product) {
        echo "<div class='container py-5'><h3 class='text-danger'>Sản phẩm không tồn tại</h3></div>";
        include('template/footer.php');
        exit;
    }
?>

<div class="container py-5" style="min-height: 68vh;">
    <div class="row">
        <div class="col-md-5">
            <img src="<?= $product['ImageSP'] ?>" class="img-fluid rounded shadow" alt="<?= $product['TenSP'] ?>">
        </div>
        <div class="col-md-7">
            <h2><?= $product['TenSP'] ?></h2>
            <p class="text-muted">Phân loại: <?= $product['PhanLoai'] ?> | Ngày sản xuất: <?= $product['NSX'] ?></p>
            <h4 class="text-danger"><?= number_format($product['GiaTien'], 0, ',', '.') ?>đ</h4>
            
            <p><strong>Đã bán:</strong> <?= $product['Sold'] ?? 0 ?></p>
            <p><strong>Còn lại:</strong> <?= $product['SoLuong'] ?? 0 ?></p>

            <p class="mt-3">Thông tin sản phẩm: <?= nl2br($product['MoTa']) ?></p>

            <form method="post" action="controller/c_addToCart.php" class="mt-4">
                <input type="hidden" id="product_id" name="product_id" value="<?= $product['MaSP'] ?>" required>

                <div class="mb-3">
                    <label for="quantity" class="form-label me-2">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['SoLuong'] ?>" class="form-control w-25 d-inline-block" required>
                </div>

                <button type="submit" class="btn btn-success">🛒 Thêm vào giỏ hàng</button>
                <a href="index.php" class="btn btn-secondary ms-2">← Quay lại</a>
            </form>
        </div>
    </div>
</div>

<?php include('template/footer.php') ?>
