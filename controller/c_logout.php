<?php
    session_start();          
    session_unset();         
    session_destroy();        

    $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Vui lòng đăng nhập để mua hàng!',
            'type' => 'error',
            'duration' => 3000
    ];
    header("Location: ../index.php");
    exit;
?>
