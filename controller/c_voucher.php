<?php
require_once '../model/m_voucher.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) { header('Location: ../index.php'); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$model = new M_voucher();

if ($action === 'add') {
    $data = [
        'Code'=> $_POST['Code'] ?? '',
        'Description'=> $_POST['Description'] ?? '',
        'DiscountPercent'=> !empty($_POST['DiscountPercent'])?intval($_POST['DiscountPercent']):null,
        'DiscountAmount'=> !empty($_POST['DiscountAmount'])?floatval($_POST['DiscountAmount']):null,
        'ValidFrom'=> $_POST['ValidFrom'] ?? null,
        'ValidTo'=> $_POST['ValidTo'] ?? null,
        'Quantity'=> !empty($_POST['Quantity'])?intval($_POST['Quantity']):null
    ];
    
    if (empty($data['Code'])) {
        $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'Mã voucher không được để trống', 'type' => 'error'];
    } else {
        $result = $model->add($data);
        if ($result) {
            $_SESSION['toast'] = ['title' => 'Thành công', 'message' => 'Đã tạo voucher mới', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'Không thể tạo voucher. Kiểm tra log để biết lý do chi tiết.', 'type' => 'error', 'duration' => 5000];
        }
    }
    header('Location: ../admin/vouchers.php'); exit;
}
if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'ID voucher không hợp lệ', 'type' => 'error'];
    } else {
        $data = [
            'Code'=> $_POST['Code'] ?? '',
            'Description'=> $_POST['Description'] ?? '',
            'DiscountPercent'=> !empty($_POST['DiscountPercent'])?intval($_POST['DiscountPercent']):null,
            'DiscountAmount'=> !empty($_POST['DiscountAmount'])?floatval($_POST['DiscountAmount']):null,
            'ValidFrom'=> $_POST['ValidFrom'] ?? null,
            'ValidTo'=> $_POST['ValidTo'] ?? null,
            'Quantity'=> !empty($_POST['Quantity'])?intval($_POST['Quantity']):null
        ];
        
        if (empty($data['Code'])) {
            $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'Mã voucher không được để trống', 'type' => 'error'];
        } else {
            $result = $model->update($id, $data);
            if ($result) {
                $_SESSION['toast'] = ['title' => 'Thành công', 'message' => 'Đã cập nhật voucher', 'type' => 'success'];
            } else {
                $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'Không thể cập nhật voucher. Kiểm tra log để biết lý do chi tiết.', 'type' => 'error', 'duration' => 5000];
            }
        }
    }
    header('Location: ../admin/vouchers.php'); exit;
}
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'ID voucher không hợp lệ', 'type' => 'error'];
    } else {
        $result = $model->delete($id);
        if ($result) {
            $_SESSION['toast'] = ['title' => 'Thành công', 'message' => 'Đã xóa voucher', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['title' => 'Lỗi', 'message' => 'Không thể xóa voucher. Kiểm tra log để biết lý do chi tiết.', 'type' => 'error', 'duration' => 5000];
        }
    }
    header('Location: ../admin/vouchers.php'); exit;
}

header('Location: ../admin/vouchers.php'); exit;

?>
