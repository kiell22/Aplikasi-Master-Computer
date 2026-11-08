<?php
require_once __DIR__ . '/koneksi.php';

// Ambil semua pengaturan
$set = [];
$res = $db->query("SELECT kunci, nilai FROM pengaturan");
if ($res) {
    while ($r = $res->fetch_assoc()) $set[$r['kunci']] = $r['nilai'];
}

// Default fallback
$namaToko   = $set['nama_toko']      ?? 'Master Computer Store';
$tagline    = $set['tagline']        ?? 'Temukan Kebutuhan Komputer Anda';
$descHero   = $set['deskripsi_hero'] ?? '';
$kota       = $set['kota']           ?? 'Jayapura, Papua';
$wa         = $set['whatsapp']       ?? '6282198925838';
$ttgJudul   = $set['tentang_judul']  ?? $namaToko;
$ttgIsi     = $set['tentang_isi']    ?? '';
$ktJudul    = $set['kontak_judul']   ?? 'Butuh informasi produk?';
$ktIsi      = $set['kontak_isi']     ?? '';
$lokasiJson = $set['lokasi_json']    ?? '[]';
$lokasiArr  = json_decode($lokasiJson, true) ?: [];

// Ambil kategori dari DB
$kategoriList = $db->query("SELECT nama FROM kategori ORDER BY id")->fetch_all(MYSQLI_ASSOC);

// Statistik untuk hero
$totalProduk = $db->query("SELECT COUNT(*) FROM produk WHERE stok > 0")->fetch_row()[0] ?? 0;
$totalKat    = count($kategoriList);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($namaToko) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descHero) ?>">
    <link rel="icon" href="assets/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="landing.css">
    <script>
        if (localStorage.getItem('tema') === 'gelap') document.documentElement.classList.add('dark');
    </script>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
    <div class="container header-content">
        <a href="#beranda" class="logo-wrap">
            <img src="assets/logo.jpg" alt="Logo <?= htmlspecialchars($namaToko) ?>" class="logo-img">
            <div class="logo">
                <span class="logo-main"><?= htmlspecialchars(strtoupper($namaToko)) ?></span>
                <small><?= htmlspecialchars($kota) ?></small>
            </div>
        </a>
        <div class="menu-wrap">
            <button class="kebab" id="kebabBtn" aria-label="Menu" aria-expanded="false">&#8942;</button>
            <div class="dropdown" id="dropdownMenu">
                <button id="temaBtn">🌙 Tema Gelap</button>
                <button data-modal="modalPeta">📍 Lokasi Toko</button>
                <button data-modal="modalLapor">🐞 Lapor Masalah</button>
                <a href="admin/login.php">🔑 Login Admin</a>
            </div>
        </div>
    </div>
</header>

<!-- ===== NAVBAR ===== -->
<nav id="mainNav">
    <div class="container nav-content">
        <div class="menu" id="navMenu">
            <a href="#beranda">Beranda</a>
            <a href="#produk">Produk</a>
            <a href="#tentang">Tentang</a>
            <a href="#lokasi">Lokasi</a>
            <a href="#kontak">Kontak</a>
        </div>
        <div class="nav-kanan">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari laptop, printer, PC...">
                <button type="button" id="cariBtn" aria-label="Cari"><i class="bi bi-search"></i></button>
            </div>
            <button class="nav-hamburger" id="navHamburger" aria-label="Buka menu" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
    <!-- Mobile menu -->
    <div class="nav-mobile" id="navMobile">
        <a href="#beranda">🏠 Beranda</a>
        <a href="#produk">📦 Produk</a>
        <a href="#tentang">ℹ️ Tentang</a>
        <a href="#lokasi">📍 Lokasi</a>
        <a href="#kontak">📞 Kontak</a>
        <div class="nav-mobile-divider"></div>
        <button id="temaBtnMobile">🌙 Tema Gelap</button>
        <button data-modal="modalPeta">📍 Lokasi Toko</button>
        <button data-modal="modalLapor">🐞 Lapor Masalah</button>
        <a href="admin/login.php">🔑 Login Admin</a>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero" id="beranda" style="background-image: url('assets/bg.png');">
    <div class="container">
        <div class="hero-text">
            <p class="label">KATALOG DIGITAL</p>
            <h1><?= htmlspecialchars($tagline) ?> <span>di <?= htmlspecialchars($namaToko) ?></span></h1>
            <p><?= htmlspecialchars($descHero) ?></p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <strong><?= number_format($totalProduk) ?>+</strong>
                    <span>Produk Tersedia</span>
                </div>
                <div class="hero-stat">
                    <strong><?= $totalKat ?></strong>
                    <span>Kategori</span>
                </div>
                <div class="hero-stat">
                    <strong><?= count($lokasiArr) ?></strong>
                    <span>Cabang</span>
                </div>
            </div>
            <a href="#produk" class="hero-button">Lihat Produk <i class="bi bi-arrow-down ms-1"></i></a>
        </div>
    </div>
