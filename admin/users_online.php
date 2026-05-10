<?php include "../template/sidebar.php"; ?>
<?php include ('../template/toastMess.php') ?>
<?php
    require_once '../model/m_statistic.php';
    $stat = new M_statistic();

    // Ensure current session is registered before querying list/count
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['username'])) {
        // Best-effort register (ignore return)
        $stat->registerUserOnline($_SESSION['username']);
    }
    
    $result = null;
    $onlineCount = 0;
    $setupRequired = false;
    
    try {
        // Cleanup offline users
        $stat->cleanupOfflineUsers();
        
        // Get online users
        $result = $stat->getOnlineUsers();
        
        // Handle null/false result (table doesn't exist or query failed)
        if ($result === null || $result === false) {
            $setupRequired = true;
            $result = null; // Ensure it's null for later checks
        }
        
        // Get online user count
        $onlineCount = $stat->getOnlineUserCount();
    } catch (Exception $e) {
        // Database error - setup required
        $setupRequired = true;
        $result = null;
        error_log("Users online error: " . $e->getMessage());
    }
?>
<?php include('../template/head.php'); ?>

<style>
    #mainContent {
        margin-left: 250px;
        color: #212529; /* ensure readable dark text on admin white cards */
    }
    
    .online-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #28a745;
        border-radius: 50%;
        margin-right: 8px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .online-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .online-table table {
        margin: 0;
        width: 100%;
    }
    
    .online-table td, .online-table th {
        padding: 15px;
        vertical-align: middle;
    }
    
    .online-table th {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    
    .online-table tbody tr {
        border-bottom: 1px solid #dee2e6;
        transition: background 0.2s;
    }
    
    .online-table tbody tr:hover {
        background: #f9f9f9;
    }
    
    .idle-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .idle-badge-fresh {
        background: #d4edda;
        color: #155724;
    }
    
    .idle-badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .idle-badge-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 10px;
    }
    
    .stat-label {
        color: #666;
        font-size: 14px;
    }
</style>

