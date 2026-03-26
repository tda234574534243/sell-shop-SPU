<?php
require_once '../model/m_khachhang.php';
session_start();

class KhachHangController {
    private $khachHangModel;

    public function __construct() {
        $this->khachHangModel = new KhachHangModel();
    }
    
    public function getKhachHangs($page = 1, $filter = []) {
        // Số bản ghi trên mỗi trang
        $records_per_page = 5;
        $offset = ($page - 1) * $records_per_page;

        // Lọc và phân trang dữ liệu
        $whereClause = [];
        if (!empty($filter['ten_khach'])) {
            $whereClause[] = "TenTK LIKE '%" . $this->khachHangModel->real_escape_string($filter['ten_khach']) . "%'";
        }
        if (!empty($filter['email'])) {
            $whereClause[] = "Email LIKE '%" . $this->khachHangModel->real_escape_string($filter['email']) . "%'";
        }
        if (!empty($filter['sdt'])) {
            $whereClause[] = "SDT LIKE '%" . $this->khachHangModel->real_escape_string($filter['sdt']) . "%'";
        }

        $whereSQL = count($whereClause) > 0 ? 'WHERE ' . implode(' AND ', $whereClause) : '';

        // Lấy danh sách khách hàng cho trang hiện tại
        $query = "SELECT * FROM Account $whereSQL LIMIT $offset, $records_per_page";
        $this->khachHangModel->setQuery($query);
        $khachHangs = $this->khachHangModel->excuteQuery();

        // Lấy tổng số bản ghi
        $queryCount = "SELECT COUNT(*) FROM Account $whereSQL";
        $this->khachHangModel->setQuery($queryCount);
        $total_records = $this->khachHangModel->excuteQuery()->fetch_row()[0];

        // Tính tổng số trang
        $total_pages = ceil($total_records / $records_per_page);

        return [
            'khachHangs' => $khachHangs,
            'current_page' => $page,
            'total_pages' => $total_pages,
        ];
    }
    
    public function xoaKhachHang($maTK) {
        return $this->khachHangModel->xoaKhachHang($maTK);
    }
}
$controller = new KhachHangController();

// Xử lý xóa nếu có ID truyền vào
if (isset($_GET['action']) && $_GET['action'] === 'xoa' && isset($_GET['id'])) {
    $result = $controller->xoaKhachHang($_GET['id']);
    
    if ($result === "Không thể xóa tài khoản admin") {
        // Hiển thị thông báo lỗi cho người dùng
        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Không thể xóa tài khoản admin!',
            'type' => 'error',
            'duration' => 3000
        ];
       header("Location: ../admin/analystic_customer.php");
       exit;
    } else {
        // Sau khi xóa xong thì chuyển hướng để tránh bị xóa lại khi reload
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Đã xóa thành công!',
            'type' => 'success',
            'duration' => 3000
        ];
        header("Location: ?ten_khach={$_GET['ten_khach']}&email={$_GET['email']}&sdt={$_GET['sdt']}&page=" . $page);
        exit;
    }
}



// Lấy danh sách khách hàng và phân trang
$filter = [
    'ten_khach' => $_GET['ten_khach'] ?? '',
    'email' => $_GET['email'] ?? '',
    'sdt' => $_GET['sdt'] ?? '',
];
$page = $_GET['page'] ?? 1;
$khachHangs = $controller->getKhachHangs($page, $filter);
?>
