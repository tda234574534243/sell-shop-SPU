<?php
session_start();
if (!isset($_SESSION['levelID']) || $_SESSION['levelID'] != 1) {
    header('Location: ../signIn.php');
    exit;
}

// Load logger if available; otherwise provide a minimal fallback to avoid fatal errors
if (file_exists(__DIR__ . '/../helper/logger.php')) {
    require_once __DIR__ . '/../helper/logger.php';
} else {
    function log_action($level, $message, $meta = []) {
        $dir = realpath(__DIR__ . '/../logs') ?: __DIR__ . '/../logs';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $file = $dir . DIRECTORY_SEPARATOR . 'actions.log';
        $time = date('Y-m-d H:i:s');
        $user = $_SESSION['username'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $uri = $_SERVER['REQUEST_URI'] ?? 'cli';
        $metaStr = '';
        if (!empty($meta)) $metaStr = ' ' . json_encode($meta, JSON_UNESCAPED_UNICODE);
        $line = "[{$time}] [{$level}] [{$user}] [{$ip}] [{$uri}] {$message}" . $metaStr . PHP_EOL;
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
require_once __DIR__ . '/../model/m_account.php';

$logsDir = realpath(__DIR__ . '/../logs');
if ($logsDir === false) $logsDir = __DIR__ . '/../logs';

$available = [];
if (is_dir($logsDir)) {
    $files = scandir($logsDir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        if (is_file($logsDir . DIRECTORY_SEPARATOR . $f) && preg_match('/\.log$/i', $f)) {
            $available[] = $f;
        }
    }
}

$selected = $_GET['file'] ?? ($_POST['file'] ?? ($available[0] ?? ''));
if ($selected && !in_array($selected, $available)) {
    $selected = '';
}

$message = '';

// Download
if (!empty($_GET['download']) && $selected) {
    $path = $logsDir . DIRECTORY_SEPARATOR . $selected;
    if (is_file($path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// Reset file (requires password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset' && $selected) {
    $pwd = $_POST['admin_password'] ?? '';
    if (empty($pwd)) {
        $message = 'Vui lòng nhập mật khẩu để xác nhận.';
    } else {
        $acct = new M_account();
        $res = $acct->getAccount($_SESSION['user_id'] ?? 0);
        $row = ($res && $res->num_rows) ? $res->fetch_assoc() : null;
        $hash = $row['Password'] ?? '';
        if ($hash && password_verify($pwd, $hash)) {
            $path = $logsDir . DIRECTORY_SEPARATOR . $selected;
            if (is_file($path)) {
                file_put_contents($path, "");
                $message = 'Đã xóa nội dung file ' . htmlspecialchars($selected);
                log_action('INFO', 'Log file reset by admin', ['file' => $selected, 'user' => $_SESSION['username'] ?? 'admin']);
            } else {
                $message = 'File không tồn tại.';
            }
        } else {
            $message = 'Mật khẩu không đúng.';
            log_action('WARNING', 'Failed log reset attempt', ['user' => $_SESSION['username'] ?? 'unknown']);
        }
    }
}

// Clear file without password (legacy clear) - disabled
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear' && $selected) {
    $message = 'Sử dụng chức năng reset (với mật khẩu) để xóa log.';
}

function tail_file($file, $lines = 200) {
    if (!is_file($file)) return '';
    $f = fopen($file, 'rb');
    if (!$f) return '';
    $buffer = '';
    $chunk = 4096;
    fseek($f, 0, SEEK_END);
    $pos = ftell($f);
    $linecnt = 0;
    while ($pos > 0 && $linecnt <= $lines) {
        $read = ($pos - $chunk) > 0 ? $chunk : $pos;
        $pos -= $read;
        fseek($f, $pos);
        $data = fread($f, $read);
        $buffer = $data . $buffer;
        $linecnt = substr_count($buffer, "\n");
    }
    fclose($f);
    $linesArr = preg_split('/\r?\n/', trim($buffer));
    $last = array_slice($linesArr, -$lines);
    return implode("\n", $last);
}

$content = '';
if ($selected) {
    $path = $logsDir . DIRECTORY_SEPARATOR . $selected;
    $content = tail_file($path, 1000);
}

?>
<?php include "../template/sidebar.php"; ?>
<div class="container" style="margin-left:260px;padding:20px;">
    <h4 class="fw-bold">Xem Log</h4>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Chọn file log</label>
                <select name="file" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Chọn --</option>
                    <?php foreach ($available as $f): ?>
                        <option value="<?php echo htmlspecialchars($f); ?>" <?php echo ($f === $selected) ? 'selected' : ''; ?>><?php echo htmlspecialchars($f); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="btn-group ms-2">
                    <a class="btn btn-secondary" href="?file=<?php echo urlencode($selected); ?>&download=1">Tải xuống</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card mb-3">
        <div class="card-body" style="white-space:pre-wrap; font-family: monospace; max-height:60vh; overflow:auto;">
            <?php echo htmlspecialchars($content); ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Xóa/Reset file log (yêu cầu mật khẩu admin)</h5>
            <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn reset file log?');">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($selected); ?>" />
                <input type="hidden" name="action" value="reset" />
                <div class="mb-2">
                    <label class="form-label">Nhập mật khẩu hiện tại của bạn</label>
                    <input type="password" name="admin_password" class="form-control" required />
                </div>
                <button class="btn btn-danger" type="submit">Reset log</button>
            </form>
        </div>
    </div>
</div>
