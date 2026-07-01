<?php
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Base URL sistem
if (!defined('BASE_URL')) {
    if (isset($_SERVER['RAILWAY_PUBLIC_DOMAIN'])) {
        define('BASE_URL', 'https://' . $_SERVER['RAILWAY_PUBLIC_DOMAIN'] . '/');
    } else {
        define('BASE_URL', 'http://localhost/sipinv/');
    }
}

// Konfigurasi koneksi database (dinamis: localhost atau Railway)
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'db_inventaris_sipinv');
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

// Membuat koneksi
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if (!$koneksi) {
    die("<p style='color:red; font-family:sans-serif; padding:20px;'>
        <strong>Koneksi database gagal!</strong><br>
        Error: " . mysqli_connect_error() . "
    </p>");
}

// Set charset agar karakter khusus terbaca dengan benar
mysqli_set_charset($koneksi, 'utf8mb4');
?>