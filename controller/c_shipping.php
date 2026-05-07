<?php
session_start();

// Simple controller to save shipping settings to public/DATA/shipping.json
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/shipping_settings.php');
    exit;
}

$threshold = isset($_POST['threshold']) ? floatval(str_replace(',', '', $_POST['threshold'])) : null;
$fee = isset($_POST['fee']) ? floatval(str_replace(',', '', $_POST['fee'])) : null;

if ($threshold === null || $fee === null) {
    $_SESSION['toast'] = [
        'title' => 'Lỗi',
        'message' => 'Dữ liệu không hợp lệ',
        'type' => 'danger',
        'duration' => 3000
    ];
    header('Location: ../admin/shipping_settings.php');
    exit;
}

$configPath = __DIR__ . '/../public/DATA/shipping.json';
$data = [
    'threshold' => $threshold,
    'fee' => $fee
];

if (file_put_contents($configPath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
    $_SESSION['toast'] = [
        'title' => 'Lỗi',
        'message' => 'Không thể lưu cấu hình',
        'type' => 'danger',
        'duration' => 3000
    ];
} else {
    $_SESSION['toast'] = [
        'title' => 'Thành công',
        'message' => 'Cấu hình vận chuyển đã được lưu',
        'type' => 'success',
        'duration' => 2000
    ];
}

header('Location: ../admin/shipping_settings.php');
exit;
?>
