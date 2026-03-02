<?php
require_once "../model/m_donhang.php";

class DonHangController {
    public function hienThiDonHang() {
        $model = new DonHangModel();

        // Lấy trang hiện tại
        $limit = 5;
        $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $start = ($current_page - 1) * $limit;

        // Lấy các tham số tìm kiếm từ form
        $ten_tk = $_GET['ten_tk'] ?? '';
        $ten_sp = $_GET['ten_sp'] ?? '';
        $ngay_tu = $_GET['ngay_tu'] ?? '';
        $ngay_den = $_GET['ngay_den'] ?? '';
        $ma_hd = $_GET['ma_hd'] ?? '';

        // Lấy dữ liệu và tổng số trang
        $lich_su = $model->getLichSuMua($start, $limit, $ten_tk, $ten_sp, $ngay_tu, $ngay_den, $ma_hd);
        $total_rows = $model->getTotalLichSuMua($ten_tk, $ten_sp, $ngay_tu, $ngay_den, $ma_hd);
        $total_pages = ceil($total_rows / $limit);

        return [
            'lich_su' => $lich_su,
            'current_page' => $current_page,
            'total_pages' => $total_pages
        ];
    }
}


$donhang_controller = new DonHangController();
$data = $donhang_controller->hienThiDonHang();
$lich_su = $data['lich_su'];
$current_page = $data['current_page'];
$total_pages = $data['total_pages'];
?>
