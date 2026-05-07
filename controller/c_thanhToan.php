<?php
    session_start();
    include_once("../model/m_hoadon.php");
    include_once("../model/m_giohang.php");
    include_once("../model/m_lsMua.php");
    include_once("../model/m_voucher.php");
    
    $hoadon = new M_hoadon();
    $cart = new M_giohang();
    $lsMua = new M_lsMua();
    $voucherModel = new M_voucher();

    $maKH = $_SESSION['user_id'] ?? 0;
    $sotien = isset($_POST['soTien']) ? $_POST['soTien'] : 0;
    $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'local';
    $vnpayBankCode = isset($_POST['bankCode']) ? $_POST['bankCode'] : '';
    $vnpayLang = isset($_POST['vnpayLang']) ? $_POST['vnpayLang'] : 'vn';
    $voucherCode = isset($_POST['voucherCode']) ? trim($_POST['voucherCode']) : '';
    // COD confirmation fields (from payProduct.php)
    $codConfirm = isset($_POST['cod_confirm']) ? $_POST['cod_confirm'] : '0';
    $codCaptchaInput = isset($_POST['cod_captcha']) ? strtolower(trim($_POST['cod_captcha'])) : '';
    
    if ($maKH <= 0) {
        die("Vui lòng đăng nhập");
    }
    if ($sotien < 0) {
        die("Số tiền không hợp lệ");
    }

    // If payment method is local (COD), require explicit confirmation + captcha
    if ($paymentMethod === 'local') {
        $sessionCaptcha = isset($_SESSION['cod_captcha']) ? $_SESSION['cod_captcha'] : '';
        // validate checkbox and captcha (case-insensitive)
        if ($codConfirm !== '1' || empty($sessionCaptcha) || $codCaptchaInput !== $sessionCaptcha) {
            // remove stored captcha to avoid reuse
            if (isset($_SESSION['cod_captcha'])) unset($_SESSION['cod_captcha']);
            $_SESSION['toast'] = [
                'title' => 'Lỗi',
                'message' => 'Xác nhận COD không hợp lệ. Vui lòng đánh dấu và nhập đúng ký tự từ ảnh.',
                'type' => 'error'
            ];
            header("Location: ../payProduct.php?action=checkout");
            exit;
        }
        // consume captcha
        unset($_SESSION['cod_captcha']);
    }
    
    // Kiểm tra và xử lý voucher nếu có
    $voucherId = null;
    if (!empty($voucherCode)) {
        $voucher = $voucherModel->getByCode($voucherCode);
        if ($voucher) {
            $voucherId = $voucher['id'];
            
            // Giảm số lượng voucher
            if (!empty($voucher['Quantity']) && $voucher['Quantity'] > 0) {
                $newQuantity = $voucher['Quantity'] - 1;
                $voucherModel->updateQuantity($voucherId, $newQuantity);
            }
        }
    }
    // Lấy giỏ hàng trước để tính phí vận chuyển nếu cần
    $cartItems = $cart->getCartItems($maKH);
    $cartRows = [];
    if ($cartItems && $cartItems->num_rows > 0) {
        while ($r = $cartItems->fetch_assoc()) {
            $cartRows[] = $r;
        }
    }

    // Load shipping config
    $configPath = __DIR__ . '/../public/DATA/shipping.json';
    $shippingConfig = ['threshold' => 10000000, 'fee' => 0];
    if (file_exists($configPath)) {
        $raw = file_get_contents($configPath);
        $j = json_decode($raw, true);
        if (is_array($j)) $shippingConfig = array_merge($shippingConfig, $j);
    }

    // Nếu có bất kỳ mặt hàng nào có giá đơn vị > threshold thì áp phí
    $applyFee = false;
    foreach ($cartRows as $it) {
        $price = floatval($it['GiaTien'] ?? 0);
        if ($price > floatval($shippingConfig['threshold'])) {
            $applyFee = true;
            break;
        }
    }
    if ($applyFee && floatval($shippingConfig['fee']) > 0) {
        $sotien = floatval($sotien) + floatval($shippingConfig['fee']);
    }

    // If payment method is VNPAY we create a pending order and forward to VNPAY
    if ($paymentMethod === 'vnpay') {
        // Create HoaDon with status 'Chờ thanh toán'
        $maHD = $hoadon->createHoaDon($maKH, $sotien, 'Chờ thanh toán');
        if ($maHD === false) {
            error_log("createHoaDon failed for MaTK={$maKH} amount={$sotien}");
            $_SESSION['toast'] = [ 'title' => 'Lỗi', 'message' => 'Không thể tạo đơn hàng', 'type' => 'danger' ];
            header("Location: ../payProduct.php");
            exit;
        }

        // Insert LS_Mua rows with state 'Chờ thanh toán'
        if (!empty($cartRows)) {
            foreach ($cartRows as $row) {
                $maSP = $row['MaSP'];
                $tenSP = $row['TenSP'];
                $soLuong = $row['SoLuong'];
                $giaTien = $row['GiaTien'];
                $added = $lsMua->addLSMua($maHD, $maKH, $maSP, $tenSP, $soLuong, $giaTien, 'Chờ thanh toán');
                if ($added === false) {
                    error_log("addLSMua failed for MaHD={$maHD} MaSP={$maSP} MaTK={$maKH}");
                }
            }
        }

        // Clear cart now to avoid duplicate payment
        $cart->clearCart($maKH);

        // Forward to VNPAY create payment endpoint via auto-submitting form so we can include MaHD as vnp_TxnRef
        $amount = floatval($sotien);
        $bankCode = htmlspecialchars($vnpayBankCode);
        $lang = htmlspecialchars($vnpayLang);

        echo "<html><body>";
        echo "<form id='vnpayForward' action='../vnpay_php/vnpay_create_payment.php' method='POST'>";
        echo "<input type='hidden' name='amount' value='".htmlspecialchars($amount)."'>";
        echo "<input type='hidden' name='language' value='".htmlspecialchars($lang)."'>";
        echo "<input type='hidden' name='bankCode' value='".htmlspecialchars($bankCode)."'>";
        echo "<input type='hidden' name='vnp_TxnRef' value='".htmlspecialchars($maHD)."'>";
        echo "</form>";
        echo "<script>document.getElementById('vnpayForward').submit();</script>";
        echo "</body></html>";
        exit;
    }

    // Local (COD) / other payment methods: create HoaDon immediately with COD status
    if ($paymentMethod === 'local') {
        // create invoice with COD status
        $maHD = $hoadon->createHoaDon($maKH, $sotien, 'Chờ giao (COD)');
        if ($maHD === false) {
            error_log("createHoaDon failed for MaTK={$maKH} amount={$sotien}");
            $_SESSION['toast'] = [ 'title' => 'Lỗi', 'message' => 'Không thể tạo đơn hàng', 'type' => 'danger' ];
            header("Location: ../payProduct.php");
            exit;
        }

        // Insert LS_Mua rows with state 'Chờ giao'
        if (!empty($cartRows)) {
            foreach ($cartRows as $row) {
                $maSP = $row['MaSP'];
                $tenSP = $row['TenSP'];
                $soLuong = $row['SoLuong'];
                $giaTien = $row['GiaTien'];
                $added = $lsMua->addLSMua($maHD, $maKH, $maSP, $tenSP, $soLuong, $giaTien, 'Chờ giao');
                if ($added === false) {
                    error_log("addLSMua failed for MaHD={$maHD} MaSP={$maSP} MaTK={$maKH}");
                }
            }
        }

        // Clear cart now
        $cart->clearCart($maKH);

        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Đặt hàng thành công (COD). Vui lòng chuẩn bị thanh toán ' . number_format($sotien, 0, ',', '.') . ' VNĐ khi nhận hàng.' . ($voucherCode ? ' (Sử dụng voucher: ' . htmlspecialchars($voucherCode) . ')' : ''),
            'type' => 'success',
            'duration' => 3000
        ];
        header("Location: ../index.php");
        exit;
    }
?>