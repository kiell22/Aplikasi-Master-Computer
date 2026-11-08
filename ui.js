/* =================================================
   ui.js
   Modal, menu titik tiga, tema gelap/terang, peta lokasi,
   dan form lapor masalah.
   (membutuhkan "$" dari script.js)
================================================= */

// LOKASI & WA_NUMBER diinjeksi dari PHP (index.php)
// Fallback untuk index.html lama
if (typeof LOKASI === 'undefined') var LOKASI = [
    { nama: "Toko Pusat \u2013 Jayapura Utara", pusat: true, alamat: "Jl. Percetakan Negara No.94, Gurabesi, Kec. Jayapura Utara, Kota Jayapura, Papua 99111" },
    { nama: "Cabang Abepura", alamat: "Kota Baru, Kec. Abepura, Kota Jayapura, Papua 99351" },
    { nama: "Cabang Koya", alamat: "Jalan Poros SMP Blk. N No.8, West Koya, Muara Tami, Jayapura City, Papua 99351" },
    { nama: "Cabang Sentani", alamat: "Jl. Raya Kemiri No.53, Hinekombe, Kec. Sentani, Kabupaten Jayapura, Papua 99352" },
    { nama: "Cabang Timika", alamat: "Sebelah kiri lampu merah, Master Computer Timika, Jl. Hasanudin, Nawaripi, Kec. Mimika Baru, Kabupaten Mimika, Papua 99971" },
    { nama: "Cabang Sorong", alamat: "Jl. Basuki Rahmat Km.9,5 No.45, Klasaman, Distrik Sorong Timur, Kota Sorong, Papua Barat Daya 98416" }
];
if (typeof WA_NUMBER === 'undefined') var WA_NUMBER = '6282198925838';


/* =================================================
   HAMBURGER MOBILE NAV
================================================= */

const navHamburger = $("navHamburger");
const navMobile = $("navMobile");

if (navHamburger && navMobile) {
    navHamburger.addEventListener("click", e => {
        e.stopPropagation();
        const buka = navMobile.classList.toggle("buka");
        navHamburger.setAttribute("aria-expanded", buka);
        navHamburger.querySelector("i").className = buka ? "bi bi-x" : "bi bi-list";
    });

    // Tutup saat klik link di mobile menu
    navMobile.querySelectorAll("a").forEach(a => {
        a.addEventListener("click", () => {
            navMobile.classList.remove("buka");
            navHamburger.setAttribute("aria-expanded", "false");
            navHamburger.querySelector("i").className = "bi bi-list";
        });
    });

    document.addEventListener("click", e => {
        if (!e.target.closest("#mainNav")) {
            navMobile.classList.remove("buka");
            navHamburger.setAttribute("aria-expanded", "false");
            navHamburger.querySelector("i").className = "bi bi-list";
        }
    });
}

// Tema button di mobile menu
const temaBtnMobile = $("temaBtnMobile");
if (temaBtnMobile) {
    temaBtnMobile.addEventListener("click", () => {
        const gelap = document.documentElement.classList.toggle("dark");
        localStorage.setItem("tema", gelap ? "gelap" : "terang");
        labelTema();
    });
}


/* =================================================
   MODAL
================================================= */

function bukaModal(id) {
    $(id).classList.add("buka");
    tutupDropdown();
}

function tutupModal() {
    document.querySelectorAll(".modal").forEach(m => m.classList.remove("buka"));
}

document.querySelectorAll("[data-modal]").forEach(b =>
    b.addEventListener("click", () => bukaModal(b.dataset.modal)));

document.querySelectorAll("[data-tutup]").forEach(b =>
    b.addEventListener("click", tutupModal));

document.querySelectorAll(".modal").forEach(m =>
    m.addEventListener("click", e => { if (e.target === m) tutupModal(); }));


/* =================================================
   DROPDOWN TITIK TIGA
================================================= */

function tutupDropdown() {
    $("dropdownMenu").classList.remove("buka");
    $("kebabBtn").setAttribute("aria-expanded", "false");
}

