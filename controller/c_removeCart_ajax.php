<?php
    session_start();
    include_once(__DIR__ . '/../model/m_giohang.php');
    $cart = new M_giohang();
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success'=>false,'message'=>'Vui lòng đăng nhập']); exit;
    }
    $maTK = $_SESSION['user_id'];
    $maSP = $_POST['product_id'] ?? null;
    if (!$maSP) { echo json_encode(['success'=>false,'message'=>'Thiếu product_id']); exit; }

    $ok = $cart->removeFromCart($maTK, $maSP);
    $count = $cart->getCartCount($maTK);
    echo json_encode(['success'=>(bool)$ok, 'count'=>$count]);
    exit;
?>
