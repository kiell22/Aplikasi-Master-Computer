<?php
/**
 * API Etalase Master Computer
 *
 * Dipanggil lewat: api.php?aksi=<nama_aksi>
 *   Publik : list, login, cek, logout, lapor
 *   Admin  : tambah, ubah, hapus, merek_list, laporan_list, laporan_hapus
 */

session_start();
require __DIR__ . "/koneksi.php";
header("Content-Type: application/json; charset=utf-8");


/* =================================================
   HELPER
================================================= */

/** Kirim respons JSON lalu hentikan skrip. */
function keluar($data, int $kode = 200): void
{
    http_response_code($kode);
    echo json_encode($data);
    exit;
}

/** Tolak permintaan jika belum login sebagai admin. */
function wajibAdmin(): void
{
    if (empty($_SESSION["admin"])) {
        keluar(["error" => "Belum login"], 401);
    }
}

/** Cari id kategori berdasarkan nama; null jika tidak ada. */
function idKategori(mysqli $db, $nama): ?int
{
    $nama = (string)$nama;
    $stmt = $db->prepare("SELECT id FROM kategori WHERE nama = ?");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();

    return $baris ? (int)$baris["id"] : null;
}

/** Cari id merek berdasarkan nama; null jika kosong atau tidak ada. */
function idMerek(mysqli $db, $nama): ?int
{
    $nama = trim((string)$nama);
    if ($nama === "") {
        return null;
    }

    $stmt = $db->prepare("SELECT id FROM merek WHERE nama = ?");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_assoc();

    return $baris ? (int)$baris["id"] : null;
}

/**
 * Baca dan validasi data produk dari form admin (dipakai tambah & ubah).
 * Urutan hasil: nama, kategori_id, merek_id, harga, stok, gambar, deskripsi.
 */
function dataProduk(mysqli $db, array $body): array
{
    $nama  = trim($body["nama"] ?? "");
    $harga = (int)($body["harga"] ?? 0);
    $stok  = (int)($body["stok"] ?? 0);

    if ($nama === "") {
        keluar(["error" => "Data produk tidak lengkap"], 400);
    }
    if ($harga < 0 || $stok < 0) {
        keluar(["error" => "Harga dan stok tidak boleh minus"], 400);
    }

    $kategoriId = idKategori($db, $body["kategori"] ?? "");
    if ($kategoriId === null) {
        keluar(["error" => "Kategori tidak valid"], 400);
    }

    return [
        $nama,
        $kategoriId,
        idMerek($db, $body["merek"] ?? ""),
        $harga,
        $stok,
        trim($body["gambar"] ?? ""),
        trim($body["deskripsi"] ?? ""),
    ];
}


/* =================================================
   AKSI PUBLIK
================================================= */

/** Ambil semua produk. */
function aksiList(mysqli $db): void
{
    $sql = "SELECT p.id, p.nama, k.nama AS kategori, m.nama AS merek,
                   p.harga, p.stok, p.gambar, p.deskripsi
            FROM produk p
            JOIN kategori k ON k.id = p.kategori_id
            LEFT JOIN merek m ON m.id = p.merek_id
            ORDER BY p.id DESC";

    $produk = array_map(function ($baris) {
        $baris["id"]    = (int)$baris["id"];
        $baris["harga"] = (int)$baris["harga"];
        $baris["stok"]  = (int)$baris["stok"];
        return $baris;
    }, $db->query($sql)->fetch_all(MYSQLI_ASSOC));

    keluar($produk);
}

/** Login admin. */
function aksiLogin(array $body): void
{
    $userCocok = hash_equals(ADMIN_USER, (string)($body["username"] ?? ""));
    $passCocok = ADMIN_PASS !== "" && hash_equals(ADMIN_PASS, (string)($body["password"] ?? ""));

    if ($userCocok && $passCocok) {
        $_SESSION["admin"] = true;
        keluar(["ok" => true]);
    }

    keluar(["error" => "Username atau password salah."], 401);
}

