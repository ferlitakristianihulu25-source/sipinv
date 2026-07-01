<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';


// Cek apakah form disubmit via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input kosong
if (empty($email) || empty($password)) {
    header('Location: login.php?error=empty');
    exit();
}

// Cari user berdasarkan email
$email  = mysqli_real_escape_string($koneksi, $email);
$query  = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verifikasi password dengan bcrypt
    if (password_verify($password, $user['password'])) {
        // Login berhasil, simpan session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];

        header('Location: ../dashboard/index.php');
        exit();
    }
}

// Login gagal
header('Location: login.php?error=wrong');
exit();
?>