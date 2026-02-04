<?php
// ============================================
// FILE: config/config.php
// FUNGSI: Konfigurasi global aplikasi
// ============================================

// 1. SETTING ERROR REPORTING (Untuk Development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. TIMEZONE
date_default_timezone_set('Asia/Jakarta');

// 3. PATH KONSTANTA
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads/');

// 4. KONFIGURASI UPLOAD FILE
define('MAX_FILE_SIZE', 1048576); // 1MB dalam bytes
define('ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// 5. EMOJI LIST (15 emoji umum)
$EMOJI_LIST = [
    '😀', '😂', '😊', '🤔', '👍',
    '❤️', '🔥', '🎉', '🙏', '👋',
    '😎', '🤖', '💻', '📚', '✅'
];

// 6. FUNGSI HELPER
function sanitizeInput($input) {
    // Bersihkan input dari XSS
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function formatDateTime($timestamp = null) {
    // Format tanggal dan waktu
    if ($timestamp === null) {
        $timestamp = time();
    }
    return date('H:i', $timestamp);
}

// 7. AUTO-LOAD DATABASE CONFIG
require_once 'database.php';

// ============================================
// CATATAN:
// File ini akan di-include di file lain yang
// membutuhkan konfigurasi global
// ============================================
?>