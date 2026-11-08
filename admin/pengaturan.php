<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Pengaturan Toko';
$activePage = 'pengaturan';
$breadcrumb = 'Pengaturan';

// Ambil data merek & kategori
$merekList    = $db->query("SELECT * FROM merek ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$kategoriList = $db->query("SELECT * FROM kategori ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$pesan = '';
$tipeAlert = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah_merek') {
        $nama = trim($_POST['nama_merek'] ?? '');
        if ($nama !== '') {
            $stmt = $db->prepare("INSERT IGNORE INTO merek (nama) VALUES (?)");
            $stmt->bind_param("s", $nama);
            $stmt->execute();
            $pesan = "Merek \"$nama\" berhasil ditambahkan.";
        }
    }

    if ($aksi === 'hapus_merek') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM merek WHERE id=?")->execute([$id]);
        $pesan = 'Merek berhasil dihapus.';
        $tipeAlert = 'warning';
    }

    // Reload data setelah aksi
    $merekList = $db->query("SELECT * FROM merek ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipeAlert ?> alert-dismissible fade show d-flex align-items-center gap-2" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-3">
    <!-- Kelola Merek -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-bookmark me-2 text-primary"></i>Kelola Merek</h6>
            </div>
            <div class="card-body">
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="hidden" name="aksi" value="tambah_merek">
                    <input type="text" name="nama_merek" class="form-control form-control-sm" placeholder="Nama merek baru..." required>
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </form>
                <div style="max-height:320px;overflow-y:auto;">
                    <?php foreach ($merekList as $m): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span style="font-size:14px;"><?= htmlspecialchars($m['nama']) ?></span>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus merek ini?')">
                            <input type="hidden" name="aksi" value="hapus_merek">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Kategori -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-tags me-2 text-primary"></i>Kategori Produk</h6>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:13px;">Kategori produk sudah ditetapkan sesuai menu website.</p>
                <?php foreach ($kategoriList as $k): ?>
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($k['nama']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Info Koneksi -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Sistem</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted mb-1" style="font-size:12px;">DATABASE</div>
                            <div class="fw-semibold" style="font-size:14px;"><?= htmlspecialchars($db_nama) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted mb-1" style="font-size:12px;">SERVER</div>
                            <div class="fw-semibold" style="font-size:14px;"><?= htmlspecialchars($db_host) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted mb-1" style="font-size:12px;">PHP VERSION</div>
                            <div class="fw-semibold" style="font-size:14px;"><?= PHP_VERSION ?></div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0 d-flex align-items-center gap-2" style="font-size:13px;border-radius:8px;">
                    <i class="bi bi-shield-lock-fill"></i>
                    Untuk mengubah username/password admin, edit file <code>koneksi.php</code> di root folder.
                </div>
            </div>
        </div>
    </div>
</div>

<style>.btn-xs { padding: 3px 8px; font-size: 12px; }</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
