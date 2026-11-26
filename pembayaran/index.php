<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$db = pdo();
$periodeList = $db->query('SELECT id, nama_periode FROM periode_iuran ORDER BY jatuh_tempo DESC')->fetchAll();
$pid = (int)($_GET['periode_id'] ?? $_POST['periode_id'] ?? ($periodeList[0]['id'] ?? 0));

$s = $db->prepare('SELECT * FROM periode_iuran WHERE id = ?');
$s->execute([$pid]);
$periode = $s->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $periode) {
    csrf_check();
    $bid = (int)($_POST['bayar_id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    // pastikan tagihan memang milik periode yang sedang dibuka
    $c = $db->prepare('SELECT id FROM pembayaran_iuran WHERE id = ? AND periode_id = ?');
    $c->execute([$bid, $pid]);
    if (!$c->fetch()) { flash('danger', 'Tagihan tidak ditemukan.'); redirect("/pembayaran/index.php?periode_id=$pid"); }

    if ($aksi === 'bayar') {
        $tanggal = trim($_POST['tanggal_bayar'] ?? '');
        $d = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$d || $d->format('Y-m-d') !== $tanggal) {
            flash('danger', 'Tanggal bayar tidak valid.');
        } else {
            // iuran dicatat sebesar nominal periode (pembayaran penuh)
            $db->prepare("UPDATE pembayaran_iuran SET jumlah_bayar = ?, tanggal_bayar = ?, status = 'lunas', catatan = ? WHERE id = ?")
               ->execute([$periode['nominal'], $tanggal, trim($_POST['catatan'] ?? '') ?: null, $bid]);
            flash('success', 'Pembayaran dicatat.');
        }
    } elseif ($aksi === 'batal') {
        $db->prepare("UPDATE pembayaran_iuran SET jumlah_bayar = 0, tanggal_bayar = NULL, status = 'belum' WHERE id = ?")->execute([$bid]);
        flash('success', 'Pembayaran dibatalkan.');
    }
    redirect("/pembayaran/index.php?periode_id=$pid");
}

$rows = [];
if ($periode) {
    $q = $db->prepare('SELECT b.id, u.nama, b.jumlah_bayar, b.tanggal_bayar, b.status, b.catatan
        FROM pembayaran_iuran b JOIN users u ON u.id = b.user_id WHERE b.periode_id = ? ORDER BY u.nama');
    $q->execute([$pid]);
    $rows = $q->fetchAll();
}
render_header('Pembayaran Iuran', 'Pilih periode, lalu klik Lunas pada anggota yang sudah membayar.');
?>
<?php if (!$periodeList): ?>
  <div class="alert alert-info">Belum ada periode iuran. <a href="../periode/form.php">Buat periode dulu</a>.</div>
<?php else: ?>
<form method="get" class="row g-2 mb-3 align-items-end">
  <div class="col-auto"><label class="form-label mb-0 small">Periode</label>
    <select name="periode_id" class="form-select" onchange="this.form.submit()">
      <?php foreach ($periodeList as $p): ?><option value="<?= (int)$p['id'] ?>" <?= $p['id'] == $pid ? 'selected' : '' ?>><?= e($p['nama_periode']) ?></option><?php endforeach; ?>
    </select></div>
  <?php if ($periode): ?><div class="col-auto text-muted small">Nominal: <strong><?= rupiah($periode['nominal']) ?></strong> &middot; Jatuh tempo: <?= tgl($periode['jatuh_tempo']) ?></div><?php endif; ?>
</form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>Nama</th><th>Status</th><th>Tanggal Bayar</th><th>Catatan</th><th class="text-end">Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['nama']) ?></td>
    <td><span class="badge bg-<?= $r['status'] === 'lunas' ? 'success' : 'warning text-dark' ?>"><?= e($r['status']) ?></span></td>
    <td><?= tgl($r['tanggal_bayar']) ?></td><td><?= e($r['catatan']) ?></td>
    <td class="text-end">
      <?php if ($r['status'] === 'belum'): ?>
        <form method="post" class="d-inline-flex gap-1">
          <?= csrf_field() ?><input type="hidden" name="periode_id" value="<?= $pid ?>"><input type="hidden" name="bayar_id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="aksi" value="bayar">
          <input type="date" name="tanggal_bayar" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
          <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan" maxlength="100">
          <button class="btn btn-sm btn-success">Lunas</button></form>
      <?php else: ?>
        <form method="post" class="d-inline" onsubmit="return confirm('Batalkan pembayaran ini?')">
          <?= csrf_field() ?><input type="hidden" name="periode_id" value="<?= $pid ?>"><input type="hidden" name="bayar_id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="aksi" value="batal">
          <button class="btn btn-sm btn-outline-secondary">Batalkan</button></form>
      <?php endif; ?>
    </td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="text-center text-muted">Belum ada tagihan pada periode ini.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php endif;
render_footer();
