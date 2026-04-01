<?php
require_once __DIR__ . '/../model/m_statistic.php';

$stat = new M_statistic();

echo "Running online users test...\n";

try {
    $result = $stat->getOnlineUsers();
    $count = $stat->getOnlineUserCount();

    echo "Online count (from getOnlineUserCount): " . $count . "\n";

    if ($result === null) {
        echo "getOnlineUsers() returned null (table might be missing or query failed)\n";
    } elseif ($result === false) {
        echo "getOnlineUsers() returned false (query error)\n";
    } elseif (is_array($result)) {
        $rows = $result;
        echo "getOnlineUsers() returned " . count($rows) . " rows:\n";
        if (count($rows) > 0) {
            $nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            foreach ($rows as $r) {
                $cands = [];
                try { $dt1 = new DateTime($r['last_activity'], new DateTimeZone('UTC')); $cands[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                try { $dt2 = new DateTime($r['login_time'], new DateTimeZone('UTC')); $cands[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                $s1 = strtotime($r['last_activity']); if ($s1 !== false) $cands[] = $s1;
                $s2 = strtotime($r['login_time']); if ($s2 !== false) $cands[] = $s2;
                $most = count($cands) ? max($cands) : 0;
                $diff = $nowUtc - $most;
                $isOnline = ($most > 0 && $diff < 30 * 60);
                echo json_encode($r, JSON_UNESCAPED_UNICODE) . " | most=" . $most . " | diffSec=" . $diff . " | online=" . ($isOnline? 'YES':'NO') . "\n";
            }
        }
    } elseif (is_object($result)) {
        // Backwards compatibility: older getOnlineUsers() returned mysqli_result
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo "getOnlineUsers() returned " . count($rows) . " rows:\n";
        if (count($rows) > 0) {
            $nowUtc = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            foreach ($rows as $r) {
                $cands = [];
                try { $dt1 = new DateTime($r['last_activity'], new DateTimeZone('UTC')); $cands[] = $dt1->getTimestamp(); } catch (Exception $e) {}
                try { $dt2 = new DateTime($r['login_time'], new DateTimeZone('UTC')); $cands[] = $dt2->getTimestamp(); } catch (Exception $e) {}
                $s1 = strtotime($r['last_activity']); if ($s1 !== false) $cands[] = $s1;
                $s2 = strtotime($r['login_time']); if ($s2 !== false) $cands[] = $s2;
                $most = count($cands) ? max($cands) : 0;
                $diff = $nowUtc - $most;
                $isOnline = ($most > 0 && $diff < 30 * 60);
                echo json_encode($r, JSON_UNESCAPED_UNICODE) . " | most=" . $most . " | diffSec=" . $diff . " | online=" . ($isOnline? 'YES':'NO') . "\n";
            }
        }
    } else {
        echo "getOnlineUsers() returned unexpected type\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

?>