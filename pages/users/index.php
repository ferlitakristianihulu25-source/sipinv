<?php
session_start();
require_once __DIR__ . '/../../config/koneksi.php';

$page_title  = 'Manajemen User';
$active_menu = 'users';

// Hanya admin yang boleh akses
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit();
}

// ===== PROSES TAMBAH =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'tambah') {
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role     = $_POST['role'] == 'admin' ? 'admin' : 'staff';

    // Cek email duplikat
    $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        header('Location: index.php?error=duplikat');
        exit();
    }

    mysqli_query($koneksi, "INSERT INTO users (nama, email, password, role)
        VALUES ('$nama','$email','$password','$role')");
    header('Location: index.php?success=tambah');
    exit();
}

// ===== PROSES EDIT =====
if (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
    $id    = (int)$_POST['id'];
    $nama  = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $role  = $_POST['role'] == 'admin' ? 'admin' : 'staff';

    // Kalau password diisi, update juga passwordnya
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        mysqli_query($koneksi, "UPDATE users SET nama='$nama', email='$email',
            password='$password', role='$role' WHERE id=$id");
    } else {
        mysqli_query($koneksi, "UPDATE users SET nama='$nama', email='$email',
            role='$role' WHERE id=$id");
    }

    header('Location: index.php?success=edit');
    exit();
}

// ===== PROSES HAPUS =====
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // Tidak boleh hapus akun sendiri
    if ($id == $_SESSION['user_id']) {
        header('Location: index.php?error=sendiri');
        exit();
    }

    mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
    header('Location: index.php?success=hapus');
    exit();
}

// ===== AMBIL DATA =====
$data = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role ASC, nama ASC");

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-people me-2 text-success"></i>Manajemen User</h4>
        <p>Kelola akun pengguna yang dapat mengakses sistem SIPINV</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-person-plus me-1"></i> Tambah User
    </button>
</div>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <span>
        <?php
        if ($_GET['success'] == 'tambah') echo 'User baru berhasil ditambahkan.';
        if ($_GET['success'] == 'edit')   echo 'Data user berhasil diperbarui.';
        if ($_GET['success'] == 'hapus')  echo 'User berhasil dihapus.';
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
        if ($_GET['error'] == 'duplikat') echo 'Email sudah digunakan oleh user lain.';
        if ($_GET['error'] == 'sendiri')  echo 'Tidak bisa menghapus akun yang sedang digunakan.';
        ?>
    </span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- TABEL USER -->
<div class="card">
    <div class="card-header">
        <span><i class="bi bi-list-ul me-2"></i>Daftar Pengguna Sistem</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($data)):
                        $is_me = $row['id'] == $_SESSION['user_id'];
                    ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;
                                     background:linear-gradient(135deg,#1a5c2a,#2d8a45);
                                     display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-person text-white small"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($row['nama']) ?>
                                        <?php if ($is_me): ?>
                                        <span class="badge bg-success ms-1" style="font-size:0.65rem;">
                                            Anda
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['role'] == 'admin' ? 'danger' : 'primary' ?>">
                                <i class="bi bi-<?= $row['role'] == 'admin' ? 'shield' : 'person' ?> me-1"></i>
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="<?= $row['id'] ?>"
                                data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                data-role="<?= $row['role'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if (!$is_me): ?>
                            <a href="index.php?hapus=<?= $row['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus user ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-danger" disabled>
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
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
                    <i class="bi bi-person-plus me-2 text-success"></i>Tambah User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control"
                               placeholder="Nama lengkap pengguna" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               placeholder="email@sipinv.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Minimal 6 karakter" minlength="6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                        <small class="text-muted">Admin dapat mengakses semua fitur termasuk manajemen user.</small>
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
                    <i class="bi bi-pencil me-2 text-primary"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="editNama"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small class="text-muted">Isi hanya jika ingin mengganti password.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
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

<script>
document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value    = btn.getAttribute('data-id');
    document.getElementById('editNama').value  = btn.getAttribute('data-nama');
    document.getElementById('editEmail').value = btn.getAttribute('data-email');

    const role   = btn.getAttribute('data-role');
    const select = document.getElementById('editRole');
    for (let opt of select.options) {
        opt.selected = opt.value == role;
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>