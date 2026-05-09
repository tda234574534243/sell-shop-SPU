<?php
header('Content-Type: application/json; charset=utf-8');
// Return a small product list (MaSP, TenSP, MoTa) for chatbot context
include_once __DIR__ . '/../model/m_sanpham.php';

$m = new SanPhamModel();
$res = $m->getProductsByPage(0, 100);
$out = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $gia = isset($r['GiaTien']) ? $r['GiaTien'] : null;
        $soluong = isset($r['SoLuong']) ? $r['SoLuong'] : null;
        $nsx = isset($r['NSX']) ? $r['NSX'] : '';
        $baohanh = isset($r['BaoHanh']) ? $r['BaoHanh'] : '';
        $out[] = [
            'MaSP' => $r['MaSP'],
            'TenSP' => $r['TenSP'],
            'MoTa' => $r['MoTa'] ?? '',
            'GiaTien' => $gia,
            'Gia' => $gia !== null ? number_format((float)$gia, 0, ',', '.') . '₫' : null,
            'SoLuong' => $soluong,
            'NSX' => $nsx,
            'BaoHanh' => $baohanh
        ];
    }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>
