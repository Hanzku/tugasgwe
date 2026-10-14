# SIA SMK Malaka — Modul Pelajaran

Modul **Pelajaran** (CRUD) untuk kelompok 2, dibangun dengan PHP 8.1+ dan MySQL/MariaDB, tanpa framework. Entitas hanya menyimpan tiga atribut sesuai ERD: `KD_PELAJARAN`, `NAMA_PELAJARAN`, dan `BOBOT`.

## Struktur

| File | Fungsi |
|---|---|
| `pelajaran.php` | Daftar data (cari, urutkan, halaman, hapus) |
| `form_pelajaran.php` | Form tambah / edit terpisah dengan validasi |
| `detail_pelajaran.php` | Halaman detail satu pelajaran |
| `hapus_pelajaran.php` | Konfirmasi penghapusan |
| `proses_pelajaran.php` | Aksi simpan/edit/hapus (POST + CSRF) |
| `includes/` | Koneksi PDO, layout, dan validasi |
| `database/schema_pelajaran.sql` | Skema tabel dan data contoh DEMO |

## Menjalankan secara lokal

1. Pastikan PHP 8.1+ (ekstensi `pdo_mysql`) dan MySQL/MariaDB tersedia, misalnya lewat XAMPP.
2. Salin `config/database.example.php` menjadi `config/database.php`, lalu isi host, database, pengguna, dan kata sandi Anda.
3. Import `database/schema_pelajaran.sql` melalui phpMyAdmin atau klien `mysql`.
4. Jalankan di web server (contoh XAMPP): letakkan folder ini di `htdocs`, lalu buka `http://localhost/sia-pelajaran/pelajaran.php`.

Baris dengan kode `DEMO-*` adalah contoh pengembangan, bukan data resmi. Data nyata dapat dimasukkan melalui halaman Tambah Data atau dihapus dari daftar.

## Deploy ke Vercel dari GitHub

Aplikasi ini memakai PHP dan MySQL. `vercel.json` mengatur runtime PHP komunitas `vercel-php` dan mengarahkan halaman aplikasi ke serverless function; file CSS dan JavaScript tetap disajikan sebagai aset statis. Database MySQL lokal tidak ikut ter-deploy dan filesystem Vercel tidak cocok untuk menyimpan data aplikasi secara permanen, jadi siapkan database MySQL/MariaDB yang bisa diakses dari internet terlebih dahulu.

1. Upload seluruh isi folder proyek ini ke sebuah repository GitHub. Jangan upload `config/database.php`; file tersebut berisi kredensial lokal dan sudah diabaikan Git.
2. Buat database pada layanan MySQL/MariaDB Anda. Jalankan `database/schema_pelajaran.sql` jika pengguna database Anda diizinkan membuat database; jika penyedia sudah membuatkan database, pilih database tersebut lalu jalankan bagian `CREATE TABLE` dan `INSERT` dari file skema (lewati `CREATE DATABASE` dan `USE`).
3. Di Vercel, pilih **Add New → Project**, import repository GitHub, dan biarkan **Root Directory** menunjuk ke folder yang berisi `vercel.json`.
4. Tambahkan environment variables berikut di **Project Settings → Environment Variables** untuk Production (dan Preview bila diperlukan):

   | Nama | Nilai |
   |---|---|
   | `DB_HOST` | Host database dari penyedia |
   | `DB_PORT` | Port database, biasanya `3306` |
   | `DB_NAME` | Nama database yang dibuat |
   | `DB_USER` | Pengguna database |
   | `DB_PASSWORD` | Kata sandi database |

5. Deploy project. Setelah mengubah environment variables, lakukan redeploy agar nilainya digunakan.

Pastikan penyedia database mengizinkan koneksi dari aplikasi Vercel dan kredensial database tidak disimpan di repository. Runtime PHP Vercel dikonfigurasi melalui `vercel-php@0.9.0`; koneksi database tetap memakai PDO MySQL seperti saat dijalankan secara lokal.

## Keamanan dan keputusan desain

- Semua query memakai prepared statements PDO; pengurutan memakai whitelist kolom.
- Form mengirim POST dengan token CSRF; hapus meminta konfirmasi terpisah.
- Kode pelajaran adalah identitas tetap: hanya dapat diisi saat tambah data dan menjadi readonly saat edit, agar relasi `Mempelajari`/`Diampu` tidak rusak.
- Hapus ditolak dengan pesan jelas jika data masih dirujuk tabel lain (foreign key).
- Tidak ada atribut siswa (nama, foto, kelas) yang disalin ke modul ini; hanya tiga atribut Pelajaran.

## Pengujian

- Sintaks seluruh file PHP diperiksa dengan `php -l` (PHP 8.4).
- `tests/validation_test.php` berisi 27 pemeriksaan validasi, termasuk nilai batas bobot dan input tidak valid; jalankan dengan `php tests/validation_test.php`.
- Uji CRUD end-to-end: `.tools/smoke.py` menjalankan MariaDB portabel dan server PHP bawaan, lalu menguji tambah, cari, edit, hapus, penolakan duplikat, CSRF, dan penolakan foreign key.