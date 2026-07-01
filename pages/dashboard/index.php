<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';

$page_title  = 'Dashboard';
$active_menu = 'dashboard';

// ===== QUERY STATISTIK =====

// Total barang
$q_total_barang = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
$total_barang   = mysqli_fetch_assoc($q_total_barang)['total'];

// Total kategori
$q_total_kategori = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kategori");
$total_kategori   = mysqli_fetch_assoc($q_total_kategori)['total'];

// Total barang masuk bulan ini
$q_masuk = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM barang_masuk 
            WHERE MONTH(tanggal) = MONTH(NOW()) AND YEAR(tanggal) = YEAR(NOW())");
$total_masuk = mysqli_fetch_assoc($q_masuk)['total'] ?? 0;

// Total barang keluar bulan ini
$q_keluar = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM barang_keluar 
             WHERE MONTH(tanggal) = MONTH(NOW()) AND YEAR(tanggal) = YEAR(NOW())");
$total_keluar = mysqli_fetch_assoc($q_keluar)['total'] ?? 0;

// Data grafik: stok per kategori
$q_grafik = mysqli_query($koneksi, "SELECT k.nama_kategori, SUM(b.stok) as total_stok 
             FROM barang b 
             JOIN kategori k ON b.kategori_id = k.id 
             GROUP BY k.id, k.nama_kategori");
$label_grafik = [];
$data_grafik  = [];
while ($row = mysqli_fetch_assoc($q_grafik)) {
    $label_grafik[] = $row['nama_kategori'];
    $data_grafik[]  = (int)$row['total_stok'];
}

// Transaksi terbaru
$q_transaksi = mysqli_query($koneksi, "
    SELECT 'Masuk' as jenis, b.nama_barang, bm.jumlah, bm.tanggal, u.nama as user
    FROM barang_masuk bm
    JOIN barang b ON bm.barang_id = b.id
    JOIN users u ON bm.user_id = u.id
    UNION ALL
    SELECT 'Keluar' as jenis, b.nama_barang, bk.jumlah, bk.tanggal, u.nama as user
    FROM barang_keluar bk
    JOIN barang b ON bk.barang_id = b.id
    JOIN users u ON bk.user_id = u.id
    ORDER BY tanggal DESC
    LIMIT 8
");

// Stok barang menipis (stok <= 10)
$q_menipis = mysqli_query($koneksi, "SELECT nama_barang, stok, satuan FROM barang 
              WHERE stok <= 10 ORDER BY stok ASC LIMIT 5");

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-speedometer2 me-2 text-success"></i>Dashboard</h4>
        <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_nama']) ?></strong>! 
           Berikut ringkasan inventaris hari ini.</p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-calendar3 me-1"></i>
        <?= date('l, d F Y') ?>
    </div>
</div>

<!-- KARTU STATISTIK -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#1a5c2a,#2d8a45);
                     border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-archive text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Barang</div>
                    <div class="fw-bold fs-3"><?= $total_barang ?></div>
                    <div class="text-muted" style="font-size:0.75rem;">jenis barang terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#0d6efd,#4dabf7);
                     border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-tags text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Kategori</div>
                    <div class="fw-bold fs-3"><?= $total_kategori ?></div>
                    <div class="text-muted" style="font-size:0.75rem;">kategori barang aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#198754,#51cf66);
                     border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-box-arrow-in-down text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Barang Masuk</div>
                    <div class="fw-bold fs-3"><?= $total_masuk ?></div>
                    <div class="text-muted" style="font-size:0.75rem;">unit masuk bulan ini</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#dc3545,#ff6b6b);
                     border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-box-arrow-up text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Barang Keluar</div>
                    <div class="fw-bold fs-3"><?= $total_keluar ?></div>
                    <div class="text-muted" style="font-size:0.75rem;">unit keluar bulan ini</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK DAN STOK MENIPIS -->
<div class="row g-3 mb-4">
    <!-- Grafik stok per kategori -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bar-chart me-2 text-success"></i>Stok Barang per Kategori</span>
            </div>
            <div class="card-body p-4">
                <canvas id="grafikStok" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Stok menipis -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <span><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Stok Menipis</span>
            </div>
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($q_menipis) > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php while ($row = mysqli_fetch_assoc($q_menipis)): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4">
                        <span class="small"><?= htmlspecialchars($row['nama_barang']) ?></span>
                        <span class="badge bg-<?= $row['stok'] <= 5 ? 'danger' : 'warning text-dark' ?> rounded-pill">
                            <?= $row['stok'] ?> <?= $row['satuan'] ?>
                        </span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                    <p class="mt-2 small">Semua stok barang aman</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- TRANSAKSI TERBARU -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-2 text-success"></i>Transaksi Terbaru</span>
        <a href="<?= BASE_URL ?>pages/laporan/index.php" class="btn btn-sm btn-outline-success">
            Lihat Semua
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Jenis</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Dicatat oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($q_transaksi)): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-<?= $row['jenis'] == 'Masuk' ? 'success' : 'danger' ?>">
                                <i class="bi bi-<?= $row['jenis'] == 'Masuk' ? 'arrow-down' : 'arrow-up' ?> me-1"></i>
                                <?= $row['jenis'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['user']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT GRAFIK -->
<script>
const ctx = document.getElementById('grafikStok').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($label_grafik) ?>,
        datasets: [{
            label: 'Jumlah Stok',
            data: <?= json_encode($data_grafik) ?>,
            backgroundColor: [
                'rgba(26,92,42,0.8)',
                'rgba(45,138,69,0.8)',
                'rgba(13,110,253,0.8)',
                'rgba(220,53,69,0.8)',
                'rgba(255,193,7,0.8)'
            ],
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>