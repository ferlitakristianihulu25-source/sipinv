<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

$page_title  = 'Barang Masuk';
$active_menu = 'masuk';
$is_admin    = ($_SESSION['user_role'] ?? '') === 'admin';

// ===== PROTEKSI: HANYA ADMIN YANG BOLEH HAPUS =====
if (isset($_GET['hapus']) && !$is_admin) {
    header('Location: masuk.php?error=akses');
    exit();
}

// ===== PROSES TAMBAH (Admin dan Staff boleh) =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $barang_id   = (int)$_POST['barang_id'];
    $jumlah      = (int)$_POST['jumlah'];
    $tanggal     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $keterangan  = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $user_id     = $_SESSION['user_id'];

    // Simpan transaksi
    mysqli_query($koneksi, "INSERT INTO barang_masuk 
        (barang_id, user_id, jumlah, tanggal, keterangan)
        VALUES ('$barang_id','$user_id','$jumlah','$tanggal','$keterangan')");

    // Update stok barang
    mysqli_query($koneksi, "UPDATE barang SET stok = stok + $jumlah 
        WHERE id = $barang_id");

    header('Location: masuk.php?success=tambah');
    exit();
}

// ===== PROSES HAPUS (Hanya Admin) =====
if (isset($_GET['hapus'])) {
    $id   = (int)$_GET['hapus'];

    // Ambil data transaksi dulu sebelum hapus
    $data_hapus = mysqli_fetch_assoc(mysqli_query($koneksi, 
        "SELECT * FROM barang_masuk WHERE id=$id"));

    if ($data_hapus) {
        // Kurangi stok kembali
        mysqli_query($koneksi, "UPDATE barang SET stok = stok - {$data_hapus['jumlah']} 
            WHERE id = {$data_hapus['barang_id']}");
        mysqli_query($koneksi, "DELETE FROM barang_masuk WHERE id=$id");
    }

    header('Location: masuk.php?success=hapus');
    exit();
}

// ===== AMBIL DATA =====
$search = mysqli_real_escape_string($koneksi, trim($_GET['search'] ?? ''));
$where  = $search ? "WHERE b.nama_barang LIKE '%$search%'" : '';

$data = mysqli_query($koneksi, "SELECT bm.*, b.nama_barang, b.satuan, 
        b.kode_barang, u.nama as user_nama
        FROM barang_masuk bm
        JOIN barang b ON bm.barang_id = b.id
        JOIN users u ON bm.user_id = u.id
        $where
        ORDER BY bm.tanggal DESC, bm.created_at DESC");

// Ambil daftar barang untuk dropdown
$barang_list = mysqli_query($koneksi, "SELECT id, kode_barang, nama_barang, 
               stok, satuan FROM barang ORDER BY nama_barang");

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Barang Masuk</h4>
        <p>Catat setiap penambahan stok barang yang diterima perusahaan</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Catat Barang Masuk
    </button>
</div>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <span>
        <?php
        if ($_GET['success'] == 'tambah') echo 'Transaksi barang masuk berhasil dicatat, stok otomatis bertambah.';
        if ($_GET['success'] == 'hapus')  echo 'Transaksi berhasil dihapus, stok otomatis dikurangi kembali.';
        ?>
    </span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 'akses'): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Anda tidak memiliki akses untuk menghapus transaksi. Hanya Admin yang dapat melakukan koreksi data.</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- TABEL BARANG MASUK -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-list-ul me-2"></i>Riwayat Barang Masuk</span>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari nama barang..."
                   value="<?= htmlspecialchars($search) ?>" style="width:230px;">
            <button class="btn btn-sm btn-outline-success" type="submit">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="masuk.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($data)):
                    ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded small">
                                <?= htmlspecialchars($row['kode_barang']) ?>
                            </code>
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td>
                            <span class="badge bg-success rounded-pill fs-6 px-3">
                                +<?= $row['jumlah'] ?> <?= $row['satuan'] ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($row['user_nama']) ?></td>
                        <td class="text-center">
                            <?php if ($is_admin): ?>
                            <a href="masuk.php?hapus=<?= $row['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Hapus transaksi ini? Stok barang akan dikurangi kembali.')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">
                                <i class="bi bi-lock"></i> Terkunci
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($data) === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2">Belum ada transaksi barang masuk</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-down me-2 text-success"></i>Catat Barang Masuk
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <select name="barang_id" id="selectBarang" class="form-select" required
                                onchange="updateInfoBarang(this)">
                            <option value="">Pilih barang...</option>
                            <?php while ($b = mysqli_fetch_assoc($barang_list)): ?>
                            <option value="<?= $b['id'] ?>"
                                data-stok="<?= $b['stok'] ?>"
                                data-satuan="<?= htmlspecialchars($b['satuan']) ?>">
                                <?= htmlspecialchars($b['kode_barang']) ?> —
                                <?= htmlspecialchars($b['nama_barang']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <!-- Info stok saat ini -->
                        <div id="infoBarang" class="mt-2 d-none">
                            <small class="text-muted">
                                Stok saat ini: <strong id="stokSaatIni">0</strong>
                                <span id="satuanBarang"></span>
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Masuk <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control"
                               placeholder="Masukkan jumlah" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"
                                  placeholder="Contoh: Pengadaan barang bulan Juli"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateInfoBarang(select) {
    const opt    = select.options[select.selectedIndex];
    const info   = document.getElementById('infoBarang');
    const stok   = document.getElementById('stokSaatIni');
    const satuan = document.getElementById('satuanBarang');

    if (select.value) {
        stok.textContent   = opt.getAttribute('data-stok');
        satuan.textContent = opt.getAttribute('data-satuan');
        info.classList.remove('d-none');
    } else {
        info.classList.add('d-none');
    }
}
</script>

require_once __DIR__ . '/../../includes/footer.php'; ?>