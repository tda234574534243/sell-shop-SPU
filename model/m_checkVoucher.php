<?php
header('Content-Type: application/json');
include_once('m_database.php');

$voucher = $_GET['voucher'] ?? '';
$voucher = trim($voucher);

$response = ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];

if ($voucher !== '') {
    $db = new M_database();
    // Sửa: tìm theo Code thay cho MaV
    $voucher_safe = $db->getConnection()->real_escape_string($voucher);
    $db->setQuery("SELECT * FROM Vouchers WHERE Code = '$voucher_safe'");

    $row = $db->excuteQuery()->fetch_assoc();
    $db->close();

    if ($row) {
        // Kiểm tra ngày hết hạn
        $today = date('Y-m-d');
        if (!empty($row['ValidFrom']) && $row['ValidFrom'] > $today) {
            $response['message'] = 'Mã giảm giá chưa có hiệu lực';
            echo json_encode($response);
            exit;
        }
        
        if (!empty($row['ValidTo']) && $row['ValidTo'] < $today) {
            $response['message'] = 'Mã giảm giá đã hết hạn';
            echo json_encode($response);
            exit;
        }
        
        // Kiểm tra số lượng
        if (!empty($row['Quantity']) && $row['Quantity'] <= 0) {
            $response['message'] = 'Mã giảm giá đã hết';
            echo json_encode($response);
            exit;
        }
        
        $response['valid'] = true;
        $response['message'] = 'Mã giảm giá hợp lệ';
        // Trả về cả hai loại discount
        $response['discountPercent'] = $row['DiscountPercent'] ?? null;
        $response['discountAmount'] = $row['DiscountAmount'] ?? null;
        // Giữ lại discount cho backward compatibility (sử dụng percent nếu có)
        $response['discount'] = $row['DiscountPercent'] ?? 0;
    }
}

echo json_encode($response);