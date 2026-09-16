# JARA — An Advanced To Do List

Aplikasi web modern untuk mengelola tugas pribadi maupun kolaborasi tim secara efektif dan aman. Sistem ini memungkinkan pengguna membuat dan mengelompokkan tugas ke dalam proyek, menetapkan prioritas dan tenggat waktu (*deadline*), memantau keterlambatan (*overdue alert*), serta menandai tugas sebagai selesai dengan visualisasi progres interaktif. Pemilik daftar (*Owner*) dapat mengundang pengguna lain (*Members*) untuk berkolaborasi bersama dalam satu ruang kerja.

Aplikasi dibangun menggunakan **Laravel 13 (PHP >= 8.3/8.5)** dengan penyimpanan data terpusat pada **MySQL 8.4 (Aiven Cloud via SSL Connection)**.

## User Story

Sebagai pengguna (individu maupun tim), saya ingin mengelola, mengelompokkan, dan memantau tugas dalam daftar proyek dengan prioritas dan tenggat waktu yang jelas, serta dapat berkolaborasi bersama pengguna lain secara aman tanpa risiko kehilangan integritas data atau kebocoran hak akses.

[Repository / Demo](https://github.com/AkmalFazli27/jara)

## Pembagian Tugas & Tanggung Jawab Programmer

* **Akmal Fazli Riyadi (Programmer 1):** Bertanggung jawab atas F-01 s/d F-06 (Autentikasi Multi-Role, Manajemen Profil, dan Administrasi Akun Pengguna oleh Admin).
* **Dehar Zaidan Dzaki Amirullah (Programmer 2):** Bertanggung jawab atas F-07 s/d F-12 (Pembuatan & Penghapusan Proyek secara Atomik, Kepemilikan Otomatis, Otorisasi 403 Forbidden, dan Kolaborasi Anggota).
* **Devano Trestanto (Programmer 3):** Bertanggung jawab atas F-13 s/d F-18 (Manajemen Item Tugas, Tingkat Prioritas, Tenggat Waktu & Overdue, Filter/Sorting, serta Statistik & Progress Bar).

## Daftar SRS

| Kode | Deskripsi | Acceptance Criteria |
|:---|:---|:---|
| **F-01** | Login multi-role untuk Pengguna dan Administrator. | - Form login menerima input email dan password yang valid<br>- Pengguna dengan role `admin` diarahkan ke dashboard administrator<br>- Pengguna dengan role `user` diarahkan ke dashboard proyek pengguna<br>- Kredensial salah menampilkan pesan error validasi tanpa membocorkan info sistem |
| **F-02** | Logout dan proteksi rute privat melalui middleware auth. | - Tombol logout menghancurkan sesi aktif dan token auth<br>- Rute privat tidak dapat diakses sebelum login (redirect otomatis ke login)<br>- Mencegah akses halaman privat via browser back-button setelah logout |
| **F-03** | Lihat dan edit data profil pengguna. | - Halaman profil menampilkan nama, email, dan tanggal bergabung<br>- Pengguna dapat memperbarui nama dan password lama ke password baru<br>- Validasi email unik tetap terjaga saat update profil |
| **F-04** | Admin melihat daftar seluruh pengguna terdaftar di sistem. | - Tabel menampilkan ID, Nama, Email, Role (Admin/User), dan Status Akun<br>- Tersedia fitur pencarian dan paginasi data pengguna<br>- Hanya dapat diakses oleh pengguna dengan role `admin` |
| **F-05** | Admin menambahkan akun pengguna baru secara manual. | - Form tambah akun memvalidasi format email, nama, dan password default<br>- Password dienkripsi menggunakan bcrypt sebelum disimpan ke database<br>- Akun baru langsung aktif dan dapat digunakan untuk login |
| **F-06** | Admin menghapus atau menonaktifkan akun pengguna. | - Tersedia tombol toggle status (Active/Inactive) dan tombol hapus akun<br>- Akun yang berstatus nonaktif ditolak saat mencoba login<br>- Admin tidak dapat menghapus akun miliknya sendiri (self-delete protection) |
| **F-07** | Pembuatan daftar proyek baru secara atomik dan penetapan pemilik otomatis (*Auto-Owner*). | - Input nama proyek tervalidasi (wajib diisi, maksimal 150 karakter)<br>- Kreator proyek secara otomatis tercatat sebagai `role = 'owner'` pada tabel `project_members`<br>- Pembuatan proyek dan penetapan owner dieksekusi dalam satu transaksi atomik (`DB::transaction`)<br>- Jika salah satu langkah gagal, seluruh operasi dibatalkan (*rollback*) |
| **F-08** | Melihat daftar proyek pribadi, proyek bersama (*Shared*), dan fitur pencarian proyek. | - Halaman menampilkan tab *Proyek Pribadi* dan *Proyek Bersama Saya*<br>- Tersedia input pencarian real-time/filter berdasarkan nama proyek<br>- Proyek menampilkan ringkasan jumlah tugas dan jumlah anggota tim |
| **F-09** | Mengubah nama, deskripsi, dan tema warna daftar proyek. | - Hanya pengguna dengan peran `OWNER` yang dapat membuka dan menyimpan form edit proyek<br>- Validasi nama proyek tidak boleh kosong<br>- Perubahan langsung terrefleksi pada halaman detail proyek |
| **F-10** | Menghapus daftar proyek beserta seluruh tugas dan keanggotaan secara atomik dengan proteksi otorisasi. | - Tombol hapus hanya dapat diakses oleh `OWNER` proyek<br>- Permintaan hapus dari pengguna non-owner ditolak langsung dengan status **HTTP 403 Forbidden**<br>- Penghapusan mengeksekusi `DB::transaction` untuk menghapus seluruh `tasks`, `project_members`, dan entitas `projects`<br>- Jika terjadi error koneksi/query di tengah proses, seluruh data di-*rollback* utuh (tidak ada *orphan data*) |
| **F-11** | Mengundang atau menambahkan anggota baru ke dalam daftar proyek. | - Form menerima alamat email pengguna yang sudah terdaftar di sistem<br>- Menolak penambahan jika email tidak terdaftar atau sudah menjadi anggota proyek<br>- Record baru tersimpan di tabel `project_members` dengan `role = 'member'` |
| **F-12** | Manajemen keanggotaan proyek (lihat anggota, hapus anggota, keluar proyek, dan kontrol role). | - Modal/halaman menampilkan daftar seluruh kolaborator beserta rolenya<br>- `OWNER` berhak menghapus anggota dari proyek<br>- Anggota biasa (`MEMBER`) dapat memilih opsi keluar mandiri (*leave project*)<br>- `OWNER` tidak dapat keluar dari proyek miliknya tanpa mengalihkan kepemilikan |
| **F-13** | Membuat item tugas baru di dalam daftar proyek dengan proteksi SQL Injection. | - Form input tugas memvalidasi judul tugas (wajib), deskripsi (opsional), prioritas, dan tanggal tenggat<br>- Seluruh data diproses menggunakan *Prepared Statements* (parameterized query binding via Eloquent)<br>- Tugas baru otomatis terhubung ke `project_id` aktif dengan status default `todo` |
| **F-14** | Mengedit rincian tugas dan menghapus item tugas individu. | - Anggota proyek dapat memperbarui judul, deskripsi, prioritas, dan tenggat waktu tugas<br>- Anggota dapat menghapus tugas yang sudah tidak relevan dengan dialog konfirmasi<br>- Operasi hapus hanya menghapus baris tugas yang bersangkutan di tabel `tasks` |
| **F-15** | Melihat detail tugas dan menandai status selesai / belum selesai (*Toggle Done*). | - Klik pada checkbox/tombol status mengubah status tugas (`todo` / `in_progress` / `done`)<br>- Menandai `done` otomatis mencatat timestamp `completed_at` saat itu juga<br>- Menandai belum selesai mengosongkan kembali kolom `completed_at` |
| **F-16** | Mengatur tingkat prioritas tugas (*Low*, *Medium*, *High*, *Urgent*). | - Setiap tugas memiliki badge warna prioritas yang jelas (Hijau, Biru, Kuning, Merah)<br>- Pengguna dapat mengubah prioritas tugas secara cepat<br>- Opsi prioritas divalidasi ketat terhadap enum: `low`, `medium`, `high`, `urgent` |
| **F-17** | Menetapkan tenggat waktu (*deadline*) dan deteksi visual tugas terlambat (*Overdue*). | - Input tanggal batas akhir pengerjaan tugas (*due date*)<br>- Sistem otomatis membandingkan tanggal hari ini dengan `due_date`<br>- Jika tanggal telah lewat dan status belum `done`, sistem menampilkan label peringatan **OVERDUE** berwarna merah |
| **F-18** | Filter, sorting, pencarian tugas, serta komponen progress bar dan statistik penyelesaian. | - Filter tugas berdasarkan status (`todo`, `in_progress`, `done`) dan prioritas<br>- Pengurutan tugas berdasarkan tenggat waktu terdekat atau tanggal dibuat<br>- Progress bar menampilkan persentase penyelesaian: `(Tugas Selesai / Total Tugas) * 100%`<br>- Ringkasan angka statistik (total tugas, belum selesai, selesai, terlambat) |

## Menjalankan Proyek

Pastikan komputer Anda telah terinstal **PHP >= 8.3**, **Composer**, **Node.js & NPM**, serta memiliki akses koneksi internet untuk basis data **MySQL 8.4 Aiven SSL**.

```bash
# 1. Clone repositori dan masuk ke direktori proyek
git clone https://github.com/AkmalFazli27/jara.git
cd jara

# 2. Instalasi dependensi backend dan frontend
composer install
npm install

# 3. Buat file konfigurasi lingkungan (.env)
cp .env.example .env          # Linux / macOS / Git Bash
Copy-Item .env.example .env   # Windows PowerShell

# 4. Generate application key Laravel
php artisan key:generate

# 5. Konfigurasikan file .env (isi kredensial Aiven Anda & pastikan path SSL aktif)
# DB_HOST=<host_aiven>
# DB_PORT=<port_aiven>
# DB_USERNAME=<username_anda>
# DB_PASSWORD=<password_anda>
# MYSQL_ATTR_SSL_CA="storage/certs/aiven-ca.pem"

# 6. Sinkronisasi skema basis data bersama
php artisan migrate
php artisan db:show

# 7. Jalankan aplikasi (pada 2 tab terminal terpisah)
php artisan serve             # Terminal 1: Laravel Server (http://localhost:8000)
npm run dev                   # Terminal 2: Vite Asset Compiler
```

## Struktur Folder

```
jara/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controller logika Proyek, Tugas, dan Auth
│   │   ├── Middleware/        # Middleware autentikasi dan role check
│   │   └── Requests/          # Validasi form input (Anti-SQLi & Sanitasi)
│   ├── Models/                # Model Eloquent (User, Project, Task, ProjectMember)
│   └── Policies/              # Otorisasi hak akses (ProjectPolicy - Gate 403)
├── config/                    # Konfigurasi sistem dan koneksi database
├── database/
│   ├── migrations/            # File migration skema basis data MySQL
│   └── seeders/               # Seeder akun demo admin & user
├── public/                    # Entry point web (index.php) dan asset publik
├── resources/
│   ├── css/                   # Styling kustom & konfigurasi Tailwind CSS
│   ├── js/                    # Skrip JavaScript interaktif (Alpine.js)
│   └── views/                 # Template antarmuka Blade
│       ├── auth/              # Halaman login, register, dan profil
│       ├── projects/          # Tampilan daftar proyek dan kolaborator
│       └── tasks/             # Komponen checklist tugas, filter, & modal
├── routes/
│   └── web.php                # Definisi seluruh rute aplikasi terproteksi
├── storage/
│   └── certs/
│       └── aiven-ca.pem       # Sertifikat SSL untuk koneksi database Aiven
├── .env.example               # Template variabel lingkungan
├── .gitignore                 # Daftar file yang diabaikan oleh Git
├── ERD.md                     # Kontrak skema relasi basis data
└── README.md                  # Dokumentasi resmi proyek
```

Note:
## Akun Demo (untuk asisten praktikum)

Tidak ada halaman registrasi mandiri — akun hanya dibuat oleh admin
(F-05). Untuk login pertama setelah clone, gunakan salah satu akun
yang dibuat oleh `database/seeders/DatabaseSeeder.php`:

| Role  | Email              | Password    |
| ----- | ------------------ | ----------- |
| Admin | `admin@jara.local` | `Admin123!` |
| User  | `user@jara.local`  | `User123!`  |
| User  | `test@example.com` | `password`  |

> Catatan: kredensial di atas hanya untuk pengembangan lokal.
> Ganti password setelah login via halaman `/profile` dan jangan
> pakai kredensial ini di production.
