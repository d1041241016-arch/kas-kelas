-- =====================================================================
-- Website Pengelolaan Iuran dan Transparansi Kas Kelas
-- Database : db_kas_bersama   (MySQL 5.7+ / MariaDB 10.3+)
-- Isi      : 1) struktur tabel  2) index  3) data awal  4) view laporan
--            5) kumpulan query yang dipakai website (referensi)
-- Cara pakai: phpMyAdmin > Import, atau  mysql -u root -p < kas_bersama.sql
--
-- AKUN CONTOH (semua password: password123)
--   Bendahara : bendahara@kaskelas.test
--   Anggota   : anggota01@kaskelas.test ... anggota15@kaskelas.test
-- Setelah deploy, GANTI password semua akun contoh ini.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_kas_bersama
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_kas_bersama;

SET FOREIGN_KEY_CHECKS = 0;
DROP VIEW  IF EXISTS v_laporan_kas;
DROP VIEW  IF EXISTS v_rekap_iuran_periode;
DROP VIEW  IF EXISTS v_saldo_kas;
DROP TABLE IF EXISTS pengeluaran;
DROP TABLE IF EXISTS pembayaran_iuran;
DROP TABLE IF EXISTS periode_iuran;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. STRUKTUR TABEL
-- ---------------------------------------------------------------------

-- users: akun bendahara dan anggota (akun anggota dibuat oleh bendahara)
CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  nim_email   VARCHAR(100) NOT NULL UNIQUE,      -- identitas login
  password    VARCHAR(255) NOT NULL,             -- hasil password_hash(), bukan teks biasa
  role        ENUM('bendahara','anggota') NOT NULL DEFAULT 'anggota',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- periode_iuran: satu baris = satu periode tagihan (mis. bulan)
CREATE TABLE periode_iuran (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nama_periode  VARCHAR(100) NOT NULL,
  nominal       DECIMAL(12,2) NOT NULL,
  jatuh_tempo   DATE NOT NULL,
  keterangan    TEXT NULL
) ENGINE=InnoDB;

