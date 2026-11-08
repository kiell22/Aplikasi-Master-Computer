<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

// Statistik
$totalProduk   = $db->query("SELECT COUNT(*) FROM produk")->fetch_row()[0];
$totalKategori = $db->query("SELECT COUNT(*) FROM kategori")->fetch_row()[0];
$stokHabis     = $db->query("SELECT COUNT(*) FROM produk WHERE stok = 0")->fetch_row()[0];
$totalLaporan  = $db->query("SELECT COUNT(*) FROM laporan")->fetch_row()[0];

// Produk terbaru
$produkTerbaru = $db->query(
    "SELECT p.nama, k.nama AS kategori, p.harga, p.stok
     FROM produk p JOIN kategori k ON k.id = p.kategori_id
     ORDER BY p.id DESC LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// Produk per kategori
$perKategori = $db->query(
    "SELECT k.nama, COUNT(p.id) AS jumlah
     FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id
     GROUP BY k.id ORDER BY jumlah DESC"
)->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="small-box bg-primary text-white">
            <div class="inner">
                <h3><?= number_format($totalProduk) ?></h3>
                <p>Total Produk</p>
            </div>
            <div class="icon"><i class="bi bi-box-seam"></i></div>
            <a href="produk.php" class="small-box-footer text-white-50">
                Kelola Produk <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="small-box bg-success text-white">
            <div class="inner">
                <h3><?= $totalKategori ?></h3>
                <p>Kategori Produk</p>
            </div>
            <div class="icon"><i class="bi bi-tags"></i></div>
            <a href="produk.php" class="small-box-footer text-white-50">
                Lihat Produk <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="small-box bg-warning text-white">
            <div class="inner">
                <h3><?= $stokHabis ?></h3>
                <p>Produk Stok Habis</p>
            </div>
            <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
            <a href="produk.php?filter=habis" class="small-box-footer text-white-50">
                Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="small-box bg-danger text-white">
            <div class="inner">
                <h3><?= $totalLaporan ?></h3>
                <p>Laporan Masalah</p>
            </div>
            <div class="icon"><i class="bi bi-flag"></i></div>
            <a href="laporan.php" class="small-box-footer text-white-50">
                Lihat Laporan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Produk Terbaru -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>Produk Terbaru</h6>
                <a href="produk.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produkTerbaru as $p): ?>
                            <tr>
                                <td class="ps-3 fw-medium" style="font-size:14px;"><?= htmlspecialchars($p['nama']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['kategori']) ?></span></td>
                                <td style="font-size:14px;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($p['stok'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $p['stok'] ?> unit</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
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

    <!-- Produk per Kategori -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>Produk per Kategori</h6>
            </div>
            <div class="card-body">
                <?php foreach ($perKategori as $k): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:13px;font-weight:500;"><?= htmlspecialchars($k['nama']) ?></span>
                        <span class="text-muted" style="font-size:13px;"><?= $k['jumlah'] ?> produk</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:10px;">
                        <div class="progress-bar bg-primary" style="width:<?= $totalProduk > 0 ? round($k['jumlah'] / $totalProduk * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
