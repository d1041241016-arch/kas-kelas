<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
wajib_login();

// Hanya data milik sendiri: user_id dari session
$s = pdo()->prepare("SELECT p.nama_periode, p.nominal, p.jatuh_tempo, b.jumlah_bayar, b.tanggal_bayar, b.status
    FROM pembayaran_iuran b JOIN periode_iuran p ON p.id = b.periode_id
    WHERE b.user_id = ? ORDER BY p.jatuh_tempo DESC");
$s->execute([user_id()]);
$rows = $s->fetchAll();

render_header('Riwayat Iuran Saya', 'Status iuranmu pada setiap periode.');
?>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>Periode</th><th>Nominal</th><th>Jatuh Tempo</th><th>Dibayar</th><th>Tanggal Bayar</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td><td><?= tgl($r['jatuh_tempo']) ?></td>
    <td><?= rupiah($r['jumlah_bayar']) ?></td><td><?= tgl($r['tanggal_bayar']) ?></td>
    <td><span class="badge bg-<?= $r['status'] === 'lunas' ? 'success' : 'warning text-dark' ?>"><?= e($r['status']) ?></span></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="text-center text-muted">Belum ada data iuran.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php render_footer();
