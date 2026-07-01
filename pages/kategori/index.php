<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

$page_title  = 'Kategori Barang';
$active_menu = 'kategori';
$is_admin    = ($_SESSION['user_role'] ?? '') === 'admin';

// ===== PROTEKSI: HANYA ADMIN YANG BOLEH TAMBAH/EDIT/HAPUS =====
if (($_POST['aksi'] ?? $_GET['hapus'] ?? null) !== null 
    && ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.php?error=akses');
    exit();
}

// ===== PROSES TAMBAH =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    if (!empty($nama)) {
        mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori, deskripsi) 
                                VALUES ('$nama', '$deskripsi')");
        header('Location: index.php?success=tambah');
        exit();
    }
}

// ===== PROSES EDIT =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id        = (int)$_POST['id'];
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $deskripsi = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama', 
                            deskripsi='$deskripsi' WHERE id=$id");
    header('Location: index.php?success=edit');
    exit();
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Cek apakah kategori masih dipakai barang
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE kategori_id=$id");
    $cek = mysqli_fetch_assoc($cek);
    if ($cek['total'] > 0) {
        header('Location: index.php?error=dipakai');
        exit();
    }
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id=$id");
    header('Location: index.php?success=hapus');
    exit();
}

// ===== AMBIL DATA KATEGORI =====
$search = mysqli_real_escape_string($koneksi, trim($_GET['search'] ?? ''));
$where  = $search ? "WHERE nama_kategori LIKE '%$search%'" : '';
$data   = mysqli_query($koneksi, "SELECT k.*, COUNT(b.id) as jumlah_barang 
           FROM kategori k LEFT JOIN barang b ON k.id = b.kategori_id 
           $where GROUP BY k.id ORDER BY k.created_at DESC");

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-tags me-2 text-success"></i>Kategori Barang</h4>
        <p>Kelola kategori pengelompokan barang inventaris</p>
    </div>
    <?php if ($is_admin): ?>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </button>
    <?php endif; ?>
</div>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <span>
        <?php
        if ($_GET['success'] == 'tambah') echo 'Kategori berhasil ditambahkan.';
        if ($_GET['success'] == 'edit')   echo 'Kategori berhasil diperbarui.';
        if ($_GET['success'] == 'hapus')  echo 'Kategori berhasil dihapus.';
        ?>
    </span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>
        <?php
        if ($_GET['error'] == 'dipakai') echo 'Kategori tidak bisa dihapus karena masih digunakan oleh data barang.';
        if ($_GET['error'] == 'akses')   echo 'Anda tidak memiliki akses untuk melakukan aksi ini. Hanya Admin yang dapat mengelola kategori.';
        ?>
    </span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- TABEL KATEGORI -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-list-ul me-2"></i>Daftar Kategori</span>
        <!-- Form pencarian -->
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari kategori..." value="<?= htmlspecialchars($search) ?>"
                   style="width:220px;">
            <button class="btn btn-sm btn-outline-success" type="submit">
                <i class="bi bi-search"></i>
            </button>
            <?php if ($search): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
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
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Barang</th>
                        <th>Dibuat</th>
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
                        <td>
                            <span class="fw-semibold"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-success rounded-pill">
                                <?= $row['jumlah_barang'] ?> barang
                            </span>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        <td class="text-center">
                            <?php if ($is_admin): ?>
                            <!-- Tombol Edit -->
                            <button class="btn btn-sm btn-outline-primary me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEdit"
                                data-id="<?= $row['id'] ?>"
                                data-nama="<?= htmlspecialchars($row['nama_kategori']) ?>"
                                data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? '') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Tombol Hapus -->
                            <a href="index.php?hapus=<?= $row['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">
                                <i class="bi bi-eye"></i> Lihat saja
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($data) === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2">Tidak ada data kategori ditemukan</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<?php if ($is_admin): ?>
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2 text-success"></i>Tambah Kategori
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control"
                               placeholder="Contoh: Alat Tulis Kantor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"
                                  placeholder="Deskripsi singkat kategori (opsional)"></textarea>
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

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2 text-primary"></i>Edit Kategori
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="editNama"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsi"
                                  class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Isi data ke modal edit otomatis
document.getElementById('modalEdit')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value        = btn.getAttribute('data-id');
    document.getElementById('editNama').value      = btn.getAttribute('data-nama');
    document.getElementById('editDeskripsi').value = btn.getAttribute('data-deskripsi');
});
</script>

require_once __DIR__ . '/../../includes/footer.php'; ?>