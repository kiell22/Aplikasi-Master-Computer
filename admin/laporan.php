<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Laporan Masalah';
$activePage = 'laporan';
$breadcrumb = 'Laporan';

$pesan = '';
$tipeAlert = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);
    $db->prepare("DELETE FROM laporan WHERE id=?")->execute([$id]);
    $pesan = 'Laporan berhasil dihapus.';
    $tipeAlert = 'warning';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus_semua') {
    $db->query("DELETE FROM laporan");
    $pesan = 'Semua laporan berhasil dihapus.';
    $tipeAlert = 'warning';
}

$laporan = $db->query("SELECT * FROM laporan ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipeAlert ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-flag me-2 text-danger"></i>
            Laporan dari Pengunjung
            <span class="badge bg-danger ms-1"><?= count($laporan) ?></span>
        </h6>
        <?php if (!empty($laporan)): ?>
        <form method="POST" onsubmit="return confirm('Hapus SEMUA laporan?')">
            <input type="hidden" name="aksi" value="hapus_semua">
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Hapus Semua
            </button>
        </form>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($laporan)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                <p class="mb-0">Belum ada laporan masalah.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:15%;">Pengirim</th>
                        <th>Pesan</th>
                        <th style="width:18%;">Waktu</th>
                        <th class="text-end pe-3" style="width:10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan as $l): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-medium" style="font-size:13px;">
                                <?= $l['nama'] ? htmlspecialchars($l['nama']) : '<span class="text-muted fst-italic">Anonim</span>' ?>
                            </div>
                        </td>
                        <td style="font-size:13px;"><?= nl2br(htmlspecialchars($l['pesan'])) ?></td>
                        <td class="text-muted" style="font-size:12px;"><?= htmlspecialchars($l['dibuat']) ?></td>
                        <td class="text-end pe-3">
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>.btn-xs { padding: 3px 8px; font-size: 12px; }</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
