<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

/** Koneksi PDO (prepared statement aktif, error jadi exception). */
function pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/* ---------- Helper tampilan ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
/** Panjang teks; aman walau ekstensi mbstring tidak aktif. */
function panjang(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }
function rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function tgl($d): string { return $d ? date('d/m/Y', strtotime($d)) : '-'; }
function redirect(string $path): void { header('Location: ' . BASE_URL . $path); exit; }

function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function flash_get(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('Sesi formulir tidak valid. Kembali dan muat ulang halaman.'); }
}

/* ---------- Autentikasi & access control (dicek di SERVER) ---------- */
function user_id(): int { return (int)($_SESSION['user_id'] ?? 0); }
function is_bendahara(): bool { return ($_SESSION['role'] ?? '') === 'bendahara'; }

function wajib_login(): void {
    if (!isset($_SESSION['user_id'])) redirect('/login.php');
}
function wajib_bendahara(): void {
    wajib_login();
    if (!is_bendahara()) {
        http_response_code(403);
        exit('<h3>403 - Akses ditolak</h3><p>Halaman ini hanya untuk bendahara.</p><a href="' . BASE_URL . '/dashboard.php">Kembali</a>');
    }
}


/* ---------- Pengganti VIEW ----------
   Hosting gratis menolak CREATE VIEW, jadi query yang sama dipakai sebagai subquery. */
const SQL_SALDO = "SELECT
  (SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran_iuran WHERE status = 'lunas') AS total_pemasukan,
  (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran) AS total_pengeluaran,
  (SELECT COALESCE(SUM(jumlah_bayar),0) FROM pembayaran_iuran WHERE status = 'lunas')
  - (SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran) AS saldo";

const SQL_REKAP = "SELECT p.id AS periode_id, p.nama_periode, p.nominal, p.jatuh_tempo,
  SUM(b.status = 'lunas') AS jumlah_lunas, SUM(b.status = 'belum') AS jumlah_belum,
  COALESCE(SUM(CASE WHEN b.status = 'lunas' THEN b.jumlah_bayar END),0) AS total_terkumpul
  FROM periode_iuran p LEFT JOIN pembayaran_iuran b ON b.periode_id = p.id
  GROUP BY p.id, p.nama_periode, p.nominal, p.jatuh_tempo";

const SQL_LAPORAN = "SELECT b.tanggal_bayar AS tanggal, 'pemasukan' AS jenis,
  CONCAT('Iuran ', p.nama_periode, ' - ', u.nama) AS uraian, b.jumlah_bayar AS jumlah
  FROM pembayaran_iuran b JOIN users u ON u.id = b.user_id JOIN periode_iuran p ON p.id = b.periode_id
  WHERE b.status = 'lunas'
  UNION ALL
  SELECT tanggal, 'pengeluaran', judul, jumlah FROM pengeluaran";

/* ---------- Data bersama ---------- */
function saldo_kas(): array {
    return pdo()->query('SELECT total_pemasukan, total_pengeluaran, saldo FROM (' . SQL_SALDO . ') AS s')->fetch();
}
