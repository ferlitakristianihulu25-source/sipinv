<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';

$page_title  = 'Laporan Inventaris';
$active_menu = 'laporan';

// ===== FILTER TANGGAL =====
$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$tgl_awal  = mysqli_real_escape_string($koneksi, $tgl_awal);
$tgl_akhir = mysqli_real_escape_string($koneksi, $tgl_akhir);

// ===== DATA RINGKASAN STOK =====
$data_stok = mysqli_query($koneksi, "SELECT b.kode_barang, b.nama_barang, 
    k.nama_kategori, b.stok, b.satuan, b.lokasi,
    COALESCE((SELECT SUM(jumlah) FROM barang_masuk 
        WHERE barang_id = b.id 
        AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as total_masuk,
    COALESCE((SELECT SUM(jumlah) FROM barang_keluar 
        WHERE barang_id = b.id 
        AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'), 0) as total_keluar
    FROM barang b
    JOIN kategori k ON b.kategori_id = k.id
    ORDER BY k.nama_kategori, b.nama_barang");

// ===== TOTAL KESELURUHAN =====
$total_masuk_all  = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(jumlah),0) as total FROM barang_masuk 
     WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'"))['total'];

$total_keluar_all = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(jumlah),0) as total FROM barang_keluar 
     WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'"))['total'];

$total_barang     = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM barang"))['total'];

$total_stok_all   = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(stok),0) as total FROM barang"))['total'];

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-file-earmark-bar-graph me-2 text-success"></i>Laporan Inventaris</h4>
        <p>Rekap data inventaris barang PT Salim Ivomas Pratama</p>
    </div>
    <!-- Tombol Export PDF -->
    <a href="export_pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>"
       class="btn btn-danger" target="_blank">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
    </a>
</div>

<!-- FORM FILTER -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Tanggal Awal</label>
                <input type="date" name="tgl_awal" class="form-control"
                       value="<?= $tgl_awal ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control"
                       value="<?= $tgl_akhir ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- KARTU RINGKASAN -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3">
            <div class="text-muted small mb-1">Total Jenis Barang</div>
            <div class="fw-bold fs-2 text-success"><?= $total_barang ?></div>
            <div class="text-muted" style="font-size:0.75rem;">jenis barang terdaftar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <div class="text-muted small mb-1">Total Stok Keseluruhan</div>
            <div class="fw-bold fs-2 text-primary"><?= $total_stok_all ?></div>
            <div class="text-muted" style="font-size:0.75rem;">unit tersimpan saat ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <div class="text-muted small mb-1">Total Masuk (periode)</div>
            <div class="fw-bold fs-2 text-success"><?= $total_masuk_all ?></div>
            <div class="text-muted" style="font-size:0.75rem;">
                <?= date('d M', strtotime($tgl_awal)) ?> — 
                <?= date('d M Y', strtotime($tgl_akhir)) ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <div class="text-muted small mb-1">Total Keluar (periode)</div>
            <div class="fw-bold fs-2 text-danger"><?= $total_keluar_all ?></div>
            <div class="text-muted" style="font-size:0.75rem;">
                <?= date('d M', strtotime($tgl_awal)) ?> — 
                <?= date('d M Y', strtotime($tgl_akhir)) ?>
            </div>
        </div>
    </div>
</div>

<!-- TABEL LAPORAN -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-2"></i>Rekap Stok Barang</span>
        <small class="text-muted">
            Periode: <?= date('d M Y', strtotime($tgl_awal)) ?> — 
            <?= date('d M Y', strtotime($tgl_akhir)) ?>
        </small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th>Satuan</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($data_stok)):
                        $stok_class = $row['stok'] <= 5 ? 'danger' :
                                     ($row['stok'] <= 10 ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded small">
                                <?= htmlspecialchars($row['kode_barang']) ?>
                            </code>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($row['nama_kategori']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($row['total_masuk'] > 0): ?>
                            <span class="badge bg-success">+<?= $row['total_masuk'] ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($row['total_keluar'] > 0): ?>
                            <span class="badge bg-danger">-<?= $row['total_keluar'] ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $stok_class ?> rounded-pill fs-6 px-3">
                                <?= $row['stok'] ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['satuan']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <!-- TOTAL ROW -->
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="4" class="ps-4 text-end">Total Keseluruhan:</td>
                        <td class="text-center">
                            <span class="badge bg-success">+<?= $total_masuk_all ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger">-<?= $total_keluar_all ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill fs-6 px-3">
                                <?= $total_stok_all ?>
                            </span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>