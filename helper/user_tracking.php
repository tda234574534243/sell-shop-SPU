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
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    try {
        // Ghi nhận user online
        $result = $stat->registerUserOnline($_SESSION['username']);
        
        // Debug: Log thành công
        if (!$result) {
            error_log("⚠️ registerUserOnline returned: " . var_export($result, true) . " for user: " . $_SESSION['username']);
        }
    } catch (Exception $e) {
        // Nếu bảng users_online chưa tồn tại, sẽ tạo sau
        error_log("❌ User tracking error: " . $e->getMessage());
    }
} else {
    // Debug: Log khi user chưa đăng nhập
    error_log("ℹ️ User not logged in - Session data: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . ", username=" . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET'));
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
