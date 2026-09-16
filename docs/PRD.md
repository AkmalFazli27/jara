# PRD — JARA (An Advanced To Do List)

| Atribut | Isi |
|---|---|
| Nama produk | JARA — An Advanced To Do List |
| Jenis | Aplikasi web pengelolaan tugas pribadi & tim |
| Stack | Laravel 13 (PHP >= 8.3), MySQL 8.4 (Aiven, SSL wajib) |
| Dokumen terkait | [README.md](../README.md), [ERD.md](../ERD.md) |
| Status | Disetujui tim — menjadi acuan SRS F-01 s.d F-18 |

---

## 1. Ringkasan Eksekutif

JARA adalah aplikasi web untuk mengelola tugas pribadi maupun tim dalam satu tempat.
Pengguna dapat membuat dan mengelompokkan tugas ke dalam beberapa daftar (project),
menetapkan prioritas dan tenggat waktu, serta menandai tugas sebagai selesai.
Pemilik daftar dapat menambahkan pengguna lain agar daftar dikerjakan bersama dan
memantau progress pengerjaannya. Admin bertanggung jawab menambah dan menghapus
akun pengguna dalam sistem.

Masalah yang diselesaikan:

- Tugas tersebar (catatan, chat, ingatan) sehingga mudah terlupa dan sulit dipantau.
- Kerja kelompok tidak transparan: tidak jelas siapa mengerjakan apa dan sejauh mana progressnya.
- Tidak ada pemisahan hak akses yang jelas antara pemilik daftar, anggota, dan admin sistem.

---

## 2. Tujuan & Kriteria Keberhasilan

| ID | Tujuan | Kriteria keberhasilan |
|---|---|---|
| T-01 | Pengguna dapat mengelola tugas pribadi lintas daftar | Pengguna bisa CRUD daftar & tugas, atur prioritas dan deadline |
| T-02 | Kolaborasi tim berjalan transparan | Pemilik bisa mengundang anggota; anggota bisa melihat & mengerjakan daftar bersama |
| T-03 | Progress terpantau | Setiap daftar menampilkan % tugas selesai dan sisa tugas |
| T-04 | Administrasi pengguna terkendali | Hanya admin yang bisa menambah/menghapus akun; login membedakan role |
| T-05 | Selesai tepat waktu praktikum | Seluruh SRS F-01 s.d F-18 terimplementasi dan bisa didemo |

---

## 3. Persona & Hak Akses

### 3.1 Persona

- **Pengguna (User)** — individu atau anggota tim yang mengelola dan mengerjakan tugas.
- **Pemilik Daftar (Owner)** — pengguna yang membuat/membawahi sebuah daftar;igado hak penuh atas daftar tersebut (subset dari User, berbasis kepemilikan + `list_members.role = owner`).
- **Admin** — pengelola sistem; mengatur akun pengguna, tidak ikut mengelola isi daftar.

### 3.2 Matriks hak akses

| Kemampuan | User | Owner daftar | Admin |
|---|---|---|---|
| Login / logout / kelola profil sendiri | Ya | Ya | Ya |
| Buat daftar baru | Ya | Ya | Tidak |
| Lihat daftar milik sendiri + yang di-share | Ya | Ya | Tidak |
| Edit / hapus / arsip daftar | Hanya milik sendiri | Ya | Tidak |
| Undang & hapus anggota daftar | Tidak | Ya | Tidak |
| Keluar dari daftar yang diikuti | Ya | Ya | Tidak |
| CRUD tugas dalam daftar yang bisa diakses | Ya | Ya | Tidak |
| Lihat progress daftar yang bisa diakses | Ya | Ya | Tidak |
| Lihat / tambah / hapus akun pengguna | Tidak | Tidak | Ya |

---

## 4. Ruang Lingkup

### 4.1 In-scope (SRS F-01 s.d F-18)

1. **Autentikasi & profil** — login multi-role (user & admin lewat satu pintu login),
   logout, proteksi halaman, lihat & edit profil sendiri (F-01 s.d F-03).
2. **Administrasi pengguna** — admin melihat daftar pengguna + pencarian, menambah
   akun, menghapus/menonaktifkan akun (F-04 s.d F-06).
3. **Daftar/project** — buat, lihat (pribadi + shared) + pencarian, edit, hapus/arsip (F-07 s.d F-10).
4. **Kolaborasi** — undang/tambah anggota, lihat & hapus anggota, keluar dari daftar,
   pembedaan hak owner vs member (F-11, F-12).
5. **Tugas** — buat, edit, hapus, detail, tandai selesai/belum selesai (F-13 s.d F-15).
6. **Prioritas & deadline** — atur prioritas Rendah/Sedang/Tinggi; atur tenggat waktu
   + tanda overdue + filter hari ini/terlambat (F-16, F-17).
