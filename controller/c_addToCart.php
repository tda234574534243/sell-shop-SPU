<?php
    session_start();
    include_once("../model/m_giohang.php");
    $cart = new M_giohang();

    $maKH = $_SESSION['user_id'] ?? 0;
    $maSP = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
    $soLuong = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if (!isset($_SESSION['user_id'])) {
         header("Location: ../signin.php");
    }
    else if ($maKH <= 0 || $maSP <= 0 || $soLuong <= 0) {
        die("Thiếu thông tin hoặc không hợp lệ.");
    }
  
    $db->setQuery("SELECT GiaTien FROM products WHERE MaSP = '$maSP'");
    $result = $db->excuteQuery();
    $row = $result ? $result->fetch_assoc() : null;

    if (!$row) {
        die("Không tìm thấy sản phẩm.");
    }

    $gia = $row['GiaTien'];

    $db->setQuery("SELECT * FROM cart WHERE MaTK = $maKH AND MaSP = '$maSP' AND State = 'chưa thanh toán'");
    $res = $db->excuteQuery();

    if ($res && $res->num_rows > 0) {
        $cart->updateCart($maKH, $maSP, $soLuong);
    } else {
        $cart->addToCart($maKH, $maSP, $soLuong, $gia, 'chưa thanh toán');
    }

    $cart->close();
    $_SESSION['toast'] = [
        'title' => 'Thông báo',
        'message' => 'Thêm giỏ hàng thành công!',
        'type' => 'success',
        'duration' => 3000
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
?>
