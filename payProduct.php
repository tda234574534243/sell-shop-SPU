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

// Calculate total first
if ($orders) {
    $orders->data_seek(0); // Reset pointer
    while ($row = $orders->fetch_assoc()) {
        $tongTien += $row['GiaTien'] * $row['SoLuong'];
    }
    $orders->data_seek(0); // Reset pointer for later use
    
    // Load shipping config and determine fee (apply when any item unit price > threshold)
    $configuredFee = 0.0;
    $configuredThreshold = 10000000;
    $shippingConfigPath = __DIR__ . '/public/DATA/shipping.json';
    if (file_exists($shippingConfigPath)) {
        $raw = file_get_contents($shippingConfigPath);
        $j = json_decode($raw, true);
        if (is_array($j)) {
            if (isset($j['threshold'])) $configuredThreshold = floatval($j['threshold']);
            if (isset($j['fee'])) $configuredFee = floatval($j['fee']);
        }
    }

    // Determine whether to apply fee based on per-item unit price
    $applyFee = false;
    if ($orders && $orders->num_rows > 0 && $configuredFee > 0) {
        $orders->data_seek(0);
        while ($r = $orders->fetch_assoc()) {
            $unit = floatval($r['GiaTien'] ?? 0);
            if ($unit > $configuredThreshold) {
                $applyFee = true;
                break;
            }
        }
        $orders->data_seek(0); // reset for rendering
    }
    $shippingFee = $applyFee ? $configuredFee : 0.0;
}

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
                        ?>
                            <div class="list-group-item d-flex align-items-center cart-line" data-product-id="<?= $row['MaSP'] ?>" data-price="<?= $row['GiaTien'] ?>">
                                <div class="me-3">
                                    <button class="remove-cart-btn" data-product-id="<?= $row['MaSP'] ?>" title="Xóa" aria-label="Xóa sản phẩm">&times;</button>
                                </div>
                                <img src="<?= htmlspecialchars($row['ImageSP']) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px;margin-right:12px;">
                                <div class="flex-grow-1">
                                        <div class="product-name mb-1"><?= htmlspecialchars($row['TenSP']) ?></div>
                                        <div class="text-muted small">Giá: <?= number_format($row['GiaTien'],0,',','.') ?> đ</div>
                                </div>
                                    <div class="d-flex align-items-center">
                                        <input type="number" min="0" class="form-control form-control-sm cart-qty-input" style="width:80px;" value="<?= $row['SoLuong'] ?>" data-product-id="<?= $row['MaSP'] ?>">
                                    </div>
                                    <div class="fw-bold text-end ms-3 item-total"><?= number_format($tmp,0,',','.') ?> đ</div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-muted">Tổng cộng</div>
                        <div id="orderTotal" class="fw-bold"><?= number_format($tongTien,0,',','.') ?> đ</div>
                    </div>
                    <p class="small text-muted">Trang này chỉ hiển thị danh sách sản phẩm trong giỏ. Thao tác thanh toán sẽ ở bước tiếp theo.</p>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">Giỏ hàng đang trống.</div>
                <?php endif; ?>
                <a href="index.php" class="btn btn-sm btn-secondary">Tiếp tục mua sắm</a>
                <a href="payProduct.php?action=checkout" class="btn btn-sm btn-primary ms-2">Tiến hành thanh toán</a>
            </section>

            <?php if (isset($_GET['action']) && $_GET['action'] === 'checkout'): ?>
            <section class="bg-white rounded shadow-sm p-4 mt-4" aria-label="Checkout">
                <h2 class="fs-5 fw-bold mb-3">Thanh toán</h2>

                <!-- Voucher Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Mã giảm giá</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" id="voucherInput" class="form-control" placeholder="Nhập mã voucher" maxlength="50">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="applyVoucherBtn" class="btn btn-outline-primary w-100">Áp dụng</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="clearVoucherBtn" class="btn btn-outline-secondary w-100">Xóa</button>
                            </div>
                        </div>
                        <div id="voucherMessage" class="mt-2 small"></div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Tóm tắt đơn hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tổng tiền hàng:</span>
                            <span id="originalTotal"><?= number_format($tongTien, 0, ',', '.') ?> đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Giảm giá:</span>
                            <span id="discountAmount">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí ship:</span>
                            <span id="shippingFeeDisplay"><?php echo number_format($shippingFee,0,',','.'); ?> đ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Tổng thanh toán:</span>
                            <span id="finalTotal"><?= number_format($tongTien + $shippingFee, 0, ',', '.') ?> đ</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="controller/c_thanhToan.php" method="POST" id="paymentForm">
                    <input type="hidden" name="soTien" id="soTienInput" value="<?= $tongTien + $shippingFee ?>">
                    <input type="hidden" name="voucherCode" id="voucherCodeInput" value="">

                    <!-- Payment method selection -->
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="pm_local" value="local" checked>
                                <label class="form-check-label" for="pm_local">Thanh toán tại chỗ / COD</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="paymentMethod" id="pm_vnpay" value="vnpay">
                                <label class="form-check-label" for="pm_vnpay">Thanh toán bằng VNPAY</label>
                            </div>
                        </div>
                    </div>

                    <!-- COD confirmation (required when COD selected) -->
                    <div id="codConfirmation" class="mb-3" style="display:none;">
                        <label class="form-label fw-bold">Xác nhận COD</label>
                        <div class="form-text mb-2">Để đảm bảo bạn cam kết thanh toán bằng tiền mặt khi nhận hàng, vui lòng đánh dấu và nhập dòng xác nhận dưới đây.</div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cod_confirm_checkbox" name="cod_confirm" value="1">
                            <label class="form-check-label" for="cod_confirm_checkbox">Tôi cam kết sẽ thanh toán bằng tiền mặt (COD) khi nhận hàng.</label>
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <img id="codCaptchaImg" src="captcha.php?ts=<?= time() ?>" alt="captcha" style="height:50px;border:1px solid #ddd;border-radius:4px;">
                            <button type="button" id="reloadCaptcha" class="btn btn-sm btn-outline-secondary">Làm mới</button>
                        </div>
                        <div class="mt-2">
                            <input type="text" id="cod_captcha_input" name="cod_captcha" class="form-control" placeholder="Nhập ký tự trong ảnh" maxlength="10">
                        </div>
                        <div id="codConfirmMessage" class="small text-danger mt-1" style="display:none;"></div>
                    </div>

                    <div id="vnpayOptions" class="mb-3" style="display:none;">
                        <label class="form-label">Chọn phương thức VNPAY (tùy chọn)</label>
                        <select id="vnpayBankCode" name="bankCode" class="form-select" style="max-width:360px;">
                            <option value="">Cổng VNPAY (mặc định)</option>
                            <option value="VNPAYQR">VNPAYQR</option>
                            <option value="VNBANK">Ngân hàng nội địa</option>
                            <option value="INTCARD">Thẻ quốc tế</option>
                        </select>
                        <div class="mt-2">
                            <label class="form-label me-2">Ngôn ngữ:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="vnpayLang" id="vnpayLang_vn" value="vn" checked>
                                <label class="form-check-label" for="vnpayLang_vn">Tiếng Việt</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="vnpayLang" id="vnpayLang_en" value="en">
                                <label class="form-check-label" for="vnpayLang_en">English</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="payNowBtn" class="btn btn-success btn-secure flex-fill">
                            <i class="fas fa-credit-card me-2"></i>Thanh toán ngay
                        </button>
                        <a href="payProduct.php" class="btn btn-outline-secondary">Quay lại giỏ hàng</a>
                    </div>
                </form>

                <!-- Note: VNPAY fields are submitted to controller/c_thanhToan.php when VNPAY selected -->
            </section>
            <?php endif; ?>

        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Voucher functionality
    const SHIPPING_FEE = <?= json_encode($shippingFee) ?>;
    document.getElementById('applyVoucherBtn')?.addEventListener('click', function() {
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
                    const originalTotal = <?= $tongTien ?>;
                    let discount = 0;
                    let discounted = originalTotal;

                    // Kiểm tra giảm theo percentage
                    if (data.discountPercent && data.discountPercent > 0) {
                        discount = Math.round(originalTotal * (data.discountPercent / 100));
                        discounted = originalTotal - discount;
                    }
                    // Kiểm tra giảm theo số tiền
                    else if (data.discountAmount && data.discountAmount > 0) {
                        discount = data.discountAmount;
                        discounted = Math.max(0, originalTotal - discount);
                    }

                    // Cập nhật hiển thị (bao gồm phí ship)
                    document.getElementById('discountAmount').textContent = discount.toLocaleString('vi-VN') + ' đ';
                    const final = discounted + SHIPPING_FEE;
                    document.getElementById('finalTotal').textContent = final.toLocaleString('vi-VN') + ' đ';
                    document.getElementById('soTienInput').value = final;
                    document.getElementById('voucherCodeInput').value = voucherCode;
                    document.getElementById('shippingFeeDisplay').textContent = SHIPPING_FEE.toLocaleString('vi-VN') + ' đ';

                    messageDiv.textContent = data.message || 'Áp dụng mã thành công!';
                    messageDiv.classList.remove('text-danger');
                    messageDiv.classList.add('text-success');

                    // Disable inputs
                    document.getElementById('voucherInput').disabled = true;
                    document.getElementById('applyVoucherBtn').disabled = true;
                    document.getElementById('clearVoucherBtn').disabled = false;
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

    // Clear voucher functionality
    document.getElementById('clearVoucherBtn')?.addEventListener('click', function() {
        const originalTotal = <?= $tongTien ?>;

        // Reset hiển thị
        document.getElementById('discountAmount').textContent = '0 đ';
        document.getElementById('finalTotal').textContent = (originalTotal + SHIPPING_FEE).toLocaleString('vi-VN') + ' đ';
        document.getElementById('soTienInput').value = originalTotal + SHIPPING_FEE;
        document.getElementById('voucherCodeInput').value = '';
        document.getElementById('voucherInput').value = '';
        document.getElementById('voucherMessage').textContent = '';
        document.getElementById('shippingFeeDisplay').textContent = SHIPPING_FEE.toLocaleString('vi-VN') + ' đ';

        // Reset trạng thái
        document.getElementById('voucherInput').disabled = false;
        document.getElementById('applyVoucherBtn').disabled = false;
        document.getElementById('clearVoucherBtn').disabled = true;
    });

    // Initialize clear button state
    document.addEventListener('DOMContentLoaded', function() {
        const clearBtn = document.getElementById('clearVoucherBtn');
        if (clearBtn) {
            clearBtn.disabled = true;
        }
        // Payment method toggle
        const pmLocal = document.getElementById('pm_local');
        const pmVnpay = document.getElementById('pm_vnpay');
        const vnpayOptions = document.getElementById('vnpayOptions');
        const paymentForm = document.getElementById('paymentForm');

        function refreshVnpayVisibility() {
            if (pmVnpay && pmVnpay.checked) {
                vnpayOptions.style.display = 'block';
            } else {
                vnpayOptions.style.display = 'none';
            }
        }

        pmLocal?.addEventListener('change', refreshVnpayVisibility);
        pmVnpay?.addEventListener('change', refreshVnpayVisibility);
        refreshVnpayVisibility();

        // No client-side redirect: when payment method is VNPAY the main form
        // will be submitted to `controller/c_thanhToan.php` and the server
        // will create a pending order and forward to VNPAY. We only toggle UI here.

        // Show/hide COD confirmation block and enforce validation
        const codBlock = document.getElementById('codConfirmation');
        const codCheckbox = document.getElementById('cod_confirm_checkbox');
        const codCaptchaInput = document.getElementById('cod_captcha_input');
        const codImg = document.getElementById('codCaptchaImg');
        const reloadBtn = document.getElementById('reloadCaptcha');
        const codMsg = document.getElementById('codConfirmMessage');

        function refreshPaymentUI() {
                if (pmVnpay && pmVnpay.checked) {
                vnpayOptions.style.display = 'block';
                if (codBlock) codBlock.style.display = 'none';
                if (codCheckbox) codCheckbox.required = false;
                if (codCaptchaInput) codCaptchaInput.required = false;
            } else {
                vnpayOptions.style.display = 'none';
                if (codBlock) codBlock.style.display = 'block';
                if (codCheckbox) codCheckbox.required = true;
                if (codCaptchaInput) codCaptchaInput.required = true;
            }
        }

        pmLocal?.addEventListener('change', refreshPaymentUI);
        pmVnpay?.addEventListener('change', refreshPaymentUI);
        refreshPaymentUI();

        // reload captcha image
        reloadBtn?.addEventListener('click', function() {
            if (codImg) codImg.src = 'captcha.php?ts=' + Date.now();
        });

        // Intercept submit to enforce cod confirmation for local payment
        paymentForm?.addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="paymentMethod"]:checked')?.value;
            if (selected === 'local') {
                codMsg.style.display = 'none';
                const captchaVal = (codCaptchaInput && codCaptchaInput.value || '').trim();
                const checkboxChecked = codCheckbox && codCheckbox.checked;
                if (!checkboxChecked) {
                    e.preventDefault();
                    codMsg.textContent = 'Bạn phải đồng ý cam kết thanh toán bằng tiền mặt khi nhận hàng.';
                    codMsg.style.display = 'block';
                    return;
                }
                if (captchaVal.length === 0) {
                    e.preventDefault();
                    codMsg.textContent = 'Vui lòng nhập ký tự trong ảnh để xác nhận.';
                    codMsg.style.display = 'block';
                    return;
                }
                // Disable button to avoid double submit
                const btn = document.getElementById('payNowBtn');
                if (btn) btn.disabled = true;
            }
        });
    });
    </script>

</body>
<?php include('template/footer.php') ?>