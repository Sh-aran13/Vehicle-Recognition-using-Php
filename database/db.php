<?php
// database/db.php
$host = 'localhost';
$db   = 'vehicle';
$user = 'postgres';
$pass = '1328';
$port = '5432';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Global XSS Sanitization Helper
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Session security initialization wrapper
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_use_only_cookies', 1);
        // ini_set('session.cookie_secure', 1); // Uncomment if using HTTPS
        session_start();
    }
}
?>