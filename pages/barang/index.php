<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/koneksi.php';

$page_title  = 'Data Barang';
$active_menu = 'barang';
$is_admin    = ($_SESSION['user_role'] ?? '') === 'admin';

// ===== PROTEKSI: HANYA ADMIN YANG BOLEH TAMBAH/EDIT/HAPUS =====
if (($_POST['aksi'] ?? $_GET['hapus'] ?? null) !== null 
    && ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.php?error=akses');
    exit();
}

// ===== PROSES TAMBAH =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $kategori_id = (int)$_POST['kategori_id'];
    $kode        = mysqli_real_escape_string($koneksi, trim($_POST['kode_barang']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama_barang']));
    $stok        = (int)$_POST['stok'];
    $satuan      = mysqli_real_escape_string($koneksi, trim($_POST['satuan']));
    $lokasi      = mysqli_real_escape_string($koneksi, trim($_POST['lokasi']));
    $deskripsi   = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    // Cek kode barang duplikat
    $cek = mysqli_query($koneksi, "SELECT id FROM barang WHERE kode_barang='$kode'");
    if (mysqli_num_rows($cek) > 0) {
        header('Location: index.php?error=duplikat');
        exit();
    }

    mysqli_query($koneksi, "INSERT INTO barang 
        (kategori_id, kode_barang, nama_barang, stok, satuan, lokasi, deskripsi)
        VALUES ('$kategori_id','$kode','$nama','$stok','$satuan','$lokasi','$deskripsi')");
    header('Location: index.php?success=tambah');
    exit();
}

// ===== PROSES EDIT =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id          = (int)$_POST['id'];
    $kategori_id = (int)$_POST['kategori_id'];
    $kode        = mysqli_real_escape_string($koneksi, trim($_POST['kode_barang']));
    $nama        = mysqli_real_escape_string($koneksi, trim($_POST['nama_barang']));
    $satuan      = mysqli_real_escape_string($koneksi, trim($_POST['satuan']));
    $lokasi      = mysqli_real_escape_string($koneksi, trim($_POST['lokasi']));
    $deskripsi   = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    mysqli_query($koneksi, "UPDATE barang SET 
        kategori_id='$kategori_id', kode_barang='$kode', nama_barang='$nama',
        satuan='$satuan', lokasi='$lokasi', deskripsi='$deskripsi'
        WHERE id=$id");
    header('Location: index.php?success=edit');
    exit();
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang_masuk 
           WHERE barang_id=$id");
    $cek = mysqli_fetch_assoc($cek);
    if ($cek['total'] > 0) {
        header('Location: index.php?error=dipakai');
        exit();
    }
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header('Location: index.php?success=hapus');
    exit();
}

// ===== AMBIL DATA =====
$search = mysqli_real_escape_string($koneksi, trim($_GET['search'] ?? ''));
$where  = $search ? "WHERE b.nama_barang LIKE '%$search%' 
          OR b.kode_barang LIKE '%$search%'" : '';

$data = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori 
        FROM barang b JOIN kategori k ON b.kategori_id = k.id 
        $where ORDER BY b.created_at DESC");

// Ambil semua kategori untuk dropdown
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-archive me-2 text-success"></i>Data Barang</h4>
        <p>Kelola data master barang inventaris perusahaan</p>
    </div>
    <?php if ($is_admin): ?>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i> Tambah Barang
    </button>
    <?php endif; ?>
</div>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <span>
        <?php
        if ($_GET['success'] == 'tambah') echo 'Barang berhasil ditambahkan.';
        if ($_GET['success'] == 'edit')   echo 'Barang berhasil diperbarui.';
        if ($_GET['success'] == 'hapus')  echo 'Barang berhasil dihapus.';
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
        if ($_GET['error'] == 'duplikat') echo 'Kode barang sudah digunakan, gunakan kode lain.';
        if ($_GET['error'] == 'dipakai')  echo 'Barang tidak bisa dihapus karena memiliki riwayat transaksi.';
        if ($_GET['error'] == 'akses')    echo 'Anda tidak memiliki akses untuk melakukan aksi ini. Hanya Admin yang dapat mengelola data barang.';
        ?>
    </span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- TABEL BARANG -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-list-ul me-2"></i>Daftar Barang</span>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari nama / kode barang..."
                   value="<?= htmlspecialchars($search) ?>" style="width:250px;">
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
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Lokasi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($data)):
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
                        <td>
                            <span class="badge bg-<?= $stok_class ?> rounded-pill fs-6 px-3">
                                <?= $row['stok'] ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['satuan']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php if ($is_admin): ?>
                            <button class="btn btn-sm btn-outline-primary me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="<?= $row['id'] ?>"
                                data-kategori="<?= $row['kategori_id'] ?>"
                                data-kode="<?= htmlspecialchars($row['kode_barang']) ?>"
                                data-nama="<?= htmlspecialchars($row['nama_barang']) ?>"
                                data-satuan="<?= htmlspecialchars($row['satuan']) ?>"
                                data-lokasi="<?= htmlspecialchars($row['lokasi'] ?? '') ?>"
                                data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? '') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?hapus=<?= $row['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus barang ini?')">
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
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2">Tidak ada data barang ditemukan</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2 text-success"></i>Tambah Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" class="form-control"
                                   placeholder="Contoh: ATK-005" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">Pilih kategori...</option>
                                <?php
                                mysqli_data_seek($kategori_list, 0);
                                while ($kat = mysqli_fetch_assoc($kategori_list)):
                                ?>
                                <option value="<?= $kat['id'] ?>">
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" class="form-control"
                                   placeholder="Nama lengkap barang" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control"
                                   placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" class="form-control"
                                   placeholder="Pcs / Unit / Rim / Botol" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lokasi Penyimpanan</label>
                            <input type="text" name="lokasi" class="form-control"
                                   placeholder="Contoh: Gudang Lantai 1">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"
                                      placeholder="Keterangan tambahan (opsional)"></textarea>
                        </div>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2 text-primary"></i>Edit Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kode_barang" id="editKode"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="editKategori" class="form-select" required>
                                <?php
                                mysqli_data_seek($kategori_list, 0);
                                while ($kat = mysqli_fetch_assoc($kategori_list)):
                                ?>
                                <option value="<?= $kat['id'] ?>">
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_barang" id="editNama"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="satuan" id="editSatuan"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lokasi Penyimpanan</label>
                            <input type="text" name="lokasi" id="editLokasi"
                                   class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <input type="text" name="deskripsi" id="editDeskripsi"
                                   class="form-control">
                        </div>
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
document.getElementById('modalEdit')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value       = btn.getAttribute('data-id');
    document.getElementById('editKode').value     = btn.getAttribute('data-kode');
    document.getElementById('editNama').value     = btn.getAttribute('data-nama');
    document.getElementById('editSatuan').value   = btn.getAttribute('data-satuan');
    document.getElementById('editLokasi').value   = btn.getAttribute('data-lokasi');
    document.getElementById('editDeskripsi').value= btn.getAttribute('data-deskripsi');

    // Set dropdown kategori
    const select = document.getElementById('editKategori');
    const katId  = btn.getAttribute('data-kategori');
    for (let opt of select.options) {
        opt.selected = opt.value == katId;
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>