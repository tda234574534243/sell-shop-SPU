<?php
// Debug page to test user tracking
require_once 'model/m_statistic.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: text/html; charset=utf-8');

echo '<h2>Debug user tracking</h2>';
echo '<p><a href="index.php">Back to site</a></p>';

echo '<h3>Session</h3>';
echo '<pre>' . htmlspecialchars(print_r($_SESSION, true)) . '</pre>';

$stat = new M_statistic();

if (isset($_SESSION['username'])) {
    echo '<h3>Attempting registerUserOnline()</h3>';
    $res = $stat->registerUserOnline($_SESSION['username']);
    echo '<p>registerUserOnline returned: ' . var_export($res, true) . '</p>';
} else {
    echo '<p>No logged-in username in session.</p>';
}

// Show HoaDon and LS_Mua for current user (if available)
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    echo '<h3>HoaDon for current user (MaTK=' . $uid . ')</h3>';
    $conn = $stat->getConnection();
    $hd = $conn->query("SELECT * FROM HoaDon WHERE MaTK = " . $uid);
    if ($hd === false) {
        echo '<p style="color:red;">HoaDon query error: ' . htmlspecialchars($conn->error) . '</p>';
    } else {
        echo '<p>Count: ' . $hd->num_rows . '</p>';
        if ($hd->num_rows > 0) echo '<pre>' . htmlspecialchars(print_r($hd->fetch_all(MYSQLI_ASSOC), true)) . '</pre>';
    }

    echo '<h3>LS_Mua for current user (MaTK=' . $uid . ')</h3>';
    $ls = $conn->query("SELECT * FROM LS_Mua WHERE MaTK = " . $uid);
    if ($ls === false) {
        echo '<p style="color:red;">LS_Mua query error: ' . htmlspecialchars($conn->error) . '</p>';
    } else {
        echo '<p>Count: ' . $ls->num_rows . '</p>';
        if ($ls->num_rows > 0) echo '<pre>' . htmlspecialchars(print_r($ls->fetch_all(MYSQLI_ASSOC), true)) . '</pre>';
    }
}

// Show users_online table rows if exists
try {
    $dbExists = $stat->tableUsersOnlineExists();
    echo '<p>users_online table exists: ' . ($dbExists ? 'YES' : 'NO') . '</p>';
    if ($dbExists) {
        $res = $stat->getOnlineUsers();
        if ($res && is_object($res)) {
            echo '<h3>users_online rows</h3>';
            echo '<table border="1" cellpadding="6" cellspacing="0">';
            echo '<tr><th>id</th><th>username</th><th>session_id</th><th>ip_address</th><th>login_time</th><th>last_activity</th></tr>';
            while ($row = $res->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                echo '<td>' . htmlspecialchars($row['ip_address']) . '</td>';
                echo '<td>' . htmlspecialchars($row['login_time']) . '</td>';
                echo '<td>' . htmlspecialchars($row['last_activity']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No rows or query failed.</p>';
        }
        // Direct DB check (bypass model helper) to show raw table contents and any errors
        echo '<h3>Direct SELECT * check</h3>';
        $conn = $stat->getConnection();
        $raw = $conn->query('SELECT * FROM users_online');
        if ($raw === false) {
            echo '<p style="color: red;">Direct query error: ' . htmlspecialchars($conn->error) . '</p>';
        } else {
            echo '<p>Direct rows count: ' . $raw->num_rows . '</p>';
            if ($raw->num_rows > 0) {
                echo '<pre>' . htmlspecialchars(print_r($raw->fetch_all(MYSQLI_ASSOC), true)) . '</pre>';
            }
        }
    }
} catch (Exception $e) {
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

?>