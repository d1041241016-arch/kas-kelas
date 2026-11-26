<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$rows = pdo()->query("SELECT id, nama, nim_email, created_at FROM users WHERE role = 'anggota' ORDER BY nama")->fetchAll();
render_header('Data Anggota', 'Tambah, ubah, atau hapus anggota kelas.');
?>
<a href="form.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus"></i>Tambah Anggota</a>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
  <thead><tr><th>#</th><th>Nama</th><th>Email / NIM</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['nim_email']) ?></td><td><?= tgl($r['created_at']) ?></td>
    <td class="text-end">
      <a href="form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary">Ubah</a>
      <form method="post" action="hapus.php" class="d-inline" onsubmit="return confirm('Hapus anggota ini beserta seluruh data iurannya?')">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
    </td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="text-center text-muted">Belum ada anggota.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php render_footer();
