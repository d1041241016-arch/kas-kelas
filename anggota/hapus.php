<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_bendahara();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/anggota/index.php');
csrf_check();

// hanya akun berperan anggota yang boleh dihapus (bendahara tidak bisa terhapus)
$s = pdo()->prepare("DELETE FROM users WHERE id = ? AND role = 'anggota'");
$s->execute([(int)($_POST['id'] ?? 0)]);
flash($s->rowCount() ? 'success' : 'danger', $s->rowCount() ? 'Anggota dihapus.' : 'Anggota tidak ditemukan.');
redirect('/anggota/index.php');
