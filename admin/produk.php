<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Kelola Produk';
$activePage = 'produk';
$breadcrumb = 'Produk';

$kategoriList = $db->query("SELECT * FROM kategori ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
$merekList    = $db->query("SELECT * FROM merek ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$pesan = '';
$tipeAlert = 'success';

// ── PROSES FORM ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        $nama       = trim($_POST['nama'] ?? '');
        $kategoriId = (int)($_POST['kategori_id'] ?? 0);
        $merekId    = ($_POST['merek_id'] ?? '') !== '' ? (int)$_POST['merek_id'] : null;
        $harga      = (int)($_POST['harga'] ?? 0);
        $stok       = (int)($_POST['stok'] ?? 0);
        $gambar     = trim($_POST['gambar'] ?? '');
        $deskripsi  = trim($_POST['deskripsi'] ?? '');

        if ($nama === '' || $kategoriId <= 0 || $harga < 0 || $stok < 0) {
            $pesan = 'Data tidak lengkap atau tidak valid.';
            $tipeAlert = 'danger';
        } else {
            if ($aksi === 'tambah') {
                $stmt = $db->prepare("INSERT INTO produk (nama,kategori_id,merek_id,harga,stok,gambar,deskripsi) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("siiiiss", $nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi);
                $stmt->execute();
                $pesan = 'Produk berhasil ditambahkan.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $db->prepare("UPDATE produk SET nama=?,kategori_id=?,merek_id=?,harga=?,stok=?,gambar=?,deskripsi=? WHERE id=?");
                $stmt->bind_param("siiiissi", $nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi, $id);
                $stmt->execute();
                $pesan = 'Produk berhasil diperbarui.';
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM produk WHERE id=?")->execute([$id]);
        $pesan = 'Produk berhasil dihapus.';
        $tipeAlert = 'warning';
    }
}

// ── MODE EDIT ────────────────────────────────────────────────
$idEdit   = null;
$dataEdit = null;
if (isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $res = $db->prepare("SELECT * FROM produk WHERE id=?");
    $res->bind_param("i", $idEdit);
    $res->execute();
    $dataEdit = $res->get_result()->fetch_assoc();
}

// ── FILTER & PENCARIAN ───────────────────────────────────────
$filterHabis    = isset($_GET['filter']) && $_GET['filter'] === 'habis';
$filterKategori = (int)($_GET['kategori'] ?? 0);
$cari           = trim($_GET['cari'] ?? '');

$where  = ["1=1"];
$params = [];
$types  = "";

if ($filterHabis) { $where[] = "p.stok = 0"; }
if ($filterKategori > 0) { $where[] = "p.kategori_id = ?"; $params[] = $filterKategori; $types .= "i"; }
if ($cari !== '') {
    $where[] = "(p.nama LIKE ? OR k.nama LIKE ? OR m.nama LIKE ?)";
    $like = "%$cari%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= "sss";
}

$sql = "SELECT p.*, k.nama AS kategori, m.nama AS merek
        FROM produk p
        JOIN kategori k ON k.id = p.kategori_id
        LEFT JOIN merek m ON m.id = p.merek_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY p.id DESC";

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$produkList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipeAlert ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert" style="border-radius:10px;">
    <i class="bi bi-<?= $tipeAlert === 'success' ? 'check-circle-fill' : ($tipeAlert === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill') ?>"></i>
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabel Produk -->
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-box-seam me-2 text-primary"></i>
            Daftar Produk
            <span class="badge bg-primary ms-1"><?= count($produkList) ?></span>
        </h6>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <input type="text" name="cari" class="form-control form-control-sm"
                       placeholder="Cari produk..." value="<?= htmlspecialchars($cari) ?>" style="width:180px;">
                <select name="kategori" class="form-select form-select-sm" style="width:140px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKategori == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
                <?php if ($cari || $filterKategori || $filterHabis): ?>
                    <a href="produk.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-primary btn-sm fw-semibold" onclick="bukaModalTambah()">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:38%;">Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produkList)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada produk ditemukan.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($produkList as $p): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-medium" style="font-size:13px;"><?= htmlspecialchars($p['nama']) ?></div>
                            <?php if ($p['merek']): ?>
                                <small class="text-muted"><?= htmlspecialchars($p['merek']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-light text-dark border" style="font-size:11px;"><?= htmlspecialchars($p['kategori']) ?></span></td>
                        <td style="font-size:13px;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($p['stok'] > 0): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $p['stok'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <button class="btn btn-xs btn-outline-primary me-1" title="Edit"
                                onclick="bukaModalEdit(<?= htmlspecialchars(json_encode($p)) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== MODAL TAMBAH / EDIT ===== -->
<div class="modal fade" id="modalProduk" tabindex="-1" aria-labelledby="modalProdukLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px; border:none;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalProdukLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Produk Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form method="POST" id="formProduk">
                    <input type="hidden" name="aksi" id="formAksi" value="tambah">
                    <input type="hidden" name="id" id="formId" value="">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="formNama" class="form-control"
                                   placeholder="Contoh: ASUS Vivobook 14" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="formKategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Merek</label>
                            <select name="merek_id" id="formMerek" class="form-select">
                                <option value="">Tanpa Merek</option>
                                <?php foreach ($merekList as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" id="formHarga" class="form-control"
                                   min="0" placeholder="8500000" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:13px;">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" id="formStok" class="form-control"
                                   min="0" placeholder="5" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;">Link Foto Produk</label>
                            <input type="text" name="gambar" id="formGambar" class="form-control"
                                   placeholder="https://contoh.com/foto.jpg">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:13px;">Deskripsi</label>
                            <textarea name="deskripsi" id="formDeskripsi" class="form-control" rows="3"
                                      placeholder="Deskripsi singkat produk..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-semibold" id="btnSubmit">
                            <i class="bi bi-plus-lg me-1"></i> Tambahkan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.btn-xs { padding: 4px 9px; font-size: 12px; }
.form-control, .form-select { border-radius: 8px; font-size: 14px; }
.modal-content { box-shadow: 0 20px 60px rgba(0,0,0,.15); }
</style>

<script>
let modalProduk;

function bukaModalTambah() {
    document.getElementById('modalProdukLabel').innerHTML = '<i class="bi bi-plus-circle text-primary me-2"></i>Tambah Produk Baru';
    document.getElementById('formAksi').value = 'tambah';
    document.getElementById('formId').value = '';
    document.getElementById('formProduk').reset();
    document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-plus-lg me-1"></i> Tambahkan Produk';
    modalProduk.show();
}

function bukaModalEdit(p) {
    document.getElementById('modalProdukLabel').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i>Edit Produk';
    document.getElementById('formAksi').value = 'ubah';
    document.getElementById('formId').value = p.id;
    document.getElementById('formNama').value = p.nama;
    document.getElementById('formKategori').value = p.kategori_id;
    document.getElementById('formMerek').value = p.merek_id ?? '';
    document.getElementById('formHarga').value = p.harga;
    document.getElementById('formStok').value = p.stok;
    document.getElementById('formGambar').value = p.gambar ?? '';
    document.getElementById('formDeskripsi').value = p.deskripsi ?? '';
    document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan Perubahan';
    modalProduk.show();
}

window.addEventListener('DOMContentLoaded', () => {
    modalProduk = new bootstrap.Modal(document.getElementById('modalProduk'));
    <?php if ($idEdit && $dataEdit): ?>
    bukaModalEdit(<?= json_encode($dataEdit) ?>);
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
