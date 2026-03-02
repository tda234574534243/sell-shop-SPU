<?php
header('Content-Type: application/json');
include_once('m_database.php');

$voucher = $_GET['voucher'] ?? '';
$voucher = trim($voucher);

$response = ['valid' => false];

if ($voucher !== '') {
    $db = new M_database();
    $db->setQuery("SELECT * FROM vouchers WHERE MaV = '$voucher'");

    $row = $db->excuteQuery()->fetch_assoc();
    $db->close();

    if ($row) {
        $response['valid'] = true;
        $response['discount'] = $row['Discount'];
    }
}

echo json_encode($response);