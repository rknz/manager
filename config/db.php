<?php
// Force no-cache for real-time data on all pages and APIs
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Load .env file if available
$envFile = dirname(__DIR__) . '/.env';
if (!file_exists($envFile)) {
    $envFile = dirname(__DIR__) . '/.env.production';
}
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }
}

$host     = getenv('DB_HOST')     ?: ($_ENV['DB_HOST']     ?? 'localhost');
$port     = getenv('DB_PORT')     ?: ($_ENV['DB_PORT']     ?? '3306');
$dbname   = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'lily_app');
$username = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root');
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($_ENV['DB_PASSWORD'] ?? '');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    die("<div style='font-family:sans-serif;padding:24px;background:#0b0f19;color:#fff;max-width:600px;margin:50px auto;border-radius:12px;border:1px solid #7A1A1E;'>
        <h2 style='color:#f87171;margin-top:0;'>Profix — Database Connection Notice</h2>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p style='color:#94a3b8;font-size:0.9rem;'>Please ensure MySQL is running and verify database settings in <code>.env</code> or <code>config/db.php</code>.</p>
    </div>");
}