</section>

<!-- ===== KATEGORI ===== -->
<section class="kategori-section">
    <div class="container">
        <div class="section-title">
            <p>KATEGORI</p>
            <h2>Pilih Kategori Produk</h2>
        </div>
        <div class="kategori-grid">
            <?php
            $ikonKat = ['Laptop'=>'💻','Printer'=>'🖨️','PC'=>'🖥️','UPS'=>'⚡','Projector'=>'📽️','PC AIO'=>'🖥️','Aksesoris'=>'⌨️'];
            foreach ($kategoriList as $k):
                $ikon = $ikonKat[$k['nama']] ?? '📦';
            ?>
            <button data-kategori="<?= htmlspecialchars($k['nama']) ?>">
                <?= $ikon ?>
                <span><?= htmlspecialchars($k['nama']) ?></span>
            </button>
            <?php endforeach; ?>
            <button data-semua>📦<span>Semua Produk</span></button>
        </div>
    </div>
</section>

<!-- ===== PRODUK ===== -->
<section class="produk-section" id="produk">
    <div class="container">
        <div class="produk-header">
            <div>
                <p class="label">KOLEKSI PRODUK</p>
                <h2>Produk <?= htmlspecialchars($namaToko) ?></h2>
            </div>
            <div class="produk-aksi">
                <label class="stok-toggle">
                    <input type="checkbox" id="filterStok"> Hanya ada stok
                </label>
                <select id="filterMerek" class="merek-select" aria-label="Filter merek">
                    <option value="">Semua Merek</option>
                </select>
                <button class="reset-button" data-semua>Semua Produk</button>
            </div>
        </div>
        <div id="produkContainer" class="produk-grid"></div>
        <div class="muat-wrap">
            <button id="muatLebih" class="hero-button muat-btn" style="display:none;">Muat lebih banyak</button>
        </div>
        <div id="tidakDitemukan" class="empty-search">
            <div>🔎</div>
            <h3>Produk tidak ditemukan</h3>
            <p>Coba gunakan kata pencarian lain.</p>
        </div>
    </div>
</section>

<!-- ===== TENTANG ===== -->
<section class="tentang-section" id="tentang">
    <div class="container tentang-grid">
        <div>
            <p class="label">TENTANG KAMI</p>
            <h2><?= htmlspecialchars($ttgJudul) ?></h2>
        </div>
        <div>
            <?php foreach (explode("\n\n", $ttgIsi) as $par): ?>
                <p><?= nl2br(htmlspecialchars(trim($par))) ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== LOKASI ===== -->
<section class="lokasi-section" id="lokasi">
    <div class="container">
        <div class="produk-header">
            <div>
                <p class="label">LOKASI TOKO</p>
                <h2>Toko Pusat dan Cabang</h2>
            </div>
            <button class="reset-button" data-modal="modalPeta">📍 Lihat Peta</button>
        </div>
        <div class="lokasi-grid">
            <?php foreach ($lokasiArr as $i => $lok): ?>
            <div class="lokasi-card <?= !empty($lok['pusat']) ? 'pusat' : '' ?>">
                <h3><?= htmlspecialchars($lok['nama']) ?></h3>
                <p><?= htmlspecialchars($lok['alamat']) ?></p>
                <button class="reset-button" onclick="bukaModalPeta(<?= $i ?>)">Lihat di Peta</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== MODAL PETA ===== -->