$("kebabBtn").addEventListener("click", e => {
    e.stopPropagation();
    const buka = $("dropdownMenu").classList.toggle("buka");
    $("kebabBtn").setAttribute("aria-expanded", buka);
});

document.addEventListener("click", e => {
    if (!e.target.closest(".menu-wrap")) tutupDropdown();
});

document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
        tutupDropdown();
        tutupModal();
    }
});


/* =================================================
   TEMA TERANG / GELAP
================================================= */

function labelTema() {
    const gelap = document.documentElement.classList.contains("dark");
    const label = gelap ? "\u2600\ufe0f Tema Terang" : "\uD83C\uDF19 Tema Gelap";
    if ($("temaBtn")) $("temaBtn").textContent = label;
    if ($("temaBtnMobile")) $("temaBtnMobile").textContent = label;
}

$("temaBtn").addEventListener("click", () => {
    const gelap = document.documentElement.classList.toggle("dark");
    localStorage.setItem("tema", gelap ? "gelap" : "terang");
    labelTema();
    tutupDropdown();
});

labelTema();


/* =================================================
   PETA LOKASI
================================================= */

function pilihLokasi(i) {
    const l = LOKASI[i];
    if (!l) return;
    const q = encodeURIComponent((l.alamat.includes("Master Computer") ? "" : "Master Computer ") + l.alamat);

    $("petaFrame").src = "https://www.google.com/maps?q=" + q + "&output=embed";
    $("petaLink").href = "https://www.google.com/maps/search/?api=1&query=" + q;
    $("petaAlamat").textContent = l.nama + " \u2014 " + l.alamat;

    document.querySelectorAll("#petaTabs button").forEach((b, n) =>
        b.classList.toggle("aktif", n === i));
}

// Fungsi dipanggil dari onclick PHP-rendered button
function bukaModalPeta(i) {
    bukaModal("modalPeta");
    pilihLokasi(i);
}

// Jika tab peta belum dirender oleh PHP (index.html lama), buat dinamis
const petaTabs = $("petaTabs");
if (petaTabs && petaTabs.children.length === 0) {
    LOKASI.forEach((l, i) => {
        const tab = document.createElement("button");
        tab.textContent = l.pusat ? "Pusat" : l.nama.replace("Cabang ", "");
        tab.onclick = () => pilihLokasi(i);
        petaTabs.appendChild(tab);
    });
}

// Jika kartu lokasi belum dirender PHP, buat dinamis
const lokasiGrid = $("lokasiGrid");
if (lokasiGrid && lokasiGrid.children.length === 0) {
    LOKASI.forEach((l, i) => {
        const kartu = document.createElement("div");
        kartu.className = "lokasi-card" + (l.pusat ? " pusat" : "");
        kartu.innerHTML = `<h3></h3><p></p><button class="reset-button">Lihat di Peta</button>`;
        kartu.querySelector("h3").textContent = l.nama;
        kartu.querySelector("p").textContent = l.alamat;
        kartu.querySelector("button").onclick = () => bukaModalPeta(i);
        lokasiGrid.appendChild(kartu);
    });
}

if (LOKASI.length > 0) pilihLokasi(0);


/* =================================================
   LAPOR MASALAH
================================================= */

function tampilStatusLapor(teks, warna) {
    const status = $("lapStatus");
    status.textContent = teks;
    status.style.color = warna;
}

$("lapKirim").addEventListener("click", async () => {
    try {
        const res = await fetch("api.php?aksi=lapor", {
            method: "POST",
            body: JSON.stringify({ nama: $("lapNama").value, pesan: $("lapPesan").value })
        });
        const data = await res.json();

        if (res.ok) {
            tampilStatusLapor("Terima kasih, laporan terkirim ke admin.", "#2e7d32");
            $("lapPesan").value = "";
        } else {
            tampilStatusLapor(data.error || "Gagal mengirim laporan.", "#e53935");
        }
    } catch (e) {
        tampilStatusLapor("Gagal terhubung ke server.", "#e53935");
    }
});
