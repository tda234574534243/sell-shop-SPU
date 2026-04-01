<?php
require_once("m_database.php");

class M_statistic extends M_database {
    
    /**
     * Lấy tổng doanh thu của tháng
     */
    public function getMonthlyRevenue($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $query = "
            SELECT COALESCE(SUM(ls.SoLuong * p.GiaTien), 0) as total
            FROM LS_Mua ls
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE MONTH(ls.NgayMua) = $month AND YEAR(ls.NgayMua) = $year
        ";
        
        $this->setQuery($query);
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Lấy doanh thu theo từng tháng trong năm
     */
    public function getYearlyRevenue($year = null) {
        if (!$year) $year = date('Y');
        
        $query = "
            SELECT 
                MONTH(ls.NgayMua) as month,
                COALESCE(SUM(ls.SoLuong * p.GiaTien), 0) as total
            FROM LS_Mua ls
            JOIN Products p ON p.MaSP = ls.MaSP
            WHERE YEAR(ls.NgayMua) = $year
            GROUP BY MONTH(ls.NgayMua)
            ORDER BY month
        ";
        
        $this->setQuery($query);
        $result = $this->excuteQuery();
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[$row['month']] = $row['total'];
            }
        }
        return $data;
    }

    /**
     * Lấy tổng đơn hàng
     */
    public function getTotalOrders($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $query = "
            SELECT COUNT(DISTINCT MaHD) as total
            FROM LS_Mua
            WHERE MONTH(NgayMua) = $month AND YEAR(NgayMua) = $year
        ";
        
        $this->setQuery($query);
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Lấy tổng sản phẩm
     */
    public function getTotalProducts() {
        $this->setQuery("SELECT COUNT(*) as total FROM Products");
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Lấy tổng khách hàng
     */
    public function getTotalCustomers() {
        // Đếm tổng tài khoản người dùng trong bảng `account`.
        // Sửa tên bảng/cột cho phù hợp với schema hiện tại (LevelID là cột quyền)
        $this->setQuery("SELECT COUNT(*) as total FROM account WHERE LevelID != 1");
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Lấy thay đổi % so với tháng trước (doanh thu)
     */
    public function getRevenueChange($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $currentRevenue = $this->getMonthlyRevenue($month, $year);
        
        // Tháng trước
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }
        
        $prevRevenue = $this->getMonthlyRevenue($prevMonth, $prevYear);
        
        if ($prevRevenue == 0) {
            return $prevRevenue == 0 ? 0 : 100;
        }
        
        return round((($currentRevenue - $prevRevenue) / $prevRevenue) * 100, 2);
    }

    /**
     * Lấy thay đổi % số đơn hàng so với tháng trước
     */
    public function getOrdersChange($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $currentOrders = $this->getTotalOrders($month, $year);
        
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        } else {
            $prevMonth = $month - 1;
            $prevYear = $year;
        }
        
        $prevOrders = $this->getTotalOrders($prevMonth, $prevYear);
        
        if ($prevOrders == 0) {
            return $prevOrders == 0 ? 0 : 100;
        }
        
        return round((($currentOrders - $prevOrders) / $prevOrders) * 100, 2);
    }

    /**
     * Lấy top sản phẩm bán chạy
     */
    public function getTopProducts($limit = 10) {
        $query = "
            SELECT 
                p.MaSP,
                p.TenSP,
                COUNT(ls.MaHD) as times_sold,
                COALESCE(SUM(ls.SoLuong), 0) as total_qty,
                COALESCE(SUM(ls.SoLuong * p.GiaTien), 0) as total_revenue
            FROM LS_Mua ls
            JOIN Products p ON p.MaSP = ls.MaSP
            GROUP BY p.MaSP, p.TenSP
            ORDER BY total_qty DESC
            LIMIT $limit
        ";
        
        $this->setQuery($query);
        return $this->excuteQuery();
    }

    /**
     * Lưu page view
     */
    public function trackPageView($pageSlug, $ip_address = null) {
        if (!$ip_address) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $query = "
            INSERT INTO page_views (page_slug, ip_address, timestamp)
            VALUES ('$pageSlug', '$ip_address', '$timestamp')
        ";
        
        $this->setQuery($query);
        return $this->excuteQuery();
    }

    /**
     * Lấy page views của tháng
     */
    public function getMonthlyPageViews($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $query = "
            SELECT COUNT(*) as total
            FROM page_views
            WHERE MONTH(timestamp) = $month AND YEAR(timestamp) = $year
        ";
        
        $this->setQuery($query);
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Lấy unique visitors
     */
    public function getUniqueVisitors($month = null, $year = null) {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');
        
        $query = "
            SELECT COUNT(DISTINCT ip_address) as total
            FROM page_views
            WHERE MONTH(timestamp) = $month AND YEAR(timestamp) = $year
        ";
        
        $this->setQuery($query);
        $result = $this->excuteQuery();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    /**
     * Đăng ký user online (lưu session)
     */
    public function registerUserOnline($username, $ip_address = null) {
        try {
            // Kiểm tra bảng có tồn tại không
            if (!$this->tableUsersOnlineExists()) {
                return false; // Bảng chưa tạo
            }

            if (!$ip_address) {
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            }
            $sessionId = session_id();
            $usernameEsc = $this->real_escape_string($username);
            $ipEsc = $this->real_escape_string($ip_address);

            // Use MySQL UTC_TIMESTAMP() to set times on the DB side (avoids PHP/MySQL timezone issues)
            $query = "
                INSERT INTO users_online (username, session_id, ip_address, login_time, last_activity)
                VALUES ('$usernameEsc', '$sessionId', '$ipEsc', UTC_TIMESTAMP(), UTC_TIMESTAMP())
                ON DUPLICATE KEY UPDATE last_activity = UTC_TIMESTAMP()
            ";
            $this->setQuery($query);
            $res = $this->excuteQuery();
            if ($res === false) {
                // Additional targeted logging for debugging tracking issues
                $logDir = __DIR__ . '/../logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $message = date('Y-m-d H:i:s') . " | registerUserOnline FAILED for user={$username} session={$sessionId} ip={$ip_address} ts={$timestamp} | Query: " . $this->query . PHP_EOL;
                @error_log($message, 3, $logDir . '/db_errors.log');
            }
            return $res;
        } catch (Exception $e) {
            error_log("registerUserOnline error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật last activity của user
     */
    public function updateUserActivity($username) {
        try {
            if (!$this->tableUsersOnlineExists()) {
                return false;
            }

            $usernameEsc = $this->real_escape_string($username);
            $query = "
                UPDATE users_online 
                SET last_activity = UTC_TIMESTAMP()
                WHERE username = '$usernameEsc'
            ";
            
            $this->setQuery($query);
            return $this->excuteQuery();
        } catch (Exception $e) {
            error_log("updateUserActivity error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra bảng users_online có tồn tại không
     */
    public function tableUsersOnlineExists() {
        try {
            $query = "
                SELECT 1 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'users_online'
            ";
            
            $this->setQuery($query);
            $result = $this->excuteQuery();
            return ($result && $result->num_rows > 0);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách user đang online (30 phút gần đây)
     */
    public function getOnlineUsers() {
        try {
            // Kiểm tra bảng có tồn tại không
            if (!$this->tableUsersOnlineExists()) {
                return null; // Return null nếu bảng chưa tạo
            }
            // Fetch all rows and return as array (normalize return type).
            $query = "SELECT id, username, ip_address, login_time, last_activity FROM users_online ORDER BY last_activity DESC";

            $this->setQuery($query);
            $result = $this->excuteQuery();

            if ($result === false) {
                return null;
            }

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            return $rows;
        } catch (Exception $e) {
            error_log("getOnlineUsers error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy số user đang online
     */
    public function getOnlineUserCount() {
        try {
            // Kiểm tra bảng có tồn tại không
            if (!$this->tableUsersOnlineExists()) {
                return 0; // Return 0 nếu bảng chưa tạo
            }
            // Fetch all and count in PHP to avoid timezone mismatches between PHP and MySQL
            // Also fetch login_time and last_activity and pick the most recent as activity timestamp
            $query = "SELECT id, login_time, last_activity FROM users_online";
            $this->setQuery($query);
            $result = $this->excuteQuery();
            if ($result && $result->num_rows > 0) {
                $count = 0;
                // Compare using multiple parsing strategies to avoid timezone/format issues
                $nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
                $utcTz = new DateTimeZone('UTC');
                while ($row = $result->fetch_assoc()) {
                    $candidates = [];
                    // Prefer parsing as UTC (we set connection timezone to UTC)
                    try { $dt1 = new DateTime($row['last_activity'], $utcTz); $candidates[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                    try { $dt2 = new DateTime($row['login_time'], $utcTz); $candidates[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                    // Fallback to strtotime (should also reflect UTC because session tz set)
                    $s1 = strtotime($row['last_activity']); if ($s1 !== false) $candidates[] = $s1;
                    $s2 = strtotime($row['login_time']); if ($s2 !== false) $candidates[] = $s2;
                    if (count($candidates) === 0) continue;
                    $ts = max($candidates);
                    if ($ts > $nowUtc - 30 * 60) {
                        $count++;
                    }
                }
                return $count;
            }
            return 0;
        } catch (Exception $e) {
            error_log("getOnlineUserCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Xóa user offline (timeout sau 30 phút)
     */
    public function cleanupOfflineUsers() {
        try {
            // Kiểm tra bảng có tồn tại không
            if (!$this->tableUsersOnlineExists()) {
                return false; // Bảng chưa tạo
            }

            // Safer cleanup: fetch all rows and decide in PHP using UTC timestamps
            $this->setQuery("SELECT id, login_time, last_activity FROM users_online");
            $res = $this->excuteQuery();
            if ($res === false) return false;

            $nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            $utcTz = new DateTimeZone('UTC');
            $toDelete = [];
            while ($row = $res->fetch_assoc()) {
                $candidates = [];
                // Prefer parsing as UTC (session TZ set to UTC)
                try { $dt1 = new DateTime($row['last_activity'], $utcTz); $candidates[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                try { $dt2 = new DateTime($row['login_time'], $utcTz); $candidates[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                // Fallback to strtotime
                $s1 = strtotime($row['last_activity']); if ($s1 !== false) $candidates[] = $s1;
                $s2 = strtotime($row['login_time']); if ($s2 !== false) $candidates[] = $s2;
                $mostRecent = count($candidates) ? max($candidates) : 0;
                if ($mostRecent <= $nowUtc - 30 * 60) {
                    $toDelete[] = intval($row['id']);
                }
            }

            if (count($toDelete) === 0) return true;

            $ids = implode(',', $toDelete);
            $delQuery = "DELETE FROM users_online WHERE id IN ($ids)";
            $this->setQuery($delQuery);
            return $this->excuteQuery();
        } catch (Exception $e) {
            error_log("cleanupOfflineUsers error: " . $e->getMessage());
            return false;
        }
    }
}
?>
