<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Hanya superadmin yang boleh akses
if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}

$pageTitle  = 'Kelola User Admin';
$activePage = 'users';
$breadcrumb = 'Users';

$pesan = '';
$tipeAlert = 'success';

// Pastikan tabel users ada
$db->query("CREATE TABLE IF NOT EXISTS users (
    id       INT PRIMARY KEY AUTO_INCREMENT,
    nama     VARCHAR(100) NOT NULL,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('superadmin','admin') NOT NULL DEFAULT 'admin',
    dibuat   DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // Tambah user
    if ($aksi === 'tambah') {
        $nama     = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role'] === 'superadmin' ? 'superadmin' : 'admin';

        if ($nama === '' || $username === '' || strlen($password) < 6) {
            $pesan = 'Nama, username wajib diisi dan password minimal 6 karakter.';
            $tipeAlert = 'danger';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $nama, $username, $hash, $role);
            if ($stmt->execute()) {
                $pesan = "User \"$username\" berhasil ditambahkan.";
            } else {
                $pesan = 'Username sudah digunakan.';
                $tipeAlert = 'danger';
            }
        }
    }

    // Ganti password
    if ($aksi === 'ganti_password') {
        $id          = (int)($_POST['id'] ?? 0);
        $newPassword = trim($_POST['new_password'] ?? '');

        if ($id <= 0 || strlen($newPassword) < 6) {
            $pesan = 'Password minimal 6 karakter.';
            $tipeAlert = 'danger';
        } else {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $id);
            $stmt->execute();
            $pesan = 'Password berhasil diperbarui.';
        }
    }

    // Hapus user
    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        // Tidak boleh hapus diri sendiri
        if ($id === (int)$_SESSION['admin_id']) {
            $pesan = 'Tidak bisa menghapus akun yang sedang digunakan.';
            $tipeAlert = 'danger';
        } else {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            $pesan = 'User berhasil dihapus.';
            $tipeAlert = 'warning';
        }
    }
}

$userList = $db->query("SELECT id, nama, username, role, dibuat FROM users ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipeAlert ?> alert-dismissible fade show d-flex align-items-center gap-2" style="border-radius:10px;">
    <i class="bi bi-<?= $tipeAlert === 'success' ? 'check-circle-fill' : ($tipeAlert === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill') ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-3">

    <!-- Form Tambah User -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah User Baru</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control form-control-sm" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Contoh: budi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Password <span class="text-danger">*</span> <small class="text-muted">(min. 6 karakter)</small></label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="••••••" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                        <div class="form-text" style="font-size:11px;">Super Admin bisa kelola user. Admin tidak bisa.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100">
                        <i class="bi bi-plus-lg me-1"></i>Tambah User
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar User -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-people me-2 text-primary"></i>Daftar User Admin
                    <span class="badge bg-primary ms-1"><?= count($userList) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Dibuat</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userList as $u): ?>
                            <tr>
                                <td class="ps-3 fw-medium" style="font-size:13px;">
                                    <?= htmlspecialchars($u['nama']) ?>
                                    <?php if ($u['id'] == $_SESSION['admin_id']): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">Anda</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:13px;"><?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'superadmin'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Super Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted" style="font-size:12px;"><?= htmlspecialchars($u['dibuat']) ?></td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-xs btn-outline-primary me-1"
                                            onclick="bukaGantiPassword(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')"
                                            title="Ganti Password">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if ($u['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ganti Password -->
<div class="modal fade" id="modalGantiPassword" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold"><i class="bi bi-key me-2 text-primary"></i>Ganti Password</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="ganti_password">
                    <input type="hidden" name="id" id="gantiPasswordId">
                    <p class="text-muted mb-3" style="font-size:13px;">User: <strong id="gantiPasswordUsername"></strong></p>
                    <label class="form-label fw-semibold" style="font-size:13px;">Password Baru <small class="text-muted">(min. 6 karakter)</small></label>
                    <input type="password" name="new_password" class="form-control form-control-sm"
                           placeholder="••••••" required minlength="6" autofocus>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>.btn-xs { padding: 3px 8px; font-size: 12px; }</style>

<script>
function bukaGantiPassword(id, username) {
    document.getElementById('gantiPasswordId').value = id;
    document.getElementById('gantiPasswordUsername').textContent = username;
    new bootstrap.Modal(document.getElementById('modalGantiPassword')).show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
