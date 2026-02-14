<?php
// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// App Configuration
define('APP_NAME', getenv('APP_NAME') ?: 'Camping Rental');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/camping-rental-apps');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'camping');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Session Configuration
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 7200);

// Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_MAX_SIZE', 3 * 1024 * 1024); // 3MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// // Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}

// function setFlashMessage($message, $type = 'success')
// {
//     $_SESSION['flash_message'] = [
//         'message' => $message,
//         'type' => $type
//     ];
// }