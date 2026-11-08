<?php
// ============================================
// PENGATURAN DATABASE & LOGIN ADMIN
// ============================================

// --- Database (default XAMPP) ---
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_nama = getenv("DB_NAME") ?: "etalase_master";

// --- Kredensial login API lama (opsional, atur melalui environment) ---
define("ADMIN_USER", getenv("APP_ADMIN_USER") ?: "admin");
define("ADMIN_PASS", getenv("APP_ADMIN_PASS") ?: "");

// --- Koneksi ---
$db = new mysqli($db_host, $db_user, $db_pass, $db_nama);

if ($db->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Koneksi database gagal"]));
}

$db->set_charset("utf8mb4");
