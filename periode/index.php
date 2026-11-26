<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$rows = pdo()->query('SELECT * FROM v_rekap_iuran_periode ORDER BY jatuh_tempo DESC')->fetchAll();
render_header('Periode Iuran', 'Atur besar iuran dan batas waktu bayar untuk setiap periode.');
?>
<a href="form.php" class="btn btn-primary mb-3"><i class="bi bi-calendar-plus"></i>Tambah Periode</a>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>Periode</th><th>Nominal</th><th>Jatuh Tempo</th><th>Lunas</th><th>Belum</th><th>Terkumpul</th><th class="text-end">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['nama_periode']) ?></td><td><?= rupiah($r['nominal']) ?></td><td><?= tgl($r['jatuh_tempo']) ?></td>
    <td><?= (int)$r['jumlah_lunas'] ?></td><td><?= (int)$r['jumlah_belum'] ?></td><td><?= rupiah($r['total_terkumpul']) ?></td>
    <td class="text-end">
      <a href="../pembayaran/index.php?periode_id=<?= (int)$r['periode_id'] ?>" class="btn btn-sm btn-outline-success">Pembayaran</a>
      <a href="form.php?id=<?= (int)$r['periode_id'] ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
      <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus periode ini beserta semua tagihannya?')">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['periode_id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
    </td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-muted">Belum ada periode.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php render_footer();
