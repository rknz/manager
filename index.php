<?php
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ini_set('display_errors', 0);
require_once __DIR__ . '/includes/auth.php';

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '\\' || $basePath === '/') {
    $basePath = '';
}
if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

if ($path === '/' || $path === '' || $path === false || $path === '/index.php') {
    if (isLoggedIn()) { header('Location: ' . $basePath . '/dashboard'); }
    else { header('Location: ' . $basePath . '/login'); }
    exit;
}

switch ($path) {
    case '/login':
        if (isLoggedIn()) { header('Location: ' . $basePath . '/dashboard'); exit; }
        require __DIR__ . '/views/login.php';
        break;
    case '/print_bill':
        requireLogin();
        require __DIR__ . '/views/print_bill.php';
        break;
    case '/dashboard':
        requireLogin();
        require __DIR__ . '/views/dashboard.php';
        break;
    case '/print/bill':
        requireLogin();
        require __DIR__ . '/views/print_bill.php';
        break;
    case '/print_manual_bill':
        requireLogin();
        require __DIR__ . '/views/print_manual_bill.php';
        break;
    case '/print_advances':
        requireLogin();
        require __DIR__ . '/views/print_advances.php';
        break;
    case '/print_worker_report':
        requireLogin();
        require __DIR__ . '/views/print_worker_report.php';
        break;
    case '/print_custom_bill':
        requireLogin();
        require __DIR__ . '/views/print_custom_bill.php';
        break;
    case '/settings':
        requireLogin();
        require __DIR__ . '/views/settings.php';
        break;
    case '/reports':
        requireLogin();
        require __DIR__ . '/views/reports.php';
        break;
    case '/print-bill':
        requireLogin();
        require __DIR__ . '/views/print_bill.php';
        break;
    case '/workers':
        requireLogin();
        require __DIR__ . '/views/workers.php';
        break;
    case '/contractors':
        requireLogin();
        require __DIR__ . '/views/contractors.php';
        break;
    case '/projects':
        requireLogin();
        require __DIR__ . '/views/projects.php';
        break;
    case '/project-detail':
        requireLogin();
        require __DIR__ . '/views/project-detail.php';
        break;
    case '/quick-purchase':
        requireLogin();
        require __DIR__ . '/views/quick-purchase.php';
        break;
    case '/daily-labor':
        requireLogin();
        require __DIR__ . '/views/daily-labor.php';
        break;
    case '/payments':
        requireLogin();
        require __DIR__ . '/views/payments.php';
        break;
    case '/backup':
        requireLogin();
        require __DIR__ . '/views/backup.php';
        break;
    case '/forgot-password':
        require __DIR__ . '/views/forgot-password.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}

