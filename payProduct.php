<?php
// Start session and load all data BEFORE any HTML output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once('model/m_database.php');
$db = new M_database();
$maKH = $_SESSION['user_id'] ?? 0;

if ($maKH <= 0) {
    $_SESSION['toast'] = [
        'title' => 'Lỗi',
        'message' => 'Vui lòng đăng nhập để thanh toán',
        'type' => 'error'
    ];
    header('Location: signIn.php');
    exit;
}

// Get user info
$db->setQuery("SELECT * FROM account WHERE MaTK = $maKH");
$user = $db->excuteQuery()->fetch_assoc();

if (!$user) {
    $_SESSION['toast'] = [
        'title' => 'Lỗi',
        'message' => 'Không tìm thấy thông tin tài khoản',
        'type' => 'error'
    ];
    header('Location: index.php');
    exit;
}

// Get cart items
$db->setQuery("
    SELECT c.MaSP, c.SoLuong, c.GiaTien, c.NgayMua, c.State, p.TenSP, p.ImageSP
    FROM cart c
    JOIN products p ON c.MaSP = p.MaSP
    WHERE c.MaTK = $maKH
");
$orders = $db->excuteQuery();
$tongTien = 0;

// Get vouchers list
$db->setQuery("SELECT * FROM vouchers");
$list_voucher = $db->excuteQuery();
$db->close();
?>
<?php include('template/head.php'); ?>
<?php include('template/header.php');?>
<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #f5f7f6;
    width: 100%;
    height: 100vh;
}

main {
    min-height: 75vh;
}

.payment-methods img {
    max-height: 30px;
    object-fit: contain;
    cursor: pointer;
}

.payment-methods img:active {
    border: 2px solid #047857;
}

.payment-methods img:not(:last-child) {
    margin-right: 1rem;
}

.order-summary .product-img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 0.25rem;
    margin-right: 1rem;
}

.order-summary .product-name {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0;
}

.order-summary .product-qty {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0;
}

.btn-secure {
    background-color: #047857;
    border-color: #047857;
    font-weight: 600;
    font-size: 0.875rem;
}

.btn-secure:hover,
.btn-secure:focus {
    background-color: #065f46;
    border-color: #065f46;
}

a.cancel-payment {
    font-weight: 700;
    font-size: 0.75rem;
    color: #374151;
    text-decoration: none;
    cursor: pointer;
}

a.cancel-payment:hover {
    text-decoration: underline;
}
</style>

<body>
    <main class="d-flex justify-content-center py-4 px-3">
        <div class="w-100" style="max-width:960px;">
            <section class="bg-white rounded shadow-sm p-4" aria-label="Cart list">
                <h2 class="fs-5 fw-bold mb-3">Giỏ hàng của bạn</h2>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <div class="list-group mb-3">
                        <?php while ($row = $orders->fetch_assoc()):
                            $tmp = $row['GiaTien'] * $row['SoLuong'];
                            $tongTien += $tmp;
                        ?>
                            <div class="list-group-item d-flex align-items-center">
                                <img src="<?= htmlspecialchars($row['ImageSP']) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px;margin-right:12px;">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($row['TenSP']) ?></div>
                                    <div class="text-muted small">Giá: <?= number_format($row['GiaTien'],0,',','.') ?> đ × <?= $row['SoLuong'] ?></div>
                                </div>
                                <div class="text-end fw-bold"><?= number_format($tmp,0,',','.') ?> đ</div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">Tổng cộng</div>
                        <div class="fw-bold"><?= number_format($tongTien,0,',','.') ?> đ</div>
                    </div>
                    <p class="small text-muted">Trang này chỉ hiển thị danh sách sản phẩm trong giỏ. Thao tác thanh toán sẽ ở bước tiếp theo.</p>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">Giỏ hàng đang trống.</div>
                <?php endif; ?>
                <a href="index.php" class="btn btn-sm btn-secondary">Tiếp tục mua sắm</a>
                <a href="payProduct.php?action=checkout" class="btn btn-sm btn-primary ms-2">Tiến hành thanh toán</a>
            </section>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('applyVoucherBtn').addEventListener('click', function() {
        const voucherCode = document.getElementById('voucherInput').value.trim();
        const messageDiv = document.getElementById('voucherMessage');

        if (voucherCode === '') {
            messageDiv.textContent = 'Vui lòng nhập mã giảm giá.';
            messageDiv.classList.remove('text-success');
            messageDiv.classList.add('text-danger');
            return;
        }
        fetch('model/m_checkVoucher.php?voucher=' + encodeURIComponent(voucherCode))
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.valid) {
                    const originalTotal = <?= $tongTien + $tax ?>;
                    let discounted = originalTotal;
                    
                    // Kiểm tra giảm theo percentage
                    if (data.discountPercent && data.discountPercent > 0) {
                        discounted = Math.round(originalTotal * (1 - data.discountPercent / 100));
                    }
                    // Kiểm tra giảm theo số tiền
                    else if (data.discountAmount && data.discountAmount > 0) {
                        discounted = Math.round(originalTotal - data.discountAmount);
                    }
                    
                    // Đảm bảo số tiền không âm
                    if (discounted < 0) discounted = 0;

                    document.getElementById('discountedTotal').textContent = discounted.toLocaleString(
                        'vi-VN') + ' đ';
                    document.getElementById('soTienInput').value = discounted;

                    messageDiv.textContent = data.message || 'Áp dụng mã thành công!';
                    messageDiv.classList.remove('text-danger');
                    messageDiv.classList.add('text-success');
                    
                    // Lưu voucher code vào hidden input
                    document.getElementById('voucherCodeInput').value = voucherCode;
                    
                    // Disable voucherInput và button để tránh áp dụng nhiều lần
                    document.getElementById('voucherInput').disabled = true;
                    document.getElementById('applyVoucherBtn').disabled = true;
                } else {
                    messageDiv.textContent = data.message || 'Mã giảm giá không hợp lệ.';
                    messageDiv.classList.remove('text-success');
                    messageDiv.classList.add('text-danger');
                }
            })
            .catch(err => {
                console.error('Lỗi:', err);
                messageDiv.textContent = 'Đã xảy ra lỗi khi kiểm tra mã.';
                messageDiv.classList.remove('text-success');
                messageDiv.classList.add('text-danger');
            });
    });
    </script>

</body>
<?php include('template/footer.php') ?>