<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$rows = pdo()->query('SELECT id, judul, jumlah, tanggal, keterangan FROM pengeluaran ORDER BY tanggal DESC, id DESC')->fetchAll();
$total = array_sum(array_column($rows, 'jumlah'));
render_header('Pengeluaran Kas', 'Catat uang kas yang dipakai untuk keperluan kelas.');
?>
<a href="form.php" class="btn btn-primary mb-3"><i class="bi bi-plus-lg"></i>Tambah Pengeluaran</a>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>Tanggal</th><th>Keperluan</th><th>Keterangan</th><th class="text-end">Jumlah</th><th class="text-end">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['judul']) ?></td><td><?= e($r['keterangan']) ?></td><td class="text-end"><?= rupiah($r['jumlah']) ?></td>
    <td class="text-end">
      <a href="form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
      <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini?')">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="text-center text-muted">Belum ada pengeluaran.</td></tr><?php endif; ?>
  </tbody>
  <tfoot><tr class="fw-bold"><td colspan="3">Total</td><td class="text-end"><?= rupiah($total) ?></td><td></td></tr></tfoot></table></div></div>
<?php render_footer();
