<?php

// Base URL sistem
define('BASE_URL', 'http://localhost/sipinv/');

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_inventaris_sipinv');

// Membuat koneksi
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

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