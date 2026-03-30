<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    $_SESSION['toast'] = [
        'title' => 'Lỗi',
        'message' => 'Bạn không có quyền truy cập chức năng này',
        'type' => 'error',
        'duration' => 3000
    ];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

require_once '../model/m_homepage.php';
$m = new M_homepage();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'update_banner':
        $result = $m->updateSection('banner', [
            'title' => $_POST['banner_title'] ?? '',
            'subtitle' => $_POST['banner_subtitle'] ?? '',
            'buttonText' => $_POST['banner_button'] ?? '',
            'backgroundImage' => $_POST['banner_image'] ?? ''
        ]);
        $_SESSION['toast'] = [
            'title' => $result ? 'Thành công' : 'Lỗi',
            'message' => $result ? 'Cập nhật banner thành công' : 'Lỗi khi cập nhật banner',
            'type' => $result ? 'success' : 'error',
            'duration' => 3000
        ];
        break;
        
    case 'update_featured':
        $result = $m->updateSection('featured', [
            'title' => $_POST['featured_title'] ?? '',
            'description' => $_POST['featured_desc'] ?? ''
        ]);
        $_SESSION['toast'] = [
            'title' => $result ? 'Thành công' : 'Lỗi',
            'message' => $result ? 'Cập nhật sản phẩm nổi bật thành công' : 'Lỗi khi cập nhật',
            'type' => $result ? 'success' : 'error',
            'duration' => 3000
        ];
        break;
        
    case 'update_promo':
        $result = $m->updateSection('promo', [
            'title' => $_POST['promo_title'] ?? '',
            'description' => $_POST['promo_desc'] ?? ''
        ]);
        $_SESSION['toast'] = [
            'title' => $result ? 'Thành công' : 'Lỗi',
            'message' => $result ? 'Cập nhật khuyến mãi thành công' : 'Lỗi khi cập nhật',
            'type' => $result ? 'success' : 'error',
            'duration' => 3000
        ];
        break;
        
    case 'update_announcement':
        $result = $m->updateSection('announcement', [
            'enabled' => isset($_POST['announcement_enabled']) ? true : false,
            'message' => $_POST['announcement_message'] ?? '',
            'type' => $_POST['announcement_type'] ?? 'info'
        ]);
        $_SESSION['toast'] = [
            'title' => $result ? 'Thành công' : 'Lỗi',
            'message' => $result ? 'Cập nhật thông báo thành công' : 'Lỗi khi cập nhật',
            'type' => $result ? 'success' : 'error',
            'duration' => 3000
        ];
        break;
        
    case 'reset':
        $result = $m->resetToDefault();
        $_SESSION['toast'] = [
            'title' => $result ? 'Thành công' : 'Lỗi',
            'message' => $result ? 'Đã reset về mặc định' : 'Lỗi khi reset',
            'type' => $result ? 'success' : 'error',
            'duration' => 3000
        ];
        break;
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../admin/homepage.php'));
exit;
?>
