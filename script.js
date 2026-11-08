/* =================================================
   script.js
   Data produk, tampilan katalog, filter, dan detail produk.
   Dipakai oleh index.html dan admin.html.
   (ui.js dimuat sesudah file ini dan memakai "$" dari sini)
================================================= */


/* =================================================
   HELPER UMUM
================================================= */

const $ = id => document.getElementById(id);

/** Amankan teks sebelum dimasukkan ke innerHTML (teks maupun atribut). */
function esc(teks) {
    const peta = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
    return String(teks ?? "").replace(/[&<>"']/g, c => peta[c]);
}

function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0
    }).format(angka);
}


/* =================================================
   DATA PRODUK (diambil dari database lewat api.php)
================================================= */

let produk = [];

async function ambilProduk() {
    try {
        const res = await fetch("api.php?aksi=list");
        const data = await res.json();
        produk = Array.isArray(data) ? data : [];
    } catch (e) {
        produk = [];
        console.error("Gagal mengambil data:", e);
    }
}


/* =================================================
   ILUSTRASI 2D (dipakai jika produk belum punya foto)
================================================= */

const ABU = "#546e7a";
const GELAP = "#263238";
const TERANG = "#e3f2fd";

/** Setiap kategori punya gambar SVG sendiri; "a" = warna aksen produk. */
const ILUSTRASI = {

    "Laptop": a => `
        <rect x="22" y="10" width="76" height="50" rx="4" fill="${a}"/>
        <rect x="27" y="15" width="66" height="40" rx="2" fill="${TERANG}"/>
        <rect x="34" y="24" width="32" height="4" rx="2" fill="${a}" opacity=".5"/>
        <rect x="34" y="32" width="46" height="4" rx="2" fill="${a}" opacity=".3"/>
        <rect x="34" y="40" width="24" height="4" rx="2" fill="${a}" opacity=".3"/>
        <path d="M8 64h104l-6 10H14z" fill="#90a4ae"/>
        <rect x="50" y="64" width="20" height="3" rx="1.5" fill="#607d8b"/>`,

    "Printer": a => `
        <rect x="34" y="10" width="52" height="26" rx="2" fill="#fff" stroke="#b0bec5" stroke-width="2"/>
        <rect x="16" y="32" width="88" height="36" rx="6" fill="${ABU}"/>
        <rect x="26" y="40" width="30" height="6" rx="3" fill="${GELAP}"/>
        <circle cx="88" cy="43" r="4" fill="${a}"/>
        <rect x="32" y="58" width="56" height="26" rx="2" fill="#fff" stroke="#b0bec5" stroke-width="2"/>
        <rect x="40" y="66" width="40" height="3" rx="1.5" fill="#cfd8dc"/>
        <rect x="40" y="73" width="30" height="3" rx="1.5" fill="#cfd8dc"/>`,

    "PC": a => `
        <rect x="36" y="6" width="48" height="78" rx="5" fill="${ABU}"/>
        <rect x="44" y="14" width="32" height="5" rx="2" fill="${GELAP}"/>
        <rect x="44" y="24" width="32" height="5" rx="2" fill="${GELAP}"/>
        <circle cx="60" cy="48" r="7" fill="${a}"/>
        <circle cx="60" cy="48" r="3" fill="${TERANG}"/>
        <rect x="44" y="68" width="32" height="8" rx="2" fill="${GELAP}"/>`,

    "UPS": () => `
        <rect x="26" y="16" width="68" height="58" rx="6" fill="#37474f"/>
        <rect x="34" y="24" width="30" height="10" rx="2" fill="#a5d6a7"/>
        <circle cx="78" cy="29" r="4" fill="#66bb6a"/>
        <path d="M64 40 52 58h9l-3 12 14-20h-9z" fill="#ffca28"/>
        <rect x="34" y="62" width="14" height="4" rx="2" fill="${GELAP}"/>`,

    "Projector": a => `
        <rect x="14" y="30" width="92" height="38" rx="8" fill="${ABU}"/>
        <circle cx="80" cy="49" r="15" fill="${GELAP}"/>
        <circle cx="80" cy="49" r="9" fill="${a}"/>
        <circle cx="76" cy="45" r="3" fill="${TERANG}" opacity=".7"/>
        <rect x="24" y="40" width="26" height="3" rx="1.5" fill="${GELAP}"/>
        <rect x="24" y="47" width="26" height="3" rx="1.5" fill="${GELAP}"/>
        <rect x="24" y="54" width="26" height="3" rx="1.5" fill="${GELAP}"/>
        <rect x="24" y="68" width="10" height="5" rx="2" fill="${GELAP}"/>
        <rect x="86" y="68" width="10" height="5" rx="2" fill="${GELAP}"/>`,

    "PC AIO": a => `
        <rect x="12" y="8" width="96" height="58" rx="5" fill="${ABU}"/>
        <rect x="17" y="13" width="86" height="43" rx="2" fill="${a}"/>
        <rect x="24" y="21" width="40" height="5" rx="2.5" fill="${TERANG}" opacity=".7"/>
        <rect x="24" y="30" width="56" height="5" rx="2.5" fill="${TERANG}" opacity=".4"/>
        <path d="M52 66h16l4 14H48z" fill="#90a4ae"/>
        <rect x="38" y="80" width="44" height="5" rx="2.5" fill="#607d8b"/>`,

    "Aksesoris": a => `
        <rect x="14" y="48" width="64" height="30" rx="4" fill="${ABU}"/>
        <g fill="${TERANG}">
            <rect x="19" y="53" width="8" height="6" rx="1.5"/><rect x="30" y="53" width="8" height="6" rx="1.5"/>
            <rect x="41" y="53" width="8" height="6" rx="1.5"/><rect x="52" y="53" width="8" height="6" rx="1.5"/>
            <rect x="63" y="53" width="9" height="6" rx="1.5"/>
            <rect x="19" y="62" width="8" height="6" rx="1.5"/><rect x="30" y="62" width="8" height="6" rx="1.5"/>
            <rect x="41" y="62" width="8" height="6" rx="1.5"/><rect x="52" y="62" width="8" height="6" rx="1.5"/>
            <rect x="63" y="62" width="9" height="6" rx="1.5"/>
            <rect x="26" y="71" width="40" height="4" rx="1.5"/>
        </g>
        <rect x="82" y="26" width="26" height="46" rx="13" fill="${a}"/>
        <path d="M95 26v20M82 46h26" stroke="${TERANG}" stroke-width="2" fill="none"/>`

};