-- pembayaran_iuran: satu baris = satu tagihan anggota pada satu periode
CREATE TABLE pembayaran_iuran (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NOT NULL,
  periode_id     INT NOT NULL,
  jumlah_bayar   DECIMAL(12,2) NOT NULL DEFAULT 0,
  tanggal_bayar  DATE NULL,
  status         ENUM('lunas','belum') NOT NULL DEFAULT 'belum',
  catatan        TEXT NULL,
  UNIQUE KEY uq_user_periode (user_id, periode_id),   -- 1 anggota 1 tagihan per periode
  KEY idx_bayar_status (status),
  KEY idx_bayar_tanggal (tanggal_bayar),
  CONSTRAINT fk_bayar_user    FOREIGN KEY (user_id)    REFERENCES users(id)         ON DELETE CASCADE,
  CONSTRAINT fk_bayar_periode FOREIGN KEY (periode_id) REFERENCES periode_iuran(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- pengeluaran: uang kas yang keluar
CREATE TABLE pengeluaran (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  judul        VARCHAR(150) NOT NULL,
  jumlah       DECIMAL(12,2) NOT NULL,
  tanggal      DATE NOT NULL,
  keterangan   TEXT NULL,
  dibuat_oleh  INT NOT NULL,
  KEY idx_keluar_tanggal (tanggal),
  CONSTRAINT fk_keluar_user FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. DATA AWAL
-- ---------------------------------------------------------------------

-- Akun (password semua akun: password123)
INSERT INTO users (id, nama, nim_email, password, role) VALUES
  (1, 'Bendahara Kelas', 'bendahara@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'bendahara'),
  (2, 'Rina Aprilia', 'anggota01@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (3, 'Budi Santoso', 'anggota02@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (4, 'Citra Dewi', 'anggota03@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (5, 'Dimas Pratama', 'anggota04@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (6, 'Eka Putri', 'anggota05@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (7, 'Fajar Nugroho', 'anggota06@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (8, 'Gita Maharani', 'anggota07@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (9, 'Hendra Wijaya', 'anggota08@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (10, 'Intan Permata', 'anggota09@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (11, 'Joko Susilo', 'anggota10@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (12, 'Kartika Sari', 'anggota11@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (13, 'Lukman Hakim', 'anggota12@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (14, 'Maya Anggraini', 'anggota13@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (15, 'Naufal Ramadhan', 'anggota14@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota'),
  (16, 'Olivia Puspita', 'anggota15@kaskelas.test', '$2y$10$TLwoKg8JrAaXvq/xqbeMTOdKRF02mm3cPwNf56eBLveAS7XrDxTsK', 'anggota');

-- Periode iuran
INSERT INTO periode_iuran (id, nama_periode, nominal, jatuh_tempo, keterangan) VALUES
  (1, 'Agustus 2026', 10000.00, '2026-08-31', 'Iuran kas bulan Agustus'),
  (2, 'September 2026', 10000.00, '2026-09-30', 'Iuran kas bulan September'),
  (3, 'Oktober 2026', 10000.00, '2026-10-31', 'Iuran kas bulan Oktober'),
  (4, 'November 2026', 10000.00, '2026-11-30', 'Iuran kas bulan November (berjalan)');

-- Pembayaran iuran: tagihan untuk setiap anggota di setiap periode
INSERT INTO pembayaran_iuran (user_id, periode_id, jumlah_bayar, tanggal_bayar, status, catatan) VALUES
  (2, 1, 10000.00, '2026-08-03', 'lunas', NULL),
  (3, 1, 10000.00, '2026-08-05', 'lunas', NULL),
  (4, 1, 10000.00, '2026-08-07', 'lunas', NULL),
  (5, 1, 10000.00, '2026-08-09', 'lunas', NULL),
  (6, 1, 10000.00, '2026-08-11', 'lunas', NULL),
  (7, 1, 10000.00, '2026-08-13', 'lunas', NULL),
  (8, 1, 10000.00, '2026-08-15', 'lunas', NULL),
  (9, 1, 10000.00, '2026-08-17', 'lunas', NULL),
  (10, 1, 10000.00, '2026-08-19', 'lunas', NULL),
  (11, 1, 10000.00, '2026-08-21', 'lunas', NULL),
  (12, 1, 10000.00, '2026-08-23', 'lunas', NULL),
  (13, 1, 10000.00, '2026-08-03', 'lunas', NULL),
  (14, 1, 10000.00, '2026-08-05', 'lunas', NULL),
  (15, 1, 10000.00, '2026-08-07', 'lunas', NULL),
  (16, 1, 10000.00, '2026-08-09', 'lunas', NULL),
  (2, 2, 10000.00, '2026-09-03', 'lunas', NULL),
  (3, 2, 10000.00, '2026-09-05', 'lunas', NULL),
  (4, 2, 10000.00, '2026-09-07', 'lunas', NULL),
  (5, 2, 10000.00, '2026-09-09', 'lunas', NULL),
  (6, 2, 10000.00, '2026-09-11', 'lunas', NULL),
  (7, 2, 10000.00, '2026-09-13', 'lunas', NULL),
  (8, 2, 10000.00, '2026-09-15', 'lunas', NULL),
  (9, 2, 10000.00, '2026-09-17', 'lunas', NULL),
  (10, 2, 10000.00, '2026-09-19', 'lunas', NULL),
  (11, 2, 10000.00, '2026-09-21', 'lunas', NULL),
  (12, 2, 10000.00, '2026-09-23', 'lunas', NULL),
  (13, 2, 10000.00, '2026-09-03', 'lunas', NULL),
  (14, 2, 10000.00, '2026-09-05', 'lunas', NULL),
  (15, 2, 0.00, NULL, 'belum', 'Belum membayar'),
  (16, 2, 0.00, NULL, 'belum', 'Belum membayar'),
  (2, 3, 10000.00, '2026-10-03', 'lunas', NULL),
  (3, 3, 10000.00, '2026-10-05', 'lunas', NULL),
  (4, 3, 10000.00, '2026-10-07', 'lunas', NULL),
  (5, 3, 10000.00, '2026-10-09', 'lunas', NULL),
  (6, 3, 10000.00, '2026-10-11', 'lunas', NULL),
  (7, 3, 10000.00, '2026-10-13', 'lunas', NULL),
  (8, 3, 10000.00, '2026-10-15', 'lunas', NULL),
  (9, 3, 10000.00, '2026-10-17', 'lunas', NULL),
  (10, 3, 10000.00, '2026-10-19', 'lunas', NULL),
  (11, 3, 10000.00, '2026-10-21', 'lunas', NULL),
  (12, 3, 10000.00, '2026-10-23', 'lunas', NULL),
  (13, 3, 10000.00, '2026-10-03', 'lunas', NULL),
  (14, 3, 0.00, NULL, 'belum', 'Belum membayar'),
  (15, 3, 0.00, NULL, 'belum', 'Belum membayar'),
  (16, 3, 0.00, NULL, 'belum', 'Belum membayar'),
  (2, 4, 10000.00, '2026-11-03', 'lunas', NULL),
  (3, 4, 10000.00, '2026-11-05', 'lunas', NULL),
  (4, 4, 10000.00, '2026-11-07', 'lunas', NULL),
  (5, 4, 10000.00, '2026-11-09', 'lunas', NULL),
  (6, 4, 10000.00, '2026-11-11', 'lunas', NULL),
  (7, 4, 10000.00, '2026-11-13', 'lunas', NULL),
  (8, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (9, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (10, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (11, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (12, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (13, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (14, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (15, 4, 0.00, NULL, 'belum', 'Belum membayar'),
  (16, 4, 0.00, NULL, 'belum', 'Belum membayar');

-- Pengeluaran kas (dicatat oleh bendahara, id user = 1)
INSERT INTO pengeluaran (judul, jumlah, tanggal, keterangan, dibuat_oleh) VALUES
  ('Beli spidol dan penghapus papan tulis', 35000.00, '2026-08-12', 'Perlengkapan kelas', 1),
  ('Fotokopi materi dan lembar tugas', 45000.00, '2026-09-03', 'Fotokopi bahan kuliah', 1),
  ('Konsumsi rapat kelas', 60000.00, '2026-09-20', 'Snack dan air minum rapat', 1),
  ('Kado dan kartu ucapan ulang tahun dosen', 100000.00, '2026-10-05', 'Patungan hadiah, selisih ditutup kas', 1),
  ('Isi ulang galon air kelas', 30000.00, '2026-10-18', 'Dua galon', 1),
  ('Cetak banner kegiatan kelas', 55000.00, '2026-11-02', 'Banner kegiatan akhir semester', 1);

-- ---------------------------------------------------------------------
-- 3. VIEW LAPORAN (dipakai dashboard dan halaman laporan)
-- ---------------------------------------------------------------------

-- Saldo kas = iuran lunas - pengeluaran
CREATE VIEW v_saldo_kas AS
SELECT
  (SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran_iuran WHERE status = 'lunas') AS total_pemasukan,
  (SELECT COALESCE(SUM(jumlah),0)       FROM pengeluaran)                              AS total_pengeluaran,
  (SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran_iuran WHERE status = 'lunas')
  - (SELECT COALESCE(SUM(jumlah),0)     FROM pengeluaran)                              AS saldo;

-- Rekap per periode: berapa yang sudah dan belum membayar
CREATE VIEW v_rekap_iuran_periode AS
SELECT
  p.id AS periode_id,
  p.nama_periode,
  p.nominal,
  p.jatuh_tempo,
  SUM(b.status = 'lunas') AS jumlah_lunas,
  SUM(b.status = 'belum') AS jumlah_belum,
  COALESCE(SUM(CASE WHEN b.status = 'lunas' THEN b.jumlah_bayar END),0) AS total_terkumpul
FROM periode_iuran p
LEFT JOIN pembayaran_iuran b ON b.periode_id = p.id
GROUP BY p.id, p.nama_periode, p.nominal, p.jatuh_tempo;

-- Laporan kas gabungan (pemasukan + pengeluaran) urut tanggal
CREATE VIEW v_laporan_kas AS
SELECT b.tanggal_bayar AS tanggal,
       'pemasukan' AS jenis,
       CONCAT('Iuran ', p.nama_periode, ' - ', u.nama) AS uraian,
       b.jumlah_bayar AS jumlah
FROM pembayaran_iuran b
JOIN users u ON u.id = b.user_id
JOIN periode_iuran p ON p.id = b.periode_id
WHERE b.status = 'lunas'
UNION ALL
SELECT tanggal, 'pengeluaran', judul, jumlah FROM pengeluaran;

-- ---------------------------------------------------------------------
-- 4. QUERY YANG DIPAKAI WEBSITE (referensi; tanda ? diisi lewat prepared statement)
-- ---------------------------------------------------------------------

-- [Login] ambil akun berdasarkan identitas, lalu cocokkan dengan password_verify() di PHP
--   SELECT id, nama, password, role FROM users WHERE nim_email = ?;

-- [Dashboard] saldo, total pemasukan, total pengeluaran
--   SELECT * FROM v_saldo_kas;

-- [Dashboard anggota] status iuran milik sendiri (user_id SELALU dari session)
--   SELECT p.nama_periode, p.nominal, p.jatuh_tempo, b.jumlah_bayar, b.tanggal_bayar, b.status
--   FROM pembayaran_iuran b JOIN periode_iuran p ON p.id = b.periode_id
--   WHERE b.user_id = ? ORDER BY p.jatuh_tempo DESC;

-- [Data anggota - bendahara]
--   SELECT id, nama, nim_email, role, created_at FROM users WHERE role = 'anggota' ORDER BY nama;
--   INSERT INTO users (nama, nim_email, password, role) VALUES (?, ?, ?, 'anggota');
--   UPDATE users SET nama = ?, nim_email = ? WHERE id = ? AND role = 'anggota';
--   UPDATE users SET password = ? WHERE id = ?;                    -- reset password
--   DELETE FROM users WHERE id = ? AND role = 'anggota';

-- [Periode iuran - bendahara]
--   SELECT * FROM v_rekap_iuran_periode ORDER BY jatuh_tempo DESC;
--   INSERT INTO periode_iuran (nama_periode, nominal, jatuh_tempo, keterangan) VALUES (?, ?, ?, ?);
--   UPDATE periode_iuran SET nama_periode = ?, nominal = ?, jatuh_tempo = ?, keterangan = ? WHERE id = ?;
--   DELETE FROM periode_iuran WHERE id = ?;

-- [Buat tagihan otomatis] setelah menambah periode baru, buat tagihan untuk semua anggota
--   INSERT INTO pembayaran_iuran (user_id, periode_id, jumlah_bayar, status)
--   SELECT id, ?, 0, 'belum' FROM users WHERE role = 'anggota';
--   -- ? = id periode yang baru dibuat (PHP: $pdo->lastInsertId())

-- [Pembayaran iuran - bendahara] daftar tagihan satu periode
--   SELECT b.id, u.nama, b.jumlah_bayar, b.tanggal_bayar, b.status
--   FROM pembayaran_iuran b JOIN users u ON u.id = b.user_id
--   WHERE b.periode_id = ? ORDER BY u.nama;
--   -- catat pembayaran = tandai lunas
--   UPDATE pembayaran_iuran SET jumlah_bayar = ?, tanggal_bayar = ?, status = 'lunas', catatan = ? WHERE id = ?;
--   -- batalkan pembayaran
--   UPDATE pembayaran_iuran SET jumlah_bayar = 0, tanggal_bayar = NULL, status = 'belum' WHERE id = ?;

-- [Pengeluaran - bendahara]
--   SELECT * FROM pengeluaran ORDER BY tanggal DESC;
--   INSERT INTO pengeluaran (judul, jumlah, tanggal, keterangan, dibuat_oleh) VALUES (?, ?, ?, ?, ?);
--   UPDATE pengeluaran SET judul = ?, jumlah = ?, tanggal = ?, keterangan = ? WHERE id = ?;
--   DELETE FROM pengeluaran WHERE id = ?;

-- [Laporan kas] semua transaksi, dengan filter tanggal
--   SELECT * FROM v_laporan_kas WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal;

-- [Laporan per bulan]
--   SELECT DATE_FORMAT(tanggal,'%Y-%m') AS bulan,
--          SUM(CASE WHEN jenis='pemasukan'  THEN jumlah ELSE 0 END) AS pemasukan,
--          SUM(CASE WHEN jenis='pengeluaran' THEN jumlah ELSE 0 END) AS pengeluaran
--   FROM v_laporan_kas GROUP BY bulan ORDER BY bulan;

-- [Anggota yang belum bayar pada periode tertentu]
--   SELECT u.nama FROM pembayaran_iuran b JOIN users u ON u.id = b.user_id
--   WHERE b.periode_id = ? AND b.status = 'belum' ORDER BY u.nama;

-- ---------------------------------------------------------------------
-- 5. CEK HASIL IMPORT (jalankan manual)
-- ---------------------------------------------------------------------
-- SELECT * FROM v_saldo_kas;              -- harus: pemasukan 460000, pengeluaran 325000, saldo 135000
-- SELECT COUNT(*) FROM users;             -- harus: 16 (1 bendahara + 15 anggota)
-- SELECT COUNT(*) FROM pembayaran_iuran;  -- harus: 60 (15 anggota x 4 periode)
-- SELECT * FROM v_rekap_iuran_periode;
