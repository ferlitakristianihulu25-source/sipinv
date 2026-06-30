<?php
session_start();

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIPINV PT Salim Ivomas Pratama</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a5c2a 0%, #2d8a45 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
        }
        .logo-area {
            background: linear-gradient(135deg, #1a5c2a, #2d8a45);
            border-radius: 16px 16px 0 0;
            padding: 32px 20px 24px;
            text-align: center;
        }
        .logo-area h4 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 1.1rem;
        }
        .logo-area p {
            color: rgba(255,255,255,0.75);
            font-size: 0.82rem;
            margin: 0;
        }
        .logo-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .form-control:focus {
            border-color: #2d8a45;
            box-shadow: 0 0 0 0.2rem rgba(45,138,69,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #1a5c2a, #2d8a45);
            border: none;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #154d23, #267a3c);
        }
    </style>
</head>
<body>
    <div class="card-login card">
        <!-- Header kartu -->
        <div class="logo-area">
            <div class="logo-icon">
                <i class="bi bi-box-seam fs-2 text-white"></i>
            </div>
            <h4>SIPINV</h4>
            <p>Sistem Informasi Inventaris Barang</p>
            <p>PT Salim Ivomas Pratama</p>
        </div>

        <!-- Form login -->
        <div class="card-body p-4">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>
                        <?php
                        if ($_GET['error'] == 'wrong') echo 'Email atau password salah.';
                        if ($_GET['error'] == 'empty') echo 'Email dan password wajib diisi.';
                        ?>
                    </span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control"
                            placeholder="Masukkan email" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Masukkan password" required>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login btn-success text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </div>
            </form>

            <p class="text-center text-muted mt-4 mb-0" style="font-size:0.78rem;">
                &copy; <?= date('Y') ?> PT Salim Ivomas Pratama
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>