<div class="bg-light flex-fill">
    <div id="mainContent" class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">
                <span class="online-indicator"></span> 
                Quản lý người dùng đang online
            </h4>
            <button onclick="location.reload()" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-rotate-right"></i> Làm mới
            </button>
        </div>

        <!-- Statistics -->
        <div class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-number"><?= $onlineCount ?></div>
                        <div class="stat-label">Người dùng đang online</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-number"><?= $onlineCount > 0 ? '🟢' : '🔴' ?></div>
                        <div class="stat-label">Trạng thái hệ thống</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Users Table -->
        <div class="online-table">
            <?php if ($setupRequired): ?>
                <div style="padding: 40px; text-align: center;">
                    <i class="fa-solid fa-database" style="font-size: 48px; margin-bottom: 20px; color: #dc3545;"></i>
                    <p style="color: #666; margin-bottom: 20px;">
                        <strong>⚠️ Chưa khởi tạo database</strong><br>
                        Vui lòng chạy setup trước
                    </p>
                    <a href="../setup_statistic.php" class="btn btn-danger" target="_blank">
                        <i class="fa-solid fa-gears"></i> Khởi tạo ngay
                    </a>
                </div>
            <?php else:
                // Normalize result to an array of rows (getOnlineUsers() now returns an array)
                $rows = [];
                if (is_array($result)) {
                    $rows = $result;
                } elseif (is_object($result)) {
                    // Backwards compatibility: if a mysqli_result slipped through
                    while ($r = $result->fetch_assoc()) {
                        $rows[] = $r;
                    }
                }

                // Filter using UTC DateTime to avoid MySQL/PHP timezone mismatches
                $nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
                // Optional debug: when ?dbg=1, log rows and filtering steps to logs/db_errors.log
                $debugMode = isset($_GET['dbg']) && $_GET['dbg'] == '1';
                $logDir = __DIR__ . '/../logs';
                if ($debugMode && !is_dir($logDir)) @mkdir($logDir, 0755, true);

                // Fallback: nếu model trả về rỗng nhưng bảng có dữ liệu (ví dụ do hàm model không trả về mảng),
                // thử lấy trực tiếp từ DB và dùng kết quả để hiển thị. Giúp đưa phần debug ra trang chính.
                if (count($rows) === 0) {
                    $conn = null;
                    if (method_exists($stat, 'getConnection')) {
                        $conn = $stat->getConnection();
                    } elseif (property_exists($stat, 'conn')) {
                        $conn = $stat->conn;
                    }
                    if ($conn) {
                        $raw = $conn->query('SELECT * FROM users_online ORDER BY last_activity DESC');
                        if ($raw !== false && $raw->num_rows > 0) {
                            $rows = $raw->fetch_all(MYSQLI_ASSOC);
                            if ($debugMode) {
                                $msg2 = date('Y-m-d H:i:s') . " | users_online debug: fallback_raw_rows=" . count($rows) . "\n";
                                @error_log($msg2, 3, $logDir . '/db_errors.log');
                            }
                        }
                    }
                }

                $onlineRows = array_filter($rows, function($r) use ($nowUtc) {
                    $candidates = [];
                    // try DateTime UTC parse
                    try {
                        $dt1 = new DateTime($r['last_activity'], new DateTimeZone('UTC'));
                        $candidates[] = $dt1->getTimestamp();
                    } catch (Exception $e) {
                    }
                    try {
                        $dt2 = new DateTime($r['login_time'], new DateTimeZone('UTC'));
                        $candidates[] = $dt2->getTimestamp();
                    } catch (Exception $e) {
                    }
                    // fallback to strtotime (server-default timezone)
                    $s1 = strtotime($r['last_activity']);
                    if ($s1 !== false) $candidates[] = $s1;
                    $s2 = strtotime($r['login_time']);
                    if ($s2 !== false) $candidates[] = $s2;

                    if (count($candidates) === 0) return false;
                    $mostRecent = max($candidates);
                    return ($mostRecent > $nowUtc - 30 * 60);
                });

                if ($debugMode) {
                    $msg = date('Y-m-d H:i:s') . " | users_online debug: fetched_rows=" . count($rows) . "\n";
                    foreach ($rows as $rr) {
                        $msg .= json_encode($rr) . "\n";
                    }
                    $msg .= "filtered_count=" . count($onlineRows) . "\n";
                    @error_log($msg, 3, $logDir . '/db_errors.log');
                        echo '<div style="padding:10px;background:#fff3cd;color:#856404;border-radius:6px;margin-bottom:10px;"><strong>Debug:</strong> logged users_online details to logs/db_errors.log</div>';
                        // Also show filtered_count and computed timestamps for each row
                        echo '<div style="padding:10px;background:#f0f8ff;border-radius:6px;margin-bottom:10px;">';
                        echo '<strong>Computed filter details:</strong><br/>';
                        $nowUtcDisplay = $nowUtc;
                        echo 'nowUtc: ' . $nowUtcDisplay . '<br/>';
                        foreach ($rows as $rr) {
                            $cands = [];
                            try { $dt1 = new DateTime($rr['last_activity'], new DateTimeZone('UTC')); $cands[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                            try { $dt2 = new DateTime($rr['login_time'], new DateTimeZone('UTC')); $cands[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                            $s1 = strtotime($rr['last_activity']); if ($s1 !== false) $cands[] = $s1;
                            $s2 = strtotime($rr['login_time']); if ($s2 !== false) $cands[] = $s2;
                            $most = count($cands) ? max($cands) : 0;
                            echo 'user=' . htmlspecialchars($rr['username']) . ' candidates=' . json_encode($cands) . ' mostRecent=' . $most . ' diffSeconds=' . ($nowUtc - $most) . '<br/>';
                        }
                        echo 'filtered_count=' . count($onlineRows) . '<br/>';
                        echo '</div>';

                    // Also show raw SELECT * on the page for quick inspection
                    $conn = $stat->getConnection();
                    $raw = $conn->query('SELECT * FROM users_online ORDER BY last_activity DESC');
                    if ($raw === false) {
                        echo '<div style="padding:10px;background:#f8d7da;color:#721c24;border-radius:6px;margin-bottom:10px;"><strong>DB Error:</strong> ' . htmlspecialchars($conn->error) . '</div>';
                    } else {
                        echo '<div style="padding:10px;background:#e9ecef;border-radius:6px;margin-bottom:10px;">';
                        echo '<strong>Raw users_online rows: </strong> Count: ' . $raw->num_rows;
                        if ($raw->num_rows > 0) {
                            echo '<pre>' . htmlspecialchars(print_r($raw->fetch_all(MYSQLI_ASSOC), true)) . '</pre>';
                        }
                        echo '</div>';
                    }
                }
                
                // Always show table of all fetched rows
                if (count($rows) > 0):
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Địa chỉ IP</th>
                            <th>Thời gian đăng nhập</th>
                            <th>Hoạt động gần đây</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row):
                        // Compute most recent timestamp using multiple strategies
                        $candidates = [];
                        try { $dt1 = new DateTime($row['last_activity'], new DateTimeZone('UTC')); $candidates[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                        try { $dt2 = new DateTime($row['login_time'], new DateTimeZone('UTC')); $candidates[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                        $s1 = strtotime($row['last_activity']); if ($s1 !== false) $candidates[] = $s1;
                        $s2 = strtotime($row['login_time']); if ($s2 !== false) $candidates[] = $s2;
                        $mostRecent = count($candidates) ? max($candidates) : 0;
                        $totalSeconds = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp() - $mostRecent;
                        if ($mostRecent === 0) {
                            $badge = '<span class="idle-badge idle-badge-danger">Không xác định</span>';
                            $statusText = 'Không biết';
                        } elseif ($totalSeconds < 30 * 60) {
                            // within 30 minutes -> online
                            $badge = '<span class="idle-badge idle-badge-fresh">Hoạt động</span>';
                            $statusText = 'Trực tuyến';
                        } elseif ($totalSeconds < 24 * 60 * 60) {
                            $badge = '<span class="idle-badge idle-badge-warning">' . intval($totalSeconds / 60) . ' phút trước</span>';
                            $statusText = 'Không hoạt động';
                        } else {
                            $badge = '<span class="idle-badge idle-badge-danger">' . intval($totalSeconds / 60) . ' phút trước</span>';
                            $statusText = 'Offline';
                        }
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                            <td><code><?= htmlspecialchars($row['ip_address']) ?></code></td>
                            <td><small class="text-muted"><?= htmlspecialchars($row['login_time']) ?></small></td>
                            <td><?= $badge ?></td>
                            <td><span class="badge <?= ($totalSeconds < 30*60) ? 'bg-success' : 'bg-secondary' ?>"><?= $statusText ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #999;">
                    <i class="fa-solid fa-user-slash" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p>Hiện tại không có người dùng nào đang online</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Auto-refresh notice -->
        <div class="alert alert-info mt-4" role="alert">
            <i class="fa-solid fa-info-circle"></i> 
            <strong>Ghi chú:</strong> Người dùng được coi là "online" nếu hoạt động trong 30 phút gần đây.
            <button onclick="setAutoRefresh()" class="btn btn-sm btn-outline-info ms-3">Bật làm mới tự động</button>
        </div>
    </div>
</div>

<?php include "../template/script_footer.php"; ?>

<script>
    function setAutoRefresh() {
        const interval = prompt('Nhập khoảng thời gian làm mới (giây):', '30');
        if (interval && !isNaN(interval)) {
            setInterval(() => {
                location.reload();
            }, interval * 1000);
            alert('Sẽ tự động làm mới mỗi ' + interval + ' giây');
        }
    }
</script>