7. **Tampilan & progress** — filter/sort/cari/pengelompokan tugas + progress bar
   dan statistik per daftar (F-18).

### 4.2 Out-of-scope (disepakati tidak dikerjakan)

- Registrasi akun mandiri (akun murni dibuatkan admin).
- Notifikasi deadline via email/push (F-17 hanya filter & tanda overdue di aplikasi).
- Lampiran file pada tugas.
- Penugasan tugas ke orang tertentu (`assigned_to`).
- Aplikasi mobile / API publik.
- Integrasi kalender pihak ketiga.

---

## 5. User Stories & Acceptance Criteria

Konvensi: `US-XX` dipetakan 1:1 ke `F-XX` pada pembagian SRS.

### Programmer 1 — Autentikasi & Admin

**US-01 (F-01) — Login multi-role**
Sebagai pengguna/admin, saya ingin login dengan email & password agar masuk sesuai peran saya.
- AC-01: Kredensial benar + role user → diarahkan ke dashboard user.
- AC-02: Kredensial benar + role admin → diarahkan ke dashboard admin.
- AC-03: Kredensial salah → pesan error, tidak masuk, password tidak terbaca.

**US-02 (F-02) — Logout & proteksi halaman**
Sebagai pengguna yang sudah login, saya ingin logout dan halaman penting terlindungi.
- AC-01: Logout mengakhiri sesi; tombol back tidak membuka halaman privat.
- AC-02: Akses URL privat tanpa login → dialihkan ke halaman login.

**US-03 (F-03) — Profil sendiri**
Sebagai pengguna, saya ingin melihat & mengubah profil saya.
- AC-01: Menampilkan nama & email; nama bisa diubah dan tersimpan.
- AC-02: Validasi (mis. email unik & format benar) menampilkan pesan yang jelas.

**US-04 (F-04) — Admin melihat daftar pengguna**
Sebagai admin, saya ingin melihat & mencari semua akun pengguna.
- AC-01: Tabel menampilkan nama, email, role; pencarian nama/email berfungsi.
- AC-02: Halaman ini tidak bisa diakses role user (403/redirect).

**US-05 (F-05) — Admin menambah akun**
Sebagai admin, saya ingin membuatkan akun pengguna baru.
- AC-01: Email duplikat ditolak dengan pesan jelas; password tersimpan ter-hash.
- AC-02: Akun baru langsung bisa login sesuai role yang ditetapkan.

**US-06 (F-06) — Admin menghapus akun**
Sebagai admin, saya ingin menghapus/menonaktifkan akun pengguna.
- AC-01: Ada konfirmasi sebelum hapus; akun terhapus tidak bisa login lagi.
- AC-02: Admin tidak dapat menghapus akunnya sendiri.

### Programmer 2 — Daftar/Project & Kolaborasi

**US-07 (F-07) — Buat daftar baru**
Sebagai pengguna, saya ingin membuat daftar/project dengan nama & deskripsi.
- AC-01: Nama wajib diisi; daftar tersimpan dan muncul di daftar saya.
- AC-02: Pembuat otomatis menjadi owner (tercatat di `list_members`).

**US-08 (F-08) — Lihat daftar**
Sebagai pengguna, saya ingin melihat daftar milik saya dan yang di-share ke saya.
- AC-01: Dua kelompok terlihat jelas (milik saya vs shared); pencarian nama berfungsi.
- AC-02: Daftar yang tidak terkait dengan saya tidak terlihat.

**US-09 (F-09) — Edit daftar**
Sebagai owner, saya ingin mengubah nama/deskripsi daftar.
- AC-01: Perubahan tersimpan dan terlihat anggota.
- AC-02: Member (bukan owner) tidak bisa mengedit (tombol disembunyikan + ditolak server).

**US-10 (F-10) — Hapus/arsip daftar**
Sebagai owner, saya ingin menghapus atau mengarsipkan daftar yang sudah tidak dipakai.
- AC-01: Ada konfirmasi; daftar terarsip hilang dari tampilan aktif tapi datanya utuh.
- AC-02: Daftar terhapus tidak bisa diakses lagi oleh siapa pun.

**US-11 (F-11) — Undang anggota**
Sebagai owner, saya ingin menambahkan pengguna lain ke daftar saya.
- AC-01: Pengguna yang diundang langsung melihat daftar tersebut sebagai shared.
- AC-02: Pengguna yang sudah menjadi anggota tidak bisa diundang dua kali.

