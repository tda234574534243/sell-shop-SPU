<?php
require_once '../model/m_khachhang.php';
session_start();

class KhachHangController {
    private $khachHangModel;

    public function __construct() {
        $this->khachHangModel = new KhachHangModel();
    }
    
    // Public wrappers for model methods so external code won't access private property
    public function getLevelByMaTK($maTK) {
        return $this->khachHangModel->getLevelByMaTK($maTK);
    }

    public function setLevelByMaTK($maTK, $level) {
        return $this->khachHangModel->setLevelByMaTK($maTK, $level);
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
        $result = $this->khachHangModel->excuteQuery();
        $khachHangs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $khachHangs[] = $row;
            }
        }

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
// additional model instance for admin operations
$model = new KhachHangModel();

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
        $ten = rawurlencode($_GET['ten_khach'] ?? '');
        $emailParam = rawurlencode($_GET['email'] ?? '');
        $sdt = rawurlencode($_GET['sdt'] ?? '');
        header("Location: ?ten_khach={$ten}&email={$emailParam}&sdt={$sdt}&page=" . $page);
        exit;
    }
}

// Xử lý thăng/hạ quyền
if (isset($_GET['action']) && in_array($_GET['action'], ['promote','demote']) && isset($_GET['id'])) {
    // Chỉ admin mới được thay đổi quyền
    if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
        $_SESSION['toast'] = [
            'title' => 'Thông báo',
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
            'type' => 'error',
            'duration' => 3000
        ];
        header("Location: ../admin/analystic_customer.php");
        exit;
    }

    $targetId = intval($_GET['id']);
    $currentLevel = $controller->getLevelByMaTK($targetId);

    if ($_GET['action'] === 'promote') {
        if ($currentLevel === null) {
            $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Tài khoản không tồn tại.','type'=>'error','duration'=>3000];
        } elseif ($currentLevel == 1) {
            $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Người này đã là admin.','type'=>'info','duration'=>3000];
        } else {
            $ok = $controller->setLevelByMaTK($targetId, 1);
            if ($ok) $_SESSION['toast'] = [ 'title'=>'Thành công','message'=>'Đã nâng quyền thành admin.','type'=>'success','duration'=>3000];
            else $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Không thể nâng quyền.','type'=>'error','duration'=>3000];
        }
    }

    if ($_GET['action'] === 'demote') {
        if ($currentLevel === null) {
            $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Tài khoản không tồn tại.','type'=>'error','duration'=>3000];
        } elseif ($currentLevel == 1) {
            // Target is admin: allow demotion except self-demote
            $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
            if ($currentUserId !== null && $currentUserId === $targetId) {
                $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Bạn không thể hạ quyền chính mình.','type'=>'error','duration'=>3000];
            } else {
                $ok = $controller->setLevelByMaTK($targetId, 0);
                if ($ok) $_SESSION['toast'] = [ 'title'=>'Thành công','message'=>'Đã hạ quyền admin thành user.','type'=>'success','duration'=>3000];
                else $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Không thể cập nhật quyền.','type'=>'error','duration'=>3000];
            }
        } else {
            // Target is not admin: set to user (idempotent)
            $ok = $controller->setLevelByMaTK($targetId, 0);
            if ($ok) $_SESSION['toast'] = [ 'title'=>'Thành công','message'=>'Đã cập nhật quyền.','type'=>'success','duration'=>3000];
            else $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Không thể cập nhật quyền.','type'=>'error','duration'=>3000];
        }
    }

    // Redirect back to list preserving filters/page
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $ten = rawurlencode($_GET['ten_khach'] ?? '');
    $emailParam = rawurlencode($_GET['email'] ?? '');
    $sdt = rawurlencode($_GET['sdt'] ?? '');
    header("Location: ?ten_khach={$ten}&email={$emailParam}&sdt={$sdt}&page=" . $page);
    exit;
}

