/* =================================================
   admin.js
   Halaman admin: login, kelola produk, dan laporan pengunjung.
   Membutuhkan script.js (untuk "$", "esc", "produk", "ambilProduk").
================================================= */

let idEdit = null;   // id produk yang sedang diedit (null = mode tambah)


/* =================================================
   PANGGIL API
================================================= */

async function api(aksi, data) {
    const opsi = data ? { method: "POST", body: JSON.stringify(data) } : {};

    try {
        const res = await fetch("api.php?aksi=" + aksi, opsi);
        return { ok: res.ok, data: await res.json() };
    } catch (e) {
        return { ok: false, data: { error: "Gagal terhubung ke server." } };
    }
}


/* =================================================
   LOGIN & LOGOUT
================================================= */

async function loginAdmin(event) {
    event.preventDefault();

    const r = await api("login", {
        username: $("username").value,
        password: $("password").value
    });

    if (r.ok) {
        tampilkanAdmin();
    } else {
        $("loginError").textContent = r.data.error;
    }
}

async function logoutAdmin() {
    await api("logout", {});
    location.reload();
}

async function tampilkanAdmin() {
    $("loginSection").style.display = "none";
    $("adminPanel").style.display = "block";

    tampilkanLaporan();
    isiMerekAdmin();
    await tampilkanDaftarAdmin();
}


/* =================================================
   LAPORAN DARI PENGUNJUNG
================================================= */

function buatItemLaporan(l) {
    const item = document.createElement("div");
    item.className = "admin-product-item";
    item.innerHTML = `
        <div>
            <strong>${esc(l.nama) || "Anonim"}</strong>
            <span>${esc(l.dibuat)}</span>
            <span class="laporan-pesan">${esc(l.pesan)}</span>
        </div>
        <button>Hapus</button>`;

    item.querySelector("button").addEventListener("click", () => hapusLaporan(l.id));
    return item;
}

async function tampilkanLaporan() {
    const box = $("laporanList");
    const r = await api("laporan_list");

    if (!r.ok) {
        box.innerHTML = "<p>Tabel laporan belum dibuat. Import database.sql (atau tambah_laporan.sql) di phpMyAdmin.</p>";
        return;
    }

    box.innerHTML = r.data.length ? "" : "<p>Belum ada laporan.</p>";
    r.data.forEach(l => box.appendChild(buatItemLaporan(l)));
}

async function hapusLaporan(id) {
    if (!confirm("Hapus laporan ini?")) return;

    await api("laporan_hapus", { id: id });
    tampilkanLaporan();
}


/* =================================================
   FORM PRODUK (tambah & edit)
================================================= */

async function isiMerekAdmin() {
    const r = await api("merek_list");
    if (!r.ok) return;

    const pilih = $("merekProduk");

    r.data.forEach(nama => {
        const o = document.createElement("option");
        o.value = nama;
        o.textContent = nama;
        pilih.appendChild(o);
    });
}

function dataForm() {
    return {
        nama: $("namaProduk").value.trim(),
        kategori: $("kategoriProduk").value,
        merek: $("merekProduk").value,
        harga: Number($("hargaProduk").value),
        stok: Number($("stokProduk").value),
        gambar: $("gambarProduk").value.trim(),
        deskripsi: $("deskripsiProduk").value.trim()
    };
}

async function simpanProduk(event) {
    event.preventDefault();

    const data = dataForm();
    const edit = idEdit !== null;
    if (edit) data.id = idEdit;

    const r = await api(edit ? "ubah" : "tambah", data);
    if (!r.ok) {
        alert(r.data.error);
        return;
    }

    batalEdit();
    await tampilkanDaftarAdmin();
    alert(edit ? "Produk berhasil diperbarui!" : "Produk berhasil ditambahkan!");
}

function mulaiEdit(id) {
    const p = produk.find(x => x.id === id);
    if (!p) return;

    idEdit = id;

    $("namaProduk").value = p.nama;
    $("kategoriProduk").value = p.kategori;
    $("merekProduk").value = p.merek || "";
    $("hargaProduk").value = p.harga;
    $("stokProduk").value = p.stok;
    $("gambarProduk").value = p.gambar || "";
    $("deskripsiProduk").value = p.deskripsi || "";

    $("judulForm").textContent = "Edit Produk";
    $("tombolSimpan").textContent = "Simpan Perubahan";
    $("tombolBatal").style.display = "block";

    const kartu = document.querySelector(".admin-form-card");
    kartu.classList.add("sedang-edit");
    kartu.scrollIntoView({ behavior: "smooth" });
}

function batalEdit() {
    idEdit = null;
    $("formProduk").reset();

    $("judulForm").textContent = "Tambahkan Produk Baru";
    $("tombolSimpan").textContent = "+ Tambahkan Produk";
    $("tombolBatal").style.display = "none";

    document.querySelector(".admin-form-card").classList.remove("sedang-edit");
}


/* =================================================
   DAFTAR PRODUK
================================================= */

function buatItemProduk(item) {
    const div = document.createElement("div");
    div.className = "admin-product-item";
    div.innerHTML = `
        <div>
            <strong>${esc(item.nama)}</strong>
            <span>${esc(item.kategori)}${item.merek ? " • " + esc(item.merek) : ""} • Rp ${item.harga.toLocaleString("id-ID")} • Stok ${item.stok}</span>
        </div>
        <div class="aksi-admin">
            <button class="edit-btn">Edit</button>
            <button class="hapus-btn">Hapus</button>
        </div>`;

    div.querySelector(".edit-btn").addEventListener("click", () => mulaiEdit(item.id));
    div.querySelector(".hapus-btn").addEventListener("click", () => hapusProduk(item.id));
    return div;
}

async function tampilkanDaftarAdmin() {
    await ambilProduk();
    renderDaftarAdmin();
}

function renderDaftarAdmin() {
    const kata = $("cariAdmin").value.toLowerCase().trim();
    const container = $("adminProductList");
    container.innerHTML = "";

    const daftar = produk.filter(i =>
        !kata ||
        i.nama.toLowerCase().includes(kata) ||
        i.kategori.toLowerCase().includes(kata) ||
        (i.merek || "").toLowerCase().includes(kata));

    if (!daftar.length) {
        container.innerHTML = "<p>Produk tidak ditemukan.</p>";
        return;
    }

    daftar.forEach(item => container.appendChild(buatItemProduk(item)));
}

async function hapusProduk(id) {
    if (!confirm("Yakin ingin menghapus produk ini?")) return;

    await api("hapus", { id: id });
    if (idEdit === id) batalEdit();
    await tampilkanDaftarAdmin();
}


/* =================================================
   PASANG EVENT & CEK SESI LOGIN
================================================= */

$("formLogin").addEventListener("submit", loginAdmin);
$("logoutBtn").addEventListener("click", logoutAdmin);
$("formProduk").addEventListener("submit", simpanProduk);
$("tombolBatal").addEventListener("click", batalEdit);
$("cariAdmin").addEventListener("input", renderDaftarAdmin);

// jika sesi admin masih aktif, langsung tampilkan panel
api("cek").then(r => {
    if (r.data.login) tampilkanAdmin();
});
