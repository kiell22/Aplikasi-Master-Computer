# Aplikasi Master Computer

Aplikasi katalog produk berbasis PHP dan MySQL untuk Master Computer.

## Persyaratan

- PHP dengan ekstensi `mysqli`
- MySQL atau MariaDB
- Apache (misalnya melalui XAMPP)

## Menjalankan secara lokal

1. Letakkan folder aplikasi di direktori web server.
2. Buat database bernama `etalase_master`.
3. Impor dump database lokal Anda melalui phpMyAdmin. Dump database tidak disertakan di repositori ini karena dapat berisi hash akun admin dan data toko.
4. Pengaturan database dapat diberikan melalui environment variable `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME`. Jika tidak diatur, aplikasi menggunakan konfigurasi lokal XAMPP (`localhost`, `root`, tanpa password, database `etalase_master`).
5. Buka `index.php` melalui server lokal.

Login admin aplikasi menggunakan akun pada tabel `users`. Kredensial login API lama, jika diperlukan, dikonfigurasi melalui `APP_ADMIN_USER` dan `APP_ADMIN_PASS`; login API dinonaktifkan bila `APP_ADMIN_PASS` tidak diatur.
