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
    $voucherCode = isset($_POST['voucherCode']) ? trim($_POST['voucherCode']) : '';
    
    if ($maKH <= 0) {
        die("Vui lòng đăng nhập");
    }
    if ($sotien < 0) {
        die("Số tiền không hợp lệ");
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
    
    $resPayment = $hoadon->thanhToan($maKH, $sotien);
    if ($resPayment === false) {
        error_log("thanhToan failed for MaTK={$maKH} amount={$sotien}");
        $_SESSION['toast'] = [
            'title' => 'Lỗi',
            'message' => 'Thanh toán thất bại, vui lòng thử lại.',
            'type' => 'danger',
            'duration' => 4000
        ];
        header("Location: ../cart.php");
        exit;
    }

    $lastHDRes = $hoadon->getLastHoaDon();
    if ($lastHDRes === false || $lastHDRes->num_rows === 0) {
        error_log("getLastHoaDon failed after thanhToan for MaTK={$maKH}");
        $_SESSION['toast'] = [
            'title' => 'Lỗi',
            'message' => 'Không thể lấy hóa đơn vừa tạo.',
            'type' => 'danger',
            'duration' => 4000
        ];
        header("Location: ../cart.php");
        exit;
    }

    $lastHD = $lastHDRes->fetch_assoc();
    $maHD = $lastHD['MaHD'];
    $cartItems = $cart->getCartItems($maKH);
    if ($cartItems === false) {
        error_log("getCartItems failed for MaTK={$maKH}");
    }
    if ($cartItems && $cartItems->num_rows === 0) {
        error_log("Cart is empty for MaTK={$maKH} at thanhToan time");
    }
    while ($row = $cartItems->fetch_assoc()) {
        $maSP = $row['MaSP'];
        $tenSP = $row['TenSP'];
        $soLuong = $row['SoLuong'];
        $giaTien = $row['GiaTien'];
            // Check existence for this user+product, not global by product
            $existing = $lsMua->getLSMuaByMaTKAndMaSP($maKH, $maSP);
            error_log("Processing LS_Mua insert for MaHD={$maHD} MaTK={$maKH} MaSP={$maSP} SoLuong={$soLuong} GiaTien={$giaTien}");
        if ($existing === false) {
            error_log("getLSMuaByMaSP query failed for MaSP={$maSP}");
        }
        if ($existing && $existing->num_rows > 0) {
            $lsMua->updateLSMua($maKH, $maSP, $soLuong);
        } else {
            // Use per-item unit price ($giaTien) instead of total order amount
            $added = $lsMua->addLSMua($maHD, $maKH, $maSP, $tenSP, $soLuong, $giaTien, 'Đã thanh toán');
            if ($added === false) {
                error_log("addLSMua failed for MaHD={$maHD} MaSP={$maSP} MaTK={$maKH}");
            }
        }
    } 
    $cart->clearCart($maKH);  
     $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Thanh toán thành công số tiền ' . number_format( $sotien, 0, ',', '.') . ' VNĐ' . ($voucherCode ? ' (Sử dụng voucher: ' . htmlspecialchars($voucherCode) . ')' : ''),
            'type' => 'success',
            'duration' => 3000
    ];
    header("Location: ../index.php");
    exit;
?>