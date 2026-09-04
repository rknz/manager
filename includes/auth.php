<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Prevent caching for all API responses that include auth
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once __DIR__ . '/../config/db.php';

function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base = preg_replace('#[\\\\/](api|views|includes)$#i', '', $scriptDir);
    if ($base === '\\' || $base === '/' || $base === '.' || $base === false) {
        $base = '';
    }
    return $base;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $base = getBasePath();
        if (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false)
        ) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Session expired.', 'redirect' => $base . '/login']);
            exit;
        }
        header('Location: ' . $base . '/login');
        exit;
    }
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['role'] === $role;
}

function verifyAdminAction() {
    if (!isLoggedIn()) return false;
    // Allow if user is admin or owner
    if (in_array($_SESSION['role'] ?? '', ['admin', 'owner'])) return true;
    // Allow if admin password was verified in the last 5 minutes (300s)
    if (isset($_SESSION['admin_auth_time']) && (time() - $_SESSION['admin_auth_time'] < 300)) return true;
    return false;
}

function checkSessionTimeout() {
    global $pdo;
    if (isLoggedIn()) {
        $timeout = 7200;
        try {
            $stmt = $pdo->query("SELECT setting_value FROM app_settings WHERE setting_key = 'session_timeout'");
            $val = $stmt->fetchColumn();
            if ($val) $timeout = (int) $val;
        } catch (Exception $e) {}
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            session_unset();
            session_destroy();
            $base = dirname($_SERVER['SCRIPT_NAME']);
            if ($base === '\\' || $base === '/') $base = '';
            header("Location: $base/login?expired=1");
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
checkSessionTimeout();