**US-12 (F-12) — Kelola anggota**
Sebagai owner/anggota, saya ingin mengatur keanggotaan daftar.
- AC-01: Owner melihat daftar anggota dan bisa menghapus anggota.
- AC-02: Anggota bisa keluar dari daftar; owner keluar = kepemilikan harus dialihkan/dihapus eksplisit.
- AC-03: Hak owner vs member ditegakkan di server, bukan hanya di tampilan.

### Programmer 3 — Tugas, Prioritas & Progress

**US-13 (F-13) — Buat tugas**
Sebagai pengguna, saya ingin menambah tugas (judul, deskripsi) ke sebuah daftar.
- AC-01: Judul wajib; tugas muncul di daftar yang benar.
- AC-02: Tidak bisa membuat tugas di daftar yang tidak bisa saya akses.

**US-14 (F-14) — Edit & hapus tugas**
Sebagai pengguna, saya ingin mengubah & menghapus tugas saya.
- AC-01: Perubahan tersimpan; hapus memakai konfirmasi.

**US-15 (F-15) — Detail & status selesai**
Sebagai pengguna, saya ingin melihat detail tugas dan menandainya selesai/belum.
- AC-01: Toggle selesai mengisi/mengosongkan `completed_at` dan memperbarui progress daftar.

**US-16 (F-16) — Prioritas**
Sebagai pengguna, saya ingin mengatur prioritas (Rendah/Sedang/Tinggi).
- AC-01: Prioritas tersimpan, bisa diubah, dan bisa dipakai mengurutkan tugas.

**US-17 (F-17) — Tenggat waktu**
Sebagai pengguna, saya ingin mengatur deadline dan melihat tugas yang overdue.
- AC-01: Tugas lewat deadline bertanda overdue; filter hari ini/terlambat berfungsi.
- AC-02: Format tanggal tidak valid ditolak dengan pesan jelas.

**US-18 (F-18) — Filter & progress**
Sebagai pengguna, saya ingin memfilter/mencari tugas dan melihat progress daftar.
- AC-01: Filter status/prioritas, sort deadline/prioritas, dan pencarian judul berfungsi kombinasi.
- AC-02: Progress bar menampilkan % selesai yang konsisten dengan data tugas.

---

## 6. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan |
|---|---|
| Keamanan | Password di-hash (bcrypt); session-based auth; otorisasi per role dicek di server; koneksi DB wajib SSL (CA Aiven); proteksi CSRF bawaan Laravel aktif |
| Performa | Daftar + filter tugas (< ratusan baris per daftar) termuat < 2 detik di jaringan kampus; index pada `(list_id, is_completed)`, `deadline`, `priority` (lihat ERD.md) |
| Ketersediaan | Mengikuti SLA Kubernetes/Aiven yang dipakai; tidak ada target HA khusus |
| Kompatibilitas | Browser modern (Chrome/Edge/Firefox terbaru); PHP >= 8.3; MySQL 8.x |
| Keterpeliharaan | Perubahan skema hanya via migration; kode mengikuti struktur bawaan Laravel; password & kredensial tidak boleh di-commit (`.env` gitignored) |
| Kegunaan | Bahasa antarmuka Indonesia; alur utama (buat daftar → tambah tugas → tandai selesai) bisa dipakai tanpa panduan |

---

## 7. Asumsi & Ketergantungan

1. Akun pengguna murni dibuatkan admin — tidak ada registrasi mandiri.
2. Tiap programmer memakai database lokal sendiri (`jara`) dengan skema dari migration git.
3. Tiga programmer bekerja paralel per modul (P1: auth+admin, P2: daftar+kolaborasi, P3: tugas+progress) dengan kontrak skema di `ERD.md`.
4. MySQL lokal (Laragon) tersedia saat pengembangan & demo.
5. Satu pintu login untuk user & admin; perbedaan perilaku diatur dari `users.role`.

---

## 8. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Konflik migration antar programmer | Skema DB rusak/inkonsisten | Skema hanya diubah via migration bernomor modul; satu orang pertama `migrate`, lainnya pull lalu `migrate` |
| MySQL Laragon belum jalan saat demo | Demo gagal total | Pastikan Laragon Start All + siapkan seed/screenshot |
| Otorisasi hanya di frontend | Member bisa memodifikasi data owner | Setiap aksi sensitif divalidasi ulang di server (policy/middleware) |
| Password bocor via chat/repo | Akun DB disalahgunakan | Password hanya di `.env` lokal; ganti password root Laragon setelah praktikum |
| Scope meluber (notif, file, mobile) | Tidak selesai tepat waktu | Out-of-scope di §4.2 dikunci; permintaan baru masuk backlog, bukan sprint ini |

---

*Akhir PRD. Perubahan kebutuhan setelah ini diputuskan bersama tim dan dicatat sebagai revisi SRS/ERD.*
