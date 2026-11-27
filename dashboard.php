<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
wajib_login();

$k = saldo_kas();
render_header('Halo, ' . ($_SESSION['nama'] ?? ''), 'Ringkasan kas kelas saat ini.');
?>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
    <div class="ikon ikon-biru"><i class="bi bi-wallet2"></i></div>
    <div><div class="label">Saldo kas sekarang</div><div class="nilai text-primary"><?= rupiah($k['saldo']) ?></div></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
    <div class="ikon ikon-hijau"><i class="bi bi-arrow-down-circle"></i></div>
    <div><div class="label">Total uang masuk</div><div class="nilai text-success"><?= rupiah($k['total_pemasukan']) ?></div></div></div></div></div>
  <div class="col-md-4"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
    <div class="ikon ikon-merah"><i class="bi bi-arrow-up-circle"></i></div>
    <div><div class="label">Total uang keluar</div><div class="nilai text-danger"><?= rupiah($k['total_pengeluaran']) ?></div></div></div></div></div>
</div>

<?php if (is_bendahara()):
    $rekap = pdo()->query('SELECT * FROM (' . SQL_REKAP . ') AS r ORDER BY jatuh_tempo DESC')->fetchAll(); ?>

  <div class="card mb-4 no-print"><div class="card-body">
    <div class="fw-semibold mb-2">Mau melakukan apa?</div>
    <div class="aksi-cepat d-flex flex-wrap gap-2">
      <a href="<?= BASE_URL ?>/pembayaran/index.php" class="btn btn-success"><i class="bi bi-cash-coin"></i>Catat pembayaran iuran</a>
      <a href="<?= BASE_URL ?>/pengeluaran/form.php" class="btn btn-danger"><i class="bi bi-receipt"></i>Catat pengeluaran</a>
      <a href="<?= BASE_URL ?>/anggota/form.php" class="btn btn-outline-primary"><i class="bi bi-person-plus"></i>Tambah anggota</a>
      <a href="<?= BASE_URL ?>/periode/form.php" class="btn btn-outline-primary"><i class="bi bi-calendar-plus"></i>Buat periode iuran</a>
      <a href="<?= BASE_URL ?>/laporan/index.php" class="btn btn-outline-secondary"><i class="bi bi-bar-chart-line"></i>Lihat laporan</a>
    </div></div></div>

  <div class="card"><div class="card-header">Iuran per periode</div>
  <div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>Periode</th><th>Iuran</th><th style="min-width:180px">Yang sudah bayar</th><th>Terkumpul</th><th class="text-end"></th></tr></thead><tbody>
    <?php foreach ($rekap as $r):
        $total = (int)$r['jumlah_lunas'] + (int)$r['jumlah_belum'];
        $pct = $total ? round($r['jumlah_lunas'] / $total * 100) : 0; ?>
      <tr><td class="fw-semibold"><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td>
      <td><div class="progress mb-1"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
          <small class="text-muted"><?= (int)$r['jumlah_lunas'] ?> dari <?= $total ?> anggota (<?= $pct ?>%)</small></td>
      <td><?= rupiah($r['total_terkumpul']) ?></td>
      <td class="text-end"><a class="btn btn-sm btn-outline-success" href="<?= BASE_URL ?>/pembayaran/index.php?periode_id=<?= (int)$r['periode_id'] ?>">Catat</a></td></tr>
    <?php endforeach; if (!$rekap): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada periode iuran. Klik &ldquo;Buat periode iuran&rdquo; di atas.</td></tr><?php endif; ?>
    </tbody></table></div></div>

<?php else:
    // user_id SELALU dari session, bukan dari URL
    $c = pdo()->prepare("SELECT COALESCE(SUM(status='lunas'),0) AS lunas, COALESCE(SUM(status='belum'),0) AS belum FROM pembayaran_iuran WHERE user_id = ?");
    $c->execute([user_id()]);
    $hit = $c->fetch();
    $s = pdo()->prepare("SELECT p.nama_periode, p.nominal, p.jatuh_tempo, b.status
        FROM pembayaran_iuran b JOIN periode_iuran p ON p.id = b.periode_id
        WHERE b.user_id = ? ORDER BY p.jatuh_tempo DESC LIMIT 5");
    $s->execute([user_id()]);
    $iuran = $s->fetchAll(); ?>

  <?php if ((int)$hit['belum'] > 0): ?>
    <div class="status-besar status-warn mb-4"><i class="bi bi-exclamation-circle ikon-besar"></i>
      <div><div class="fw-bold fs-5">Ada <?= (int)$hit['belum'] ?> iuran yang belum lunas</div>
      <div>Silakan bayar ke bendahara. Status akan berubah setelah bendahara mencatatnya.</div></div></div>
  <?php else: ?>
    <div class="status-besar status-ok mb-4"><i class="bi bi-check-circle ikon-besar"></i>
      <div><div class="fw-bold fs-5">Semua iuranmu sudah lunas</div><div>Terima kasih sudah membayar tepat waktu!</div></div></div>
  <?php endif; ?>

  <div class="card"><div class="card-header d-flex justify-content-between align-items-center">Iuran terbaru saya
      <a href="<?= BASE_URL ?>/riwayat.php" class="btn btn-sm btn-outline-primary">Lihat semua</a></div>
  <div class="table-responsive"><table class="table table-hover">
    <thead><tr><th>Periode</th><th>Iuran</th><th>Jatuh tempo</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($iuran as $r): ?>
      <tr><td class="fw-semibold"><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td><td><?= tgl($r['jatuh_tempo']) ?></td>
      <td><span class="badge bg-<?= $r['status'] === 'lunas' ? 'success' : 'warning text-dark' ?>"><?= $r['status'] === 'lunas' ? 'Lunas' : 'Belum bayar' ?></span></td></tr>
    <?php endforeach; if (!$iuran): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada data iuran.</td></tr><?php endif; ?>
    </tbody></table></div></div>
<?php endif;
render_footer();
