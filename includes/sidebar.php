<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-box-seam text-white fs-4"></i>
        <div>
            <span>SIPINV</span>
            <small>PT Salim Ivomas Pratama</small>
        </div>
    </div>

    <div class="sidebar-menu">
        <!-- Dashboard -->
        <div class="sidebar-label">Utama</div>
        <a href="<?= BASE_URL ?>pages/dashboard/index.php"
           class="<?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- Master Data -->
        <div class="sidebar-label">Master Data</div>
        <a href="<?= BASE_URL ?>pages/kategori/index.php"
           class="<?= ($active_menu ?? '') == 'kategori' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Kategori Barang
        </a>
        <a href="<?= BASE_URL ?>pages/barang/index.php"
           class="<?= ($active_menu ?? '') == 'barang' ? 'active' : '' ?>">
            <i class="bi bi-archive"></i> Data Barang
        </a>

        <!-- Transaksi -->
        <div class="sidebar-label">Transaksi</div>
        <a href="<?= BASE_URL ?>pages/transaksi/masuk.php"
           class="<?= ($active_menu ?? '') == 'masuk' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
        </a>
        <a href="<?= BASE_URL ?>pages/transaksi/keluar.php"
           class="<?= ($active_menu ?? '') == 'keluar' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-up"></i> Barang Keluar
        </a>

        <!-- Laporan -->
        <div class="sidebar-label">Laporan</div>
        <a href="<?= BASE_URL ?>pages/laporan/index.php"
           class="<?= ($active_menu ?? '') == 'laporan' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> Laporan Inventaris
        </a>

        <!-- Admin only -->
        <?php if (($_SESSION['user_role'] ?? '') == 'admin'): ?>
        <div class="sidebar-label">Pengaturan</div>
        <a href="<?= BASE_URL ?>pages/users/index.php"
           class="<?= ($active_menu ?? '') == 'users' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Manajemen User
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- NAVBAR ATAS -->
<div class="navbar-top">
    <div class="brand">
        <i class="bi bi-grid-3x3-gap me-2"></i>
        Sistem Informasi Inventaris Barang
    </div>
    <div class="user-info">
        <i class="bi bi-person-circle fs-5"></i>
        <span><?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?></span>
        <span class="badge-role"><?= $_SESSION['user_role'] ?? '' ?></span>
        <a href="<?= BASE_URL ?>pages/auth/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</div>

<!-- KONTEN UTAMA -->
<div class="main-content">
    <div class="content-area">