/** Kirim laporan masalah dari pengunjung. */
function aksiLapor(mysqli $db, array $body): void
{
    $nama  = mb_substr(trim($body["nama"] ?? ""), 0, 80);
    $pesan = mb_substr(trim($body["pesan"] ?? ""), 0, 1000);

    if (mb_strlen($pesan) < 10) {
        keluar(["error" => "Pesan minimal 10 karakter."], 400);
    }
    if (time() - ($_SESSION["lapor_terakhir"] ?? 0) < 30) {
        keluar(["error" => "Tunggu sebentar sebelum mengirim laporan lagi."], 429);
    }

    $stmt = $db->prepare("INSERT INTO laporan (nama, pesan) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $pesan);
    $stmt->execute();

    $_SESSION["lapor_terakhir"] = time();
    keluar(["ok" => true]);
}


/* =================================================
   AKSI ADMIN
================================================= */

/** Tambah produk baru. */
function aksiTambah(mysqli $db, array $body): void
{
    wajibAdmin();
    [$nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi] = dataProduk($db, $body);

    $stmt = $db->prepare(
        "INSERT INTO produk (nama, kategori_id, merek_id, harga, stok, gambar, deskripsi)
         VALUES (?,?,?,?,?,?,?)"
    );
    $stmt->bind_param("siiiiss", $nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi);
    $stmt->execute();

    keluar(["ok" => true]);
}

/** Ubah produk yang sudah ada. */
function aksiUbah(mysqli $db, array $body): void
{
    wajibAdmin();

    $id = (int)($body["id"] ?? 0);
    if ($id <= 0) {
        keluar(["error" => "Data produk tidak lengkap"], 400);
    }

    [$nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi] = dataProduk($db, $body);

    $stmt = $db->prepare(
        "UPDATE produk
         SET nama=?, kategori_id=?, merek_id=?, harga=?, stok=?, gambar=?, deskripsi=?
         WHERE id=?"
    );
    $stmt->bind_param("siiiissi", $nama, $kategoriId, $merekId, $harga, $stok, $gambar, $deskripsi, $id);
    $stmt->execute();

    keluar(["ok" => true]);
}

/** Hapus produk. */
function aksiHapus(mysqli $db, array $body): void
{
    wajibAdmin();

    $id   = (int)($body["id"] ?? 0);
    $stmt = $db->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    keluar(["ok" => true]);
}

/** Daftar nama merek (untuk pilihan di form admin). */
function aksiMerekList(mysqli $db): void
{
    wajibAdmin();
    $merek = $db->query("SELECT nama FROM merek ORDER BY nama")->fetch_all(MYSQLI_ASSOC);
    keluar(array_column($merek, "nama"));
}

/** Daftar laporan masalah dari pengunjung. */
function aksiLaporanList(mysqli $db): void
{
    wajibAdmin();
    keluar($db->query("SELECT id, nama, pesan, dibuat FROM laporan ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC));
}

/** Hapus satu laporan. */
function aksiLaporanHapus(mysqli $db, array $body): void
{
    wajibAdmin();

    $id   = (int)($body["id"] ?? 0);
    $stmt = $db->prepare("DELETE FROM laporan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    keluar(["ok" => true]);
}


/* =================================================
   ROUTER
================================================= */

$aksi = $_GET["aksi"] ?? "list";
$body = json_decode(file_get_contents("php://input"), true);
if (!is_array($body)) {
    $body = [];
}

switch ($aksi) {
    // publik
    case "list":
        aksiList($db);
        break;
    case "login":
        aksiLogin($body);
        break;
    case "cek":
        keluar(["login" => !empty($_SESSION["admin"])]);
        break;
    case "logout":
        session_destroy();
        keluar(["ok" => true]);
        break;
    case "lapor":
        aksiLapor($db, $body);
        break;

    // admin
    case "tambah":
        aksiTambah($db, $body);
        break;
    case "ubah":
        aksiUbah($db, $body);
        break;
    case "hapus":
        aksiHapus($db, $body);
        break;
    case "merek_list":
        aksiMerekList($db);
        break;
    case "laporan_list":
        aksiLaporanList($db);
        break;
    case "laporan_hapus":
        aksiLaporanHapus($db, $body);
        break;

    default:
        keluar(["error" => "Aksi tidak dikenal"], 400);
}
