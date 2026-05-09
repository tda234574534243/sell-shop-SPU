<?php
/**
 * Central logger used by the application.
 * Writes to logs/actions.log and registers basic error/exception handlers.
 */
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

function ensure_logs_dir() {
    $dir = realpath(__DIR__ . '/../logs');
    if ($dir === false) $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return rtrim($dir, DIRECTORY_SEPARATOR);
}

function current_user_ident() {
    if (!empty($_SESSION['username'])) return $_SESSION['username'];
    if (!empty($_SESSION['user_id'])) return 'user#' . $_SESSION['user_id'];
    return 'guest';
}

function format_meta($meta) {
    if (empty($meta)) return '';
    $parts = [];
    foreach ($meta as $k => $v) {
        if (is_scalar($v)) $parts[] = "$k=" . str_replace(["\n","\r"], ['\n','\r'], (string)$v);
        else $parts[] = "$k=" . json_encode($v, JSON_UNESCAPED_UNICODE);
    }
    return implode(' ', $parts);
}

function log_action($level, $message, $meta = []) {
    $dir = ensure_logs_dir();
    $file = $dir . DIRECTORY_SEPARATOR . 'actions.log';
    $time = date('Y-m-d H:i:s');
    $user = current_user_ident();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $uri = $_SERVER['REQUEST_URI'] ?? (php_sapi_name() === 'cli' ? 'cli' : 'unknown');
    $metaStr = format_meta($meta);
    $line = "[{$time}] [{$level}] [{$user}] [{$ip}] [{$uri}] {$message}";
    if ($metaStr !== '') $line .= ' ' . $metaStr;
    $line .= PHP_EOL;
    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

// Error/exception handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = "PHP_ERROR: {$errstr} in {$errfile}:{$errline} (errno={$errno})";
    log_action('ERROR', $msg);
    return false;
});

set_exception_handler(function($ex) {
    $msg = "Uncaught Exception: " . $ex->getMessage() . " in " . $ex->getFile() . ':' . $ex->getLine();
    log_action('EXCEPTION', $msg, ['trace' => $ex->getTraceAsString()]);
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && ($err['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
        $msg = "Shutdown error: " . ($err['message'] ?? '') . " in " . ($err['file'] ?? '') . ':' . ($err['line'] ?? '');
        log_action('FATAL', $msg);
    }
});

?>
