<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Kelola Landing Page';
$activePage = 'landing';
$breadcrumb = 'Landing Page';

$pesan = '';
$tipeAlert = 'success';

// Pastikan tabel pengaturan ada
$db->query("CREATE TABLE IF NOT EXISTS pengaturan (
    kunci VARCHAR(80) PRIMARY KEY,
    nilai TEXT NOT NULL DEFAULT ''
)");

// Ambil semua pengaturan
$set = [];
$res = $db->query("SELECT kunci, nilai FROM pengaturan");
if ($res) while ($r = $res->fetch_assoc()) $set[$r['kunci']] = $r['nilai'];

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan_umum') {
        $fields = ['nama_toko','tagline','deskripsi_hero','kota','whatsapp','tentang_judul','tentang_isi','kontak_judul','kontak_isi'];
        $stmt = $db->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?,?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)");
        foreach ($fields as $f) {
            $val = trim($_POST[$f] ?? '');
            $stmt->bind_param("ss", $f, $val);
            $stmt->execute();
            $set[$f] = $val;
        }
        $pesan = 'Pengaturan berhasil disimpan.';
    }

    if ($aksi === 'simpan_lokasi') {
        $lokasiArr = [];
        $namaArr   = $_POST['lok_nama']   ?? [];
        $alamatArr = $_POST['lok_alamat'] ?? [];
        $pusatArr  = $_POST['lok_pusat']  ?? [];

        foreach ($namaArr as $i => $nm) {
            $nm = trim($nm);
            $al = trim($alamatArr[$i] ?? '');
            if ($nm === '' || $al === '') continue;
            $lokasiArr[] = [
                'nama'   => $nm,
                'pusat'  => isset($pusatArr[$i]),
                'alamat' => $al,
            ];
        }

        $json = json_encode($lokasiArr, JSON_UNESCAPED_UNICODE);
        $stmt = $db->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES ('lokasi_json',?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)");
        $stmt->bind_param("s", $json);
        $stmt->execute();
        $set['lokasi_json'] = $json;
        $pesan = 'Data lokasi berhasil disimpan.';
    }

    if ($aksi === 'tambah_lokasi') {
        // Redirect agar tidak resubmit
        header('Location: pengaturan_landing.php?tambah_lokasi=1');
        exit;
    }
}

$lokasiArr = json_decode($set['lokasi_json'] ?? '[]', true) ?: [];
$tambahLokasi = isset($_GET['tambah_lokasi']);

include __DIR__ . '/includes/header.php';
?>

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipeAlert ?> alert-dismissible fade show d-flex align-items-center gap-2" style="border-radius:10px;">
    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-4" id="landingTab">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-umum">
            <i class="bi bi-gear me-1"></i>Konten Umum
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-lokasi">
            <i class="bi bi-geo-alt me-1"></i>Lokasi Toko
        </a>
    </li>
    <li class="nav-item ms-auto">
        <a href="<?= BASE_URL ?>" target="_blank" class="nav-link text-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Landing Page
        </a>
    </li>
</ul>