/** Warna aksen ilustrasi: merah untuk gaming, hijau untuk Zenbook, biru untuk lainnya. */
function warnaAksen(nama) {
    const n = (nama || "").toUpperCase();
    if (/ROG|TUF|FX\d|GAMING/.test(n)) return "#c62828";
    if (/ZEN/.test(n)) return "#00897b";
    return "#1565c0";
}

function ikonProduk(item) {
    const gambar = ILUSTRASI[item.kategori];
    if (!gambar) return "📦";

    return '<svg viewBox="0 0 120 90" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' +
        esc(item.kategori || "Produk") + '">' +
        gambar(warnaAksen(item.nama)) +
        "</svg>";
}


/* =================================================
   TAMPILKAN PRODUK
================================================= */

const PER_HALAMAN = 24;
let hasilSaatIni = [];
let jumlahTampil = PER_HALAMAN;

function buatKartuProduk(item) {

    const card = document.createElement("div");
    card.className = "produk-card";
    card.tabIndex = 0;
    card.setAttribute("role", "button");
    card.setAttribute("aria-label", "Lihat detail " + item.nama);
    card.addEventListener("click", () => bukaDetail(item));
    card.addEventListener("keydown", e => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            bukaDetail(item);
        }
    });

    const gambarHTML = item.gambar
        ? `<img src="${esc(item.gambar)}" alt="${esc(item.nama)}">`
        : `<div class="no-image">${ikonProduk(item)}</div>`;

    card.innerHTML = `
        <div class="produk-image">
            ${gambarHTML}
        </div>

        <div class="produk-info">
            <div class="produk-kategori">${esc(item.kategori)}</div>
            <h3>${esc(item.nama)}</h3>
            <p class="produk-deskripsi">${esc(item.deskripsi)}</p>
            <div class="harga">${formatRupiah(item.harga)}</div>
            <div class="stok ${item.stok > 0 ? "" : "habis"}">
                ${item.stok > 0 ? "Stok: " + item.stok : "Stok habis"}
            </div>
            <div class="lihat-detail">Lihat detail →</div>
        </div>`;

    return card;
}

function tampilkanProduk(data = produk, reset = true) {

    if (reset) jumlahTampil = PER_HALAMAN;
    hasilSaatIni = data;

    const container = $("produkContainer");
    const tidakDitemukan = $("tidakDitemukan");
    const tombol = $("muatLebih");

    container.innerHTML = "";

    if (hasilSaatIni.length === 0) {
        tidakDitemukan.style.display = "block";
        if (tombol) tombol.style.display = "none";
        return;
    }

    tidakDitemukan.style.display = "none";

    hasilSaatIni.slice(0, jumlahTampil).forEach(item => {
        container.appendChild(buatKartuProduk(item));
    });

    const sisa = hasilSaatIni.length - jumlahTampil;

    if (tombol) {
        tombol.style.display = sisa > 0 ? "inline-block" : "none";
        tombol.textContent = "Muat lebih banyak (" + sisa + " produk lagi)";
    }
}

