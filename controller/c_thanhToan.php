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
    
    $hoadon->thanhToan($maKH, $sotien);
    $lastHD = $hoadon->getLastHoaDon()->fetch_assoc();
    $maHD = $lastHD['MaHD'];
    $cartItems = $cart->getCartItems($maKH);
    while ($row = $cartItems->fetch_assoc()) {
        $maSP = $row['MaSP'];
        $tenSP = $row['TenSP'];
        $soLuong = $row['SoLuong'];
        $giaTien = $row['GiaTien'];
        if ($lsMua->getLSMuaByMaSP($maSP)->num_rows > 0) {
            $lsMua->updateLSMua($maKH, $maSP, $soLuong);
        } else {
            $lsMua->addLSMua($maHD, $maKH, $maSP, $tenSP,$soLuong, $sotien, 'Đã thanh toán');
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