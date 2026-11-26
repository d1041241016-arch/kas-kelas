<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$data = ['judul' => '', 'jumlah' => '', 'tanggal' => date('Y-m-d'), 'keterangan' => ''];
if ($id) {
    $s = pdo()->prepare('SELECT * FROM pengeluaran WHERE id = ?');
    $s->execute([$id]);
    $data = $s->fetch();
    if (!$data) { flash('danger', 'Data tidak ditemukan.'); redirect('/pengeluaran/index.php'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data = [
        'judul'      => trim($_POST['judul'] ?? ''),
        'jumlah'     => trim($_POST['jumlah'] ?? ''),
        'tanggal'    => trim($_POST['tanggal'] ?? ''),
        'keterangan' => trim($_POST['keterangan'] ?? ''),
    ];
    $d = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
    if ($data['judul'] === '' || panjang($data['judul']) > 150) $errors[] = 'Keperluan wajib diisi (maksimal 150 karakter).';
    if (!is_numeric($data['jumlah']) || (float)$data['jumlah'] <= 0) $errors[] = 'Jumlah harus berupa angka lebih dari 0.';
    if (!$d || $d->format('Y-m-d') !== $data['tanggal'])          $errors[] = 'Tanggal tidak valid.';

    if (!$errors) {
        if ($id) {
            pdo()->prepare('UPDATE pengeluaran SET judul = ?, jumlah = ?, tanggal = ?, keterangan = ? WHERE id = ?')
                 ->execute([$data['judul'], $data['jumlah'], $data['tanggal'], $data['keterangan'] ?: null, $id]);
        } else {
            pdo()->prepare('INSERT INTO pengeluaran (judul, jumlah, tanggal, keterangan, dibuat_oleh) VALUES (?, ?, ?, ?, ?)')
                 ->execute([$data['judul'], $data['jumlah'], $data['tanggal'], $data['keterangan'] ?: null, user_id()]);
        }
        flash('success', $id ? 'Pengeluaran diperbarui.' : 'Pengeluaran dicatat.');
        redirect('/pengeluaran/index.php');
    }
}
render_header($id ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran');
?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:480px">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
  <div class="mb-3"><label class="form-label">Keperluan</label><input name="judul" class="form-control" value="<?= e($data['judul']) ?>" required maxlength="150"></div>
  <div class="mb-3"><label class="form-label">Jumlah (Rp)</label><input type="number" name="jumlah" class="form-control" value="<?= e($data['jumlah']) ?>" min="1" step="1" required></div>
  <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= e($data['tanggal']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"><?= e($data['keterangan']) ?></textarea></div>
  <div><button class="btn btn-primary">Simpan</button> <a href="index.php" class="btn btn-link">Batal</a></div>
</form>
<?php render_footer();
