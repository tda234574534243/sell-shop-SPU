<?php
header('Content-Type: application/json; charset=utf-8');
// Return a small product list (MaSP, TenSP, MoTa) for chatbot context
include_once __DIR__ . '/../model/m_sanpham.php';

$m = new SanPhamModel();
$res = $m->getProductsByPage(0, 100);
$out = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $out[] = [
            'MaSP' => $r['MaSP'],
            'TenSP' => $r['TenSP'],
            'MoTa' => $r['MoTa'] ?? ''
        ];
    }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>
