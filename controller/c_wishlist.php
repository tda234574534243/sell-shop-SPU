<?php
    session_start();
    include_once(__DIR__ . '/../model/m_wishlist.php');
    $mw = new M_wishlist();

    $maTK = $_SESSION['user_id'] ?? 0;
    if (!$maTK) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest') {
            echo json_encode(['success'=>false,'message'=>'Vui lòng đăng nhập']);
            exit;
        }
        header('Location: ../signIn.php'); exit;
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $maSP = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;
    $maSP = is_numeric($maSP) ? $maSP : $maSP;

    if ($action === 'add') {
        $ok = $mw->add($maTK, $maSP);
        $count = $mw->countByUser($maTK);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest') {
            echo json_encode(['success'=>true,'count'=>$count]); exit;
        }
        header('Location: ../wishlist.php'); exit;
    } elseif ($action === 'remove') {
        $ok = $mw->remove($maTK, $maSP);
        $count = $mw->countByUser($maTK);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest') {
            echo json_encode(['success'=>true,'count'=>$count]); exit;
        }
        header('Location: ../wishlist.php'); exit;
    } else {
        header('Location: ../index.php'); exit;
    }
?>
