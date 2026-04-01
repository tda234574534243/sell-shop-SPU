<?php
/**
 * File tracking hoạt động user online
 * Được include vào mỗi trang để ghi nhận hoạt động
 */

require_once __DIR__ . '/../model/m_statistic.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$stat = new M_statistic();

// Nếu user đã đăng nhập
// Ghi nhận user online cho cả user đã đăng nhập và khách (guest)
try {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $usernameToTrack = $_SESSION['username'];
    } else {
        // Tạo username cho guest dựa trên session id để phân biệt các phiên duyệt
        $sid = session_id();
        if (empty($sid)) session_start();
        $sid = session_id();
        $usernameToTrack = 'guest_' . substr($sid, 0, 20);
        // Ghi debug ngắn gọn vào log để biết có guest không
        error_log("ℹ️ Tracking guest session as: " . $usernameToTrack);
    }

    $result = $stat->registerUserOnline($usernameToTrack);
    if (!$result) {
        error_log("⚠️ registerUserOnline returned: " . var_export($result, true) . " for user: " . $usernameToTrack);
    }
} catch (Exception $e) {
    error_log("❌ User tracking error: " . $e->getMessage());
}

// Ghi nhận page view
if (!empty($_SERVER['REQUEST_URI'])) {
    try {
        // Lấy page slug từ URL
        $pageSlug = basename($_SERVER['REQUEST_URI']);
        if (strpos($pageSlug, '?') !== false) {
            $pageSlug = substr($pageSlug, 0, strpos($pageSlug, '?'));
        }
        if (empty($pageSlug) || $pageSlug === 'index.php') {
            $pageSlug = 'homepage';
        }
        
        // Ghi nhận page view
        //$stat->trackPageView($pageSlug); // Optional
    } catch (Exception $e) {
        // Nếu bảng page_views chưa tồn tại, sẽ tạo sau
        error_log("Page view tracking error: " . $e->getMessage());
    }
}
?>
