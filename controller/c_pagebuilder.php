<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../model/m_pagebuilder.php';
$m = new M_pagebuilder();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$pageSlug = $_POST['pageSlug'] ?? $_GET['pageSlug'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        // ========== PAGE MANAGEMENT ==========
        case 'getPages':
            $pages = $m->getAllPages();
            echo json_encode(['success' => true, 'pages' => $pages]);
            break;
            
        case 'getPage':
            $page = $m->getPage($pageSlug);
            if ($page) {
                echo json_encode(['success' => true, 'page' => $page]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Page not found']);
            }
            break;
            
        case 'createPage':
            $title = $_POST['title'] ?? '';
            $slug = $_POST['slug'] ?? '';
            
            if (!$title || !$slug) {
                echo json_encode(['success' => false, 'message' => 'Title and slug required']);
                break;
            }
            
            $result = $m->createPage($slug, $title);
            $_SESSION['toast'] = [
                'title' => $result ? 'Thành công' : 'Lỗi',
                'message' => $result ? 'Tạo trang mới thành công' : 'Trang này đã tồn tại',
                'type' => $result ? 'success' : 'error',
                'duration' => 3000
            ];
            echo json_encode(['success' => $result]);
            break;
            
        case 'updatePageTitle':
            $title = $_POST['title'] ?? '';
            if (!$title) {
                echo json_encode(['success' => false, 'message' => 'Title required']);
                break;
            }
            
            $result = $m->updatePageTitle($pageSlug, $title);
            echo json_encode(['success' => $result]);
            break;
            
        case 'deletePage':
            $result = $m->deletePage($pageSlug);
            $_SESSION['toast'] = [
                'title' => $result ? 'Thành công' : 'Lỗi',
                'message' => $result ? 'Xóa trang thành công' : 'Lỗi khi xóa trang',
                'type' => $result ? 'success' : 'error',
                'duration' => 3000
            ];
            echo json_encode(['success' => $result]);
            break;
            
        // ========== BLOCK MANAGEMENT ==========
        case 'getBlocks':
            $blocks = $m->getBlocks($pageSlug);
            echo json_encode(['success' => true, 'blocks' => $blocks]);
            break;
            
        case 'addBlock':
            $type = $_POST['type'] ?? '';
            $section = $_POST['section'] ?? 'center';
            $data = json_decode($_POST['data'] ?? '{}', true);
            
            if (!$type) {
                echo json_encode(['success' => false, 'message' => 'Block type required']);
                break;
            }
            
            $blockId = $m->addBlock($pageSlug, $type, $data, $section);
            $_SESSION['toast'] = [
                'title' => 'Thành công',
                'message' => 'Thêm block mới thành công',
                'type' => 'success',
                'duration' => 3000
            ];
            echo json_encode(['success' => $blockId !== false, 'blockId' => $blockId]);
            break;
            
        case 'updateBlock':
            $blockId = $_POST['blockId'] ?? '';
            $data = json_decode($_POST['data'] ?? '{}', true);
            
            if (!$blockId) {
                echo json_encode(['success' => false, 'message' => 'Block ID required']);
                break;
            }
            
            $result = $m->updateBlock($pageSlug, $blockId, $data);
            echo json_encode(['success' => $result]);
            break;
            
        case 'deleteBlock':
            $blockId = $_POST['blockId'] ?? '';
            if (!$blockId) {
                echo json_encode(['success' => false, 'message' => 'Block ID required']);
                break;
            }
            
            $result = $m->deleteBlock($pageSlug, $blockId);
            $_SESSION['toast'] = [
                'title' => $result ? 'Thành công' : 'Lỗi',
                'message' => $result ? 'Xóa block thành công' : 'Lỗi khi xóa block',
                'type' => $result ? 'success' : 'error',
                'duration' => 3000
            ];
            echo json_encode(['success' => $result]);
            break;
            
        case 'reorderBlocks':
            $blockOrder = $_POST['blockOrder'] ?? [];
            if (!is_array($blockOrder)) {
                $blockOrder = json_decode($blockOrder, true);
            }
            
            if (!$blockOrder) {
                echo json_encode(['success' => false, 'message' => 'Block order required']);
                break;
            }
            
            $result = $m->reorderBlocks($pageSlug, $blockOrder);
            echo json_encode(['success' => $result]);
            break;
            
        case 'uploadImage':
            // Ensure upload directory exists
            $uploadDir = __DIR__ . '/../media/image/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
                echo json_encode(['success' => false, 'message' => 'Upload lỗi']);
                break;
            }
            
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ JPG, PNG, GIF, WebP']);
                break;
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File quá lớn (max 5MB)']);
                break;
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imageUrl = '/sell-shop-SPU/media/image/uploads/' . $filename;
                echo json_encode(['success' => true, 'imageUrl' => $imageUrl, 'filename' => $filename]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
            }
            break;
            
        case 'updatePageStatus':
            $status = $_POST['status'] ?? '';
            if (!$pageSlug || !$status) {
                echo json_encode(['success' => false, 'message' => 'Missing pageSlug or status']);
                break;
            }
            
            $result = $m->updatePageStatus($pageSlug, $status);
            $_SESSION['toast'] = [
                'title' => $result ? 'Thành công' : 'Lỗi',
                'message' => $result ? 'Cập nhật trạng thái thành công' : 'Không thể cập nhật trạng thái',
                'type' => $result ? 'success' : 'error'
            ];
            echo json_encode(['success' => $result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
