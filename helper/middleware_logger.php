<?php
/**
 * Middleware logger to be included on every request (via template/head.php)
 * Captures session username (or guest), URI, method, and sanitized POST data.
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Ensure logger is available
if (!file_exists(__DIR__ . '/logger.php')) {
    return;
}
require_once __DIR__ . '/logger.php';

// Helper to sanitize POST data
function sanitize_post($post) {
    $sensitive = ['password','pwd','pass','token','secret','ssn','credit','card','cvv'];
    $out = [];
    foreach ($post as $k => $v) {
        $low = strtolower($k);
        $isSensitive = false;
        foreach ($sensitive as $s) {
            if (strpos($low, $s) !== false) { $isSensitive = true; break; }
        }
        if ($isSensitive) continue; // remove sensitive fields
        // limit large values
        if (is_scalar($v)) {
            $val = (string)$v;
            if (strlen($val) > 200) $val = substr($val, 0, 200) . '...';
            $out[$k] = $val;
        } else {
            $out[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
        }
    }
    return $out;
}

// Build context
$user = !empty($_SESSION['username']) ? $_SESSION['username'] : 'guest';
$uri = $_SERVER['REQUEST_URI'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Avoid logging static asset requests (optional)
$skipExtensions = ['.css','.js','.png','.jpg','.jpeg','.gif','.svg','.ico'];
foreach ($skipExtensions as $ext) {
    if (stripos($uri, $ext) !== false) {
        return;
    }
}

$meta = [
    'user' => $user,
    'method' => $method,
    'uri' => $uri,
    'time' => date('c')
];

if ($method === 'POST') {
    $san = sanitize_post($_POST);
    if (!empty($_SESSION['levelID']) && $_SESSION['levelID'] == 1) {
        $msg = 'Admin đã gửi dữ liệu';
    } else {
        $msg = 'POST request received';
    }
    if (!empty($san)) {
        $meta['data'] = $san;
    }
    log_action('INFO', $msg, $meta);
} else {
    log_action('INFO', 'Request', $meta);
}

?>