// Xử lý khóa/mở khóa tài khoản (admin)
if (isset($_GET['action']) && in_array($_GET['action'], ['lock','unlock']) && isset($_GET['id'])) {
    if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
        $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Bạn không có quyền thực hiện thao tác này.','type'=>'error','duration'=>3000];
        header("Location: ../admin/analystic_customer.php"); exit;
    }

    $targetId = intval($_GET['id']);
    $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    if ($currentUserId !== null && $currentUserId === $targetId && $_GET['action'] === 'lock') {
        $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Bạn không thể khóa chính mình.','type'=>'error','duration'=>3000];
    } else {
        $locked = ($_GET['action'] === 'lock') ? 1 : 0;
        $ok = $model->setLockedByMaTK($targetId, $locked);
        if ($ok) {
            $_SESSION['toast'] = [ 'title'=>'Thành công','message'=> ($locked ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.'),'type'=>'success','duration'=>3000];
        } else {
            $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Không thể thay đổi trạng thái khóa.','type'=>'error','duration'=>3000];
        }
    }

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $ten = rawurlencode($_GET['ten_khach'] ?? '');
    $emailParam = rawurlencode($_GET['email'] ?? '');
    $sdt = rawurlencode($_GET['sdt'] ?? '');
    header("Location: ?ten_khach={$ten}&email={$emailParam}&sdt={$sdt}&page=" . $page);
    exit;
}

// Xử lý cập nhật bởi admin (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_update' && isset($_POST['id'])) {
    if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
        $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Bạn không có quyền thực hiện thao tác này.','type'=>'error','duration'=>3000];
        header("Location: ../admin/analystic_customer.php"); exit;
    }

    $targetId = intval($_POST['id']);
    $data = [];
    if (isset($_POST['TenTK'])) $data['TenTK'] = $_POST['TenTK'];
    if (isset($_POST['Email'])) $data['Email'] = $_POST['Email'];
    if (isset($_POST['SDT'])) $data['SDT'] = $_POST['SDT'];
    if (isset($_POST['DiaChi'])) $data['DiaChi'] = $_POST['DiaChi'];
    if (isset($_POST['LevelID'])) $data['LevelID'] = intval($_POST['LevelID']);
    // Locked: use 0 when not provided (hidden input ensures this), but prevent admin self-lock
    $postedLocked = isset($_POST['Locked']) ? intval($_POST['Locked']) : 0;
    $currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    if ($currentUserId !== null && $currentUserId === $targetId && $postedLocked === 1) {
        // prevent admin from locking themselves via admin edit
        $_SESSION['toast'] = [ 'title'=>'Thông báo','message'=>'Bạn không thể khóa chính mình.','type'=>'error','duration'=>3000];
        $data['Locked'] = 0;
    } else {
        $data['Locked'] = $postedLocked;
    }

    // handle password reset by admin
    if (!empty($_POST['NewPassword'])) {
        $data['Password'] = password_hash($_POST['NewPassword'], PASSWORD_DEFAULT);
    }

    // handle avatar upload
    if (isset($_FILES['Avatar']) && $_FILES['Avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['Avatar']['tmp_name'];
        $fileName = $_FILES['Avatar']['name'];
        $fileType = mime_content_type($fileTmp);
        $allowed = ['image/jpeg','image/png','image/gif'];
        if (in_array($fileType, $allowed)) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $uploadDir = __DIR__ . '/../media/image/avatars';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $newName = 'avatar_' . $targetId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $newName;
            if (move_uploaded_file($fileTmp, $destPath)) {
                $data['Avatar'] = 'media/image/avatars/' . $newName;
            }
        }
    }

    $ok = $model->adminUpdateAccount($targetId, $data);
    if ($ok) {
        $_SESSION['toast'] = [ 'title'=>'Thành công','message'=>'Cập nhật người dùng thành công.','type'=>'success','duration'=>3000];
    } else {
        $_SESSION['toast'] = [ 'title'=>'Lỗi','message'=>'Không thể cập nhật người dùng.','type'=>'error','duration'=>3000];
    }
    header("Location: ../admin/analystic_customer.php"); exit;
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
