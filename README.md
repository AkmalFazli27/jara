# JARA — An Advanced To Do List

Aplikasi web untuk mengelola tugas pribadi atau tim. Pengguna dapat membuat,
mengelompokkan, dan mengatur tugas ke dalam beberapa daftar/project, menetapkan
prioritas dan tenggat waktu, serta menandai tugas sebagai selesai. Pemilik daftar
dapat menambahkan pengguna lain untuk dikerjakan bersama dan memantau progress.
Admin mengelola akun pengguna dalam sistem.

- Framework: **Laravel 13** (PHP >= 8.3) — teruji di PHP 8.5
- Database: **MySQL 8.x lokal (Laragon)** — database `jara`
- Struktur database: lihat **[ERD.md](./ERD.md)** (wajib dibaca semua programmer)

## Pembagian SRS

Programmer 1 - F-01 Login multi-role, F-02 Logout + proteksi halaman, F-03 Lihat & Edit Profil, F-04 Admin Lihat Daftar Pengguna, F-05 Admin Tambah Akun, F-06 Admin Hapus/Nonaktifkan Akun
Programmer 2 - F-07 Buat Daftar Project Baru, F-08 Lihat Daftar Pribadi + Shared + Pencarian, F-09 Edit Daftar, F-10 Hapus/Arsip Daftar, F-11 Undang/Tambah Anggota, F-12 Kelola Anggota (lihat/hapus/keluar/owner vs member)
Programmer 3 - F-13 Buat Tugas Dalam Daftar, F-14 Edit & Hapus Tugas, F-15 Detail + Tandai Selesai/Belum, F-16 Atur Prioritas, F-17 Atur Deadline + Overdue, F-18 Filter/Sort/Cari/Pengelompokan + Progress Bar & Statistik

## Cara Jalan (per programmer)

```powershell
# 1. Clone lalu masuk folder project
# 2. Install dependency
composer install
npm install

# 3. Copy .env.example menjadi .env, sesuaikan DB_PASSWORD
#    dengan password root Laragon masing-masing. Jangan commit password.
Copy-Item .env.example .env
php artisan key:generate

# 4. Buat database jara di Laragon (HeidiSQL):
#    CREATE DATABASE IF NOT EXISTS `jara` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 5. Sinkron skema database lokal
php artisan migrate:fresh
php artisan db:show

# 6. Jalankan aplikasi
php artisan serve
npm run dev
```

## Aturan Main Tim

1. **ERD.md adalah kontrak.** Perubahan skema hanya lewat file migration di git,
   dilarang ALTER manual di database.
2. Penamaan migration mengikuti modul masing-masing (lihat ERD.md).
3. File `.env` tidak boleh di-commit (sudah ada di `.gitignore`).
