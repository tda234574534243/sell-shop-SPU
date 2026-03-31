<?php
/**
 * Setup Database Tables for Statistic Module
 * Chạy file này một lần để tạo các bảng cần thiết
 */

require_once 'model/m_database.php';

$db = new M_database();
$conn = $db->getConnection();

// 1. Tạo bảng users_online
$createUsersOnlineTable = "
CREATE TABLE IF NOT EXISTS users_online (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    session_id VARCHAR(255),
    ip_address VARCHAR(50),
    login_time TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_last_activity (last_activity)
)
";

// 2. Tạo bảng page_views
$createPageViewsTable = "
CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(255),
    ip_address VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_slug (page_slug),
    INDEX idx_timestamp (timestamp),
    INDEX idx_ip_address (ip_address)
)
";

// Execute
$result1 = $conn->query($createUsersOnlineTable);
$result2 = $conn->query($createPageViewsTable);

if ($result1 && $result2) {
    echo "<div style='padding: 20px; background: #d4edda; color: #155724; border-radius: 4px;'>
        <strong>✅ Thành công!</strong> Các bảng đã được tạo hoặc đã tồn tại.<br>
        <small>Bạn có thể xóa file setup.php này sau khi chạy.</small>
    </div>";
} else {
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 4px;'>
        <strong>❌ Lỗi:</strong> " . htmlspecialchars($conn->error) . "
    </div>";
}
?>
