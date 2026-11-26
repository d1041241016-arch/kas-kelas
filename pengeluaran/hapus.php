<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_bendahara();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/pengeluaran/index.php');
csrf_check();

$s = pdo()->prepare('DELETE FROM pengeluaran WHERE id = ?');
$s->execute([(int)($_POST['id'] ?? 0)]);
flash($s->rowCount() ? 'success' : 'danger', $s->rowCount() ? 'Pengeluaran dihapus.' : 'Data tidak ditemukan.');
redirect('/pengeluaran/index.php');