function muatLebih() {
    jumlahTampil += PER_HALAMAN;
    tampilkanProduk(hasilSaatIni, false);
}


/* =================================================
   DETAIL PRODUK (pop-up)
================================================= */

function bukaDetail(item) {

    const kotak = $("detailGambar");
    kotak.innerHTML = "";

    if (item.gambar) {
        const img = document.createElement("img");
        img.src = item.gambar;
        img.alt = item.nama;
        kotak.appendChild(img);
    } else {
        kotak.innerHTML = ikonProduk(item);
    }

    $("detailKategori").textContent = item.kategori + (item.merek ? " • " + item.merek : "");
    $("detailNama").textContent = item.nama;
    $("detailHarga").textContent = formatRupiah(item.harga);

    const stok = $("detailStok");
    stok.className = "stok" + (item.stok > 0 ? "" : " habis");
    stok.textContent = item.stok > 0 ? "Stok tersedia: " + item.stok : "Stok habis";

    $("detailDeskripsi").textContent = item.deskripsi || "Belum ada deskripsi untuk produk ini.";

    // Nomor WhatsApp diambil dari tombol WhatsApp di bagian kontak
    const tombolWa = document.querySelector("#kontak .whatsapp-button");
    const cocok = tombolWa ? tombolWa.href.match(/wa\.me\/(\d+)/) : null;
    const link = $("detailWa");

    if (cocok) {
        const pesan = "Halo Master Computer Store, saya tertarik dengan produk: " +
            item.nama + " (" + formatRupiah(item.harga) + "). Apakah masih tersedia?";
        link.href = "https://wa.me/" + cocok[1] + "?text=" + encodeURIComponent(pesan);
        link.style.display = "";
    } else {
        link.style.display = "none";
    }

    bukaModal("modalDetail");
}


/* =================================================
   FILTER GABUNGAN: pencarian + kategori + merek + stok
================================================= */

let kategoriAktif = "";

function terapkanFilter() {

    const kata = $("searchInput").value.toLowerCase().trim();
    const merek = $("filterMerek").value;
    const hanyaStok = $("filterStok").checked;

    const hasil = produk.filter(item =>
        (!kategoriAktif || item.kategori === kategoriAktif) &&
        (!merek || item.merek === merek) &&
        (!hanyaStok || item.stok > 0) &&
        (!kata ||
            item.nama.toLowerCase().includes(kata) ||
            item.kategori.toLowerCase().includes(kata) ||
            (item.deskripsi || "").toLowerCase().includes(kata))
    );

    tampilkanProduk(hasil);
}

function keBagianProduk() {
    $("produk").scrollIntoView({ behavior: "smooth" });
}

function filterKategori(kategori) {
    kategoriAktif = kategori;
    terapkanFilter();
    keBagianProduk();
}

function tampilkanSemuaProduk() {
    kategoriAktif = "";
    $("searchInput").value = "";
    $("filterMerek").value = "";
    $("filterStok").checked = false;
    terapkanFilter();
    keBagianProduk();
}

function isiFilterMerek() {
    const pilih = $("filterMerek");
    if (!pilih) return;

    const daftar = [...new Set(produk.map(p => p.merek).filter(Boolean))].sort();

    daftar.forEach(m => {
        const o = document.createElement("option");
        o.value = m;
        o.textContent = m;
        pilih.appendChild(o);
    });
}


/* =================================================
   PASANG EVENT (khusus index.html)
================================================= */

function pasangEventKatalog() {

    $("searchInput").addEventListener("input", terapkanFilter);
    $("cariBtn").addEventListener("click", terapkanFilter);
    $("filterMerek").addEventListener("change", terapkanFilter);
    $("filterStok").addEventListener("change", terapkanFilter);
    $("muatLebih").addEventListener("click", muatLebih);

    document.querySelectorAll("[data-kategori]").forEach(tombol =>
        tombol.addEventListener("click", () => filterKategori(tombol.dataset.kategori)));

    document.querySelectorAll("[data-semua]").forEach(tombol =>
        tombol.addEventListener("click", tampilkanSemuaProduk));
}


/* =================================================
   SAAT WEBSITE DIBUKA
================================================= */

document.addEventListener("DOMContentLoaded", async function () {

    await ambilProduk();

    // admin.html tidak punya katalog, jadi berhenti di sini
    if (!$("produkContainer")) return;

    isiFilterMerek();
    tampilkanProduk();
    pasangEventKatalog();
});
