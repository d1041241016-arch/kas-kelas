# Website Pengelolaan Iuran dan Transparansi Kas Kelas

Proyek UAS Pemrograman Web (individu) oleh **Fidela (D1041241016)**, Teknik Informatika, Universitas Tanjungpura.

- Repository: https://github.com/d1041241016-arch/kas-kelas
- Laporan KP (Google Drive): https://docs.google.com/document/d/1daO-vkBV2-O-9pJM48VIvlUIfRfg-8P-/edit?usp=sharing&ouid=110774974150406332138&rtpof=true&sd=true

## Progres Milestone

### Tahap 1
- [x] Menetapkan judul dan ruang lingkup: kas kelas dengan dua peran (bendahara dan anggota)
- [x] Merancang basis data: ERD, kamus data, dan skema SQL (4 tabel, 3 view) di `database/kas_bersama.sql`
- [x] Menyusun struktur proyek PHP native: koneksi PDO, session, autentikasi (login/logout), dan pemeriksaan peran
- [x] Menyiapkan lingkungan lokal Laragon dan mengimpor basis data

### Tahap 2
- [x] Membuat fitur CRUD anggota, periode iuran, pembayaran, dan pengeluaran dengan pembatasan akses per peran
- [x] Membuat dashboard, laporan kas (filter dan ekspor CSV), riwayat iuran anggota, dan ubah password
- [x] Deploy website dan basis data ke hosting InfinityFree: http://kaskelas-fidela.lovestoblog.com

### Tahap akhir
- [ ] (isi saat dikerjakan)

## Alur Git
- `master`: hanya berisi README.md (progres milestone)
- Kode dikerjakan di branch terpisah (mis. `feat/database`, `feat/auth`) dan tidak di-push ke `master`
- Format commit: `type: deskripsi` (feat, fix, chore, docs, refactor)

---

PHP native (PDO) + MySQL/MariaDB + Bootstrap 5 (CDN).

## Menjalankan di komputer (Laragon)
1. Salin folder `kas-kelas` ke `C:\laragon\www\`.
2. Klik Start All di Laragon (Apache dan MySQL).
3. Buka phpMyAdmin (http://localhost/phpmyadmin) > Import > pilih `database/kas_bersama.sql`.
4. Salin `config/database.example.php` menjadi `config/database.php`, lalu isi `DB_PASS` sesuai password MySQL di komputermu (file `database.php` sengaja tidak masuk Git).
5. Buka http://localhost/kas-kelas

## Akun contoh (password semua: `password123`)
- Bendahara: `bendahara@kaskelas.test`
- Anggota: `anggota01@kaskelas.test` ... `anggota15@kaskelas.test`

## Deploy ke hosting
1. Upload isi folder lewat File Manager/FTP ke `public_html` (atau subfolder).
2. Buat database + user di panel hosting, lalu import `database/kas_bersama.sql`
   (hapus baris `CREATE DATABASE` dan `USE db_kas_bersama;` di bagian atas file jika hosting
   tidak mengizinkan, lalu pilih database-nya terlebih dulu di phpMyAdmin).
3. Edit `config/database.php`:
   - `BASE_URL`: `''` jika di root domain, `'/nama-subfolder'` jika di subfolder
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` sesuai data dari hosting
4. Ganti password semua akun contoh, atau hapus akun contoh dan buat akun asli lewat menu Data Anggota.
5. Aktifkan HTTPS jika tersedia.

## Struktur
```
config/database.php   pengaturan koneksi dan BASE_URL
includes/auth.php     koneksi PDO, session, CSRF, wajib_login(), wajib_bendahara()
includes/layout.php   header (navbar sesuai peran) dan footer
login.php logout.php dashboard.php riwayat.php password.php
anggota/  periode/  pembayaran/  pengeluaran/   (CRUD, khusus bendahara)
laporan/index.php     laporan kas (anggota: lihat; bendahara: cetak + ekspor CSV)
database/kas_bersama.sql   struktur + data contoh + view
```

## Hak akses
| Halaman | Bendahara | Anggota |
|---|---|---|
| Dashboard, Laporan (lihat), Ubah password | ya | ya |
| Riwayat iuran sendiri | - | ya |
| Data anggota, Periode, Pembayaran, Pengeluaran (CRUD) | ya | ditolak (403) |
| Cetak / ekspor CSV laporan | ya | ditolak (403) |

## Keamanan yang diterapkan
- Password disimpan dengan `password_hash()`, dicek dengan `password_verify()`.
- `session_regenerate_id(true)` setelah login; cookie session `HttpOnly` + `SameSite`.
- Semua query memakai prepared statement (anti SQL injection).
- Semua output memakai `htmlspecialchars` (anti XSS).
- Token CSRF di setiap form POST; penghapusan hanya lewat POST.
- Pemeriksaan peran dilakukan di server pada setiap halaman, bukan hanya menyembunyikan tombol.
- Riwayat anggota selalu memakai `user_id` dari session (tidak bisa mengintip data anggota lain lewat URL).