<div class="tab-content">

    <!-- TAB KONTEN UMUM -->
    <div class="tab-pane fade show active" id="tab-umum">
        <form method="POST">
            <input type="hidden" name="aksi" value="simpan_umum">
            <div class="row g-3">

                <!-- Informasi Toko -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-shop me-2 text-primary"></i>Informasi Toko</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Nama Toko</label>
                                <input type="text" name="nama_toko" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['nama_toko'] ?? 'Master Computer Store') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Kota / Lokasi</label>
                                <input type="text" name="kota" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['kota'] ?? 'Jayapura, Papua') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Nomor WhatsApp <small class="text-muted">(format: 628xxx)</small></label>
                                <input type="text" name="whatsapp" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['whatsapp'] ?? '6282198925838') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-stars me-2 text-primary"></i>Hero / Banner Utama</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Tagline Utama</label>
                                <input type="text" name="tagline" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['tagline'] ?? 'Temukan Kebutuhan Komputer Anda') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Deskripsi Hero</label>
                                <textarea name="deskripsi_hero" class="form-control form-control-sm" rows="4"><?= htmlspecialchars($set['deskripsi_hero'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tentang Kami -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Tentang Kami</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Judul</label>
                                <input type="text" name="tentang_judul" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['tentang_judul'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Isi <small class="text-muted">(pisah paragraf dengan baris kosong)</small></label>
                                <textarea name="tentang_isi" class="form-control form-control-sm" rows="6"><?= htmlspecialchars($set['tentang_isi'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-telephone me-2 text-primary"></i>Bagian Kontak</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Judul</label>
                                <input type="text" name="kontak_judul" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($set['kontak_judul'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:13px;">Deskripsi</label>
                                <textarea name="kontak_isi" class="form-control form-control-sm" rows="4"><?= htmlspecialchars($set['kontak_isi'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-save me-1"></i>Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB LOKASI -->
    <div class="tab-pane fade <?= $tambahLokasi ? 'show active' : '' ?>" id="tab-lokasi">
        <form method="POST">
            <input type="hidden" name="aksi" value="simpan_lokasi">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2 text-primary"></i>Daftar Lokasi Toko</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahLokasi">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Lokasi
                    </button>
                </div>
                <div class="card-body">
                    <div id="lokasiContainer">
                        <?php foreach ($lokasiArr as $i => $lok): ?>
                        <div class="lokasi-row border rounded p-3 mb-3 position-relative">
                            <button type="button" class="btn btn-xs btn-outline-danger position-absolute top-0 end-0 m-2 hapus-lokasi" title="Hapus">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Nama Lokasi</label>
                                    <input type="text" name="lok_nama[]" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($lok['nama']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size:12px;">Alamat Lengkap</label>
                                    <input type="text" name="lok_alamat[]" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($lok['alamat']) ?>" required>
                                </div>
                                <div class="col-md-1 d-flex align-items-end pb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="lok_pusat[<?= $i ?>]"
                                               <?= !empty($lok['pusat']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" style="font-size:12px;">Pusat</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-save me-1"></i>Simpan Lokasi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- Template baris lokasi baru -->
<template id="templateLokasi">
    <div class="lokasi-row border rounded p-3 mb-3 position-relative border-primary">
        <button type="button" class="btn btn-xs btn-outline-danger position-absolute top-0 end-0 m-2 hapus-lokasi" title="Hapus">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="row g-2">
            <div class="col-md-5">
                <label class="form-label fw-semibold" style="font-size:12px;">Nama Lokasi</label>
                <input type="text" name="lok_nama[]" class="form-control form-control-sm" placeholder="Contoh: Cabang Sorong" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" style="font-size:12px;">Alamat Lengkap</label>
                <input type="text" name="lok_alamat[]" class="form-control form-control-sm" placeholder="Jl. ..." required>
            </div>
            <div class="col-md-1 d-flex align-items-end pb-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="lok_pusat[__i__]">
                    <label class="form-check-label" style="font-size:12px;">Pusat</label>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.btn-xs { padding: 3px 8px; font-size: 12px; }
.nav-tabs .nav-link { font-size: 14px; }
</style>

<script>
let lokasiCount = <?= count($lokasiArr) ?>;

document.getElementById('btnTambahLokasi').addEventListener('click', function () {
    const tpl = document.getElementById('templateLokasi').innerHTML.replace(/__i__/g, lokasiCount++);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('lokasiContainer').appendChild(div.firstElementChild);
    pasangHapus();
});

function pasangHapus() {
    document.querySelectorAll('.hapus-lokasi').forEach(btn => {
        btn.onclick = function () {
            this.closest('.lokasi-row').remove();
        };
    });
}
pasangHapus();

<?php if ($tambahLokasi): ?>
// Auto-aktifkan tab lokasi
document.querySelector('[href="#tab-lokasi"]').click();
<?php endif; ?>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
