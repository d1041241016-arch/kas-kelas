<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
wajib_login();

$k = saldo_kas();
render_header('Dashboard');
?>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card card-stat border-primary"><div class="card-body">
    <div class="text-muted small">Saldo Kas</div><div class="nilai text-primary"><?= rupiah($k['saldo']) ?></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-body">
    <div class="text-muted small">Total Pemasukan</div><div class="nilai text-success"><?= rupiah($k['total_pemasukan']) ?></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-body">
    <div class="text-muted small">Total Pengeluaran</div><div class="nilai text-danger"><?= rupiah($k['total_pengeluaran']) ?></div></div></div></div>
</div>

<?php if (is_bendahara()):
    $rekap = pdo()->query('SELECT * FROM v_rekap_iuran_periode ORDER BY jatuh_tempo DESC')->fetchAll(); ?>
  <div class="card mb-4"><div class="card-header">Rekap Iuran per Periode</div>
  <div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Periode</th><th>Nominal</th><th>Lunas</th><th>Belum</th><th>Terkumpul</th></tr></thead><tbody>
    <?php foreach ($rekap as $r): ?>
      <tr><td><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td>
      <td><?= (int)$r['jumlah_lunas'] ?></td><td><?= (int)$r['jumlah_belum'] ?></td><td><?= rupiah($r['total_terkumpul']) ?></td></tr>
    <?php endforeach; if (!$rekap): ?><tr><td colspan="5" class="text-center text-muted">Belum ada periode.</td></tr><?php endif; ?>
    </tbody></table></div></div>
<?php else:
    // user_id SELALU dari session, bukan dari URL
    $s = pdo()->prepare("SELECT p.nama_periode, p.nominal, p.jatuh_tempo, b.status
        FROM pembayaran_iuran b JOIN periode_iuran p ON p.id = b.periode_id
        WHERE b.user_id = ? ORDER BY p.jatuh_tempo DESC LIMIT 5");
    $s->execute([user_id()]);
    $iuran = $s->fetchAll();
    $belum = array_filter($iuran, fn($r) => $r['status'] === 'belum'); ?>
  <?php if ($belum): ?><div class="alert alert-warning">Kamu masih punya <?= count($belum) ?> iuran yang belum lunas.</div>
  <?php else: ?><div class="alert alert-success">Semua iuranmu sudah lunas. Terima kasih!</div><?php endif; ?>
  <div class="card"><div class="card-header">Iuran Terbaru Saya</div>
  <div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Periode</th><th>Nominal</th><th>Jatuh Tempo</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($iuran as $r): ?>
      <tr><td><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td><td><?= tgl($r['jatuh_tempo']) ?></td>
      <td><span class="badge bg-<?= $r['status'] === 'lunas' ? 'success' : 'warning text-dark' ?>"><?= e($r['status']) ?></span></td></tr>
    <?php endforeach; ?></tbody></table></div></div>
<?php endif;
render_footer();
