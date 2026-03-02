<?php include('template/head.php'); ?>
<?php include('template/header.php');?>
<?php
  include_once('model/m_database.php');
  $db = new M_database();
  $maKH = $_SESSION['user_id'] ?? 0;
  if ($maKH <= 0) die("Vui lòng đăng nhập");

  $db->setQuery("SELECT * FROM account WHERE MaTK = $maKH");
  $user = $db->excuteQuery()->fetch_assoc();

  // Lấy lịch sử đơn hàng
  $db->setQuery("
      SELECT c.MaSP, c.SoLuong, c.GiaTien, c.NgayMua, c.State, p.TenSP, p.ImageSP
      FROM cart c
      JOIN products p ON c.MaSP = p.MaSP
      WHERE c.MaTK = $maKH
  ");
  $orders = $db->excuteQuery();
  $tongTien = 0;
  
  $db->setQuery("SELECT * FROM vouchers");
  $list_voucher = $db->excuteQuery();
  $db->close();
?>
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
        <div class="d-flex flex-column flex-md-row gap-4 w-100" style="max-width: 960px;">
            <section class="bg-white rounded shadow-sm p-4 flex-fill" aria-label="Payment method selection">
                <h2 class="fs-6 fw-semibold text-dark mb-3">Hình thức thanh toán</h2>
                <div class="d-flex flex-wrap gap-3 payment-methods">
                    <img src="./media/image/other/visa.png" alt="Visa credit card logo in blue" height="30"
                        width="80" />
                    <img src="./media/image/other/mastercard.png"
                        alt="MasterCard credit card logo with red and orange circles" height="30" width="80" />
                    <img src="./media/image/other/momo.png" alt="PayPal logo in blue shades" height="30" width="80" />
                    <img src="./media/image/other/bidv.jpg" alt="Carte Bancaire (CB) logo in green" height="30"
                        width="80" />
                    <img src="./media/image/other/vietcombank.png" alt="iDEAL payment logo in dark red" height="30"
                        width="80" />
                </div>
            </section>

            <section class="bg-white rounded shadow-sm p-4 flex-fill" aria-label="Order summary">
                <div class="d-flex justify-content-between text-secondary small mb-3" style="font-size: 0.7rem;">
                    <div>
                        <span class="border-bottom border-secondary pb-1 fw-semibold text-success">
                            Giỏ hàng <i class="fas fa-check"></i>
                        </span>
                    </div>
                    <div>Chi tiết thanh toán</div>
                    <div class="text-muted">Thanh toán thành công</div>
                </div>
                <h3 class="fw-semibold fs-6 text-dark mb-1">Đơn hàng</h3>
                <p class="text-secondary small mb-4">
                    Tổng số sản phẩm: <span class="fw-normal"><?php echo $orders->num_rows; ?></span>
                </p>

                <?php while ($row = $orders->fetch_assoc()): ?>
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo $row['ImageSP']; ?>" alt="<?php echo $row['TenSP']; ?>" class="product-img"
                        width="40" height="40" />
                    <div class="flex-grow-1">
                        <p class="product-name mb-0"><?php echo $row['TenSP']; ?></p>
                        <p class="product-price mb-0"><?php echo number_format($row['GiaTien'], 0, ',','.'); ?> đ</p>
                        <p class="product-qty mb-0">× <?php echo $row['SoLuong']; ?></p>
                    </div>
                    <?php 
              $tmp = $row['GiaTien'] * $row['SoLuong'];
              $tongTien += $tmp; 
            ?>
                    <div class="fw-semibold text-dark small"><?php $row['GiaTien'];?></div>
                </div>
                <?php endwhile; ?>

                <div class="d-flex justify-content-between text-secondary small mb-1">
                    <span>Phí vận chuyển</span>
                    <span>Free</span>
                </div>
                <div class="d-flex justify-content-between text-secondary small mb-4">
                    <?php $tax=$tongTien*0.001;?>
                    <span>Thuế</span>
                    <span><?php echo number_format($tax,0,',','.')?> đ</span>
                </div>

                <div class="d-flex justify-content-between fw-bold text-dark mb-4" style="font-size: 0.9rem;">
                    <span>Tổng cộng</span>
                    <span><?php echo number_format($tongTien+$tax, 0,',','.'); ?> đ</span>
                </div>
                <form action="controller/c_thanhToan.php" method="post">
                    <input type="hidden" name="maTK" value="<?php echo $user['MaTK']; ?>">
                    <input type="hidden" name="soTien" value="<?php echo $tongTien+$tax; ?>">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <input type="text" class="form-control me-2" id="voucherInput" placeholder="Nhập mã giảm giá">
                        <button type="button" class="btn btn-success" id="applyVoucherBtn">Enter</button>
                    </div>
                    <div id="voucherMessage" class="text-danger small mb-2"></div>


                    <div class="d-flex justify-content-between fw-bold text-dark mb-4" style="font-size: 0.9rem;">
                        <span>Sau giảm: </span>
                        <span id="discountedTotal"><?php echo number_format($tongTien+$tax, 0,',','.'); ?> đ</span>
                    </div>
                    <input type="hidden" name="soTien" id="soTienInput" value="<?php echo $tongTien+$tax; ?>">
                    <button type="submit" class="btn btn-secure w-100 mb-2">
                        Tiếp tục thanh toán
                    </button>
                </form>
                <a href="index.php" class="cancel-payment d-block text-center">Hủy thanh toán</a>
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
                    const discountPercent = parseFloat(data.discount);
                    const originalTotal = <?= $tongTien + $tax ?>;
                    const discounted = Math.round(originalTotal * (1 - discountPercent / 100));

                    document.getElementById('discountedTotal').textContent = discounted.toLocaleString(
                        'vi-VN') + ' đ';
                    document.getElementById('soTienInput').value = discounted;

                    messageDiv.textContent = 'Áp dụng mã thành công!';
                    messageDiv.classList.remove('text-danger');
                    messageDiv.classList.add('text-success');
                } else {
                    messageDiv.textContent = 'Mã giảm giá không hợp lệ.';
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