<div class="modal" id="modalPeta">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Lokasi <?= htmlspecialchars($namaToko) ?></h3>
            <button class="modal-x" data-tutup>✕</button>
        </div>
        <div class="peta-tabs" id="petaTabs">
            <?php foreach ($lokasiArr as $i => $lok): ?>
            <button onclick="pilihLokasi(<?= $i ?>)" id="tabLokasi<?= $i ?>">
                <?= !empty($lok['pusat']) ? 'Pusat' : htmlspecialchars(str_replace('Cabang ', '', $lok['nama'])) ?>
            </button>
            <?php endforeach; ?>
        </div>
        <iframe id="petaFrame" class="peta-frame" loading="lazy" title="Peta lokasi"></iframe>
        <p class="peta-alamat" id="petaAlamat"></p>
        <a id="petaLink" class="hero-button" target="_blank" rel="noopener">Buka di Google Maps</a>
    </div>
</div>

<!-- ===== MODAL LAPOR ===== -->
<div class="modal" id="modalLapor">
    <div class="modal-box kecil">
        <div class="modal-head">
            <h3>Lapor Masalah</h3>
            <button class="modal-x" data-tutup>✕</button>
        </div>
        <p class="peta-alamat">Menemukan error atau data yang salah? Beritahu admin.</p>
        <label for="lapNama">Nama (boleh dikosongkan)</label>
        <input type="text" id="lapNama" maxlength="80">
        <label for="lapPesan">Masalah yang ditemukan</label>
        <textarea id="lapPesan" rows="5" maxlength="1000" placeholder="Contoh: harga laptop ASUS tidak sesuai..."></textarea>
        <p id="lapStatus" class="lap-status"></p>
        <button class="hero-button lap-kirim" id="lapKirim">Kirim Laporan</button>
    </div>
</div>

<!-- ===== MODAL DETAIL PRODUK ===== -->
<div class="modal" id="modalDetail">
    <div class="modal-box detail-box">
        <div class="modal-head">
            <h3>Detail Produk</h3>
            <button class="modal-x" data-tutup>✕</button>
        </div>
        <div class="detail-grid">
            <div class="detail-gambar" id="detailGambar"></div>
            <div class="detail-info">
                <div class="produk-kategori" id="detailKategori"></div>
                <h2 id="detailNama"></h2>
                <div class="harga" id="detailHarga"></div>
                <div class="stok" id="detailStok"></div>
                <p class="detail-deskripsi" id="detailDeskripsi"></p>
                <a id="detailWa" class="whatsapp-button" target="_blank" rel="noopener">💬 Tanya via WhatsApp</a>
            </div>
        </div>
    </div>
</div>

<!-- ===== KONTAK ===== -->
<section class="kontak-section" id="kontak">
    <div class="container kontak-box">
        <div>
            <p class="label">INFORMASI</p>
            <h2><?= htmlspecialchars($ktJudul) ?></h2>
            <p><?= htmlspecialchars($ktIsi) ?></p>
        </div>
        <div class="kontak-buttons">
            <a href="https://wa.me/<?= htmlspecialchars($wa) ?>" target="_blank" class="whatsapp-button">
                💬 WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container footer-content">
        <div>
            <strong><?= htmlspecialchars(strtoupper($namaToko)) ?></strong>
            <p><?= htmlspecialchars($kota) ?></p>
        </div>
        <p>© <?= date('Y') ?> <?= htmlspecialchars($namaToko) ?></p>
    </div>
</footer>

<!-- Data lokasi untuk JS -->
<script>
const LOKASI = <?= $lokasiJson ?>;
const WA_NUMBER = '<?= htmlspecialchars($wa) ?>';
</script>
<script src="script.js"></script>
<script src="ui.js"></script>
</body>
</html>
