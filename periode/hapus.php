<?php
require_once __DIR__ . '/../includes/auth.php';
wajib_bendahara();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/periode/index.php');
csrf_check();

$s = pdo()->prepare('DELETE FROM periode_iuran WHERE id = ?');
$s->execute([(int)($_POST['id'] ?? 0)]);
flash($s->rowCount() ? 'success' : 'danger', $s->rowCount() ? 'Periode dihapus.' : 'Periode tidak ditemukan.');
redirect('/periode/index.php');
