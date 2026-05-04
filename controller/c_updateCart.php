<?php
    session_start();
    include_once(__DIR__ . '/../model/m_giohang.php');
    $cart = new M_giohang();

    $maTK = $_SESSION['user_id'] ?? 0;
    if (!$maTK) {
        echo json_encode(['success'=>false,'message'=>'Vui lòng đăng nhập']); exit;
    }

    $maSP = $_POST['product_id'] ?? 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;
    if (!$maSP || $qty === null) {
        echo json_encode(['success'=>false,'message'=>'Thiếu dữ liệu']); exit;
    }

    $ok = $cart->setCartQuantity($maTK, $maSP, $qty);
    $newCount = $cart->getCartCount($maTK);
    echo json_encode(['success' => (bool)$ok, 'count' => $newCount]);
    exit;
?>
