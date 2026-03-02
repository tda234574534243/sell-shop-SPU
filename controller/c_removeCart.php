<?php
    session_start();
    include_once('../model/m_giohang.php');
    
    $cart = new M_giohang();
    $return_url = $_GET['return_url'] ?? '../index.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../signin.php");
        exit();
    }
    $maKH = $_SESSION['user_id'] ?? 0;

    if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
        $product_id = mysqli_real_escape_string($db->conn, $_GET['id']);

        $result = $cart->getCartItem($maKH, $product_id);

        if ($result && $result->num_rows > 0) {
            $cart->removeFromCart($maKH, $product_id);
        }
    } 

    $cart->close();
     $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Xoá sản phẩm khỏi giỏ hàng thành công!',
            'type' => 'success',
            'duration' => 3000
    ];
    header("Location: $return_url");
    exit;
?>
