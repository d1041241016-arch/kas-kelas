<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$data = ['nama_periode' => '', 'nominal' => '', 'jatuh_tempo' => '', 'keterangan' => ''];
if ($id) {
    $s = pdo()->prepare('SELECT * FROM periode_iuran WHERE id = ?');
    $s->execute([$id]);
    $data = $s->fetch();
    if (!$data) { flash('danger', 'Periode tidak ditemukan.'); redirect('/periode/index.php'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data = [
        'nama_periode' => trim($_POST['nama_periode'] ?? ''),
        'nominal'      => trim($_POST['nominal'] ?? ''),
        'jatuh_tempo'  => trim($_POST['jatuh_tempo'] ?? ''),
        'keterangan'   => trim($_POST['keterangan'] ?? ''),
    ];
    $d = DateTime::createFromFormat('Y-m-d', $data['jatuh_tempo']);

    if ($data['nama_periode'] === '' || panjang($data['nama_periode']) > 100) $errors[] = 'Nama periode wajib diisi (maksimal 100 karakter).';
    if (!is_numeric($data['nominal']) || (float)$data['nominal'] <= 0)          $errors[] = 'Nominal harus berupa angka lebih dari 0.';
    if (!$d || $d->format('Y-m-d') !== $data['jatuh_tempo'])                    $errors[] = 'Tanggal jatuh tempo tidak valid.';

    if (!$errors) {
        $db = pdo();
        $db->beginTransaction();
        try {
            if ($id) {
                $db->prepare('UPDATE periode_iuran SET nama_periode = ?, nominal = ?, jatuh_tempo = ?, keterangan = ? WHERE id = ?')
                   ->execute([$data['nama_periode'], $data['nominal'], $data['jatuh_tempo'], $data['keterangan'] ?: null, $id]);
            } else {
                $db->prepare('INSERT INTO periode_iuran (nama_periode, nominal, jatuh_tempo, keterangan) VALUES (?, ?, ?, ?)')
                   ->execute([$data['nama_periode'], $data['nominal'], $data['jatuh_tempo'], $data['keterangan'] ?: null]);
                if (!empty($_POST['buat_tagihan'])) {
                    $db->prepare("INSERT INTO pembayaran_iuran (user_id, periode_id, jumlah_bayar, status) SELECT id, ?, 0, 'belum' FROM users WHERE role = 'anggota'")
                       ->execute([(int)$db->lastInsertId()]);
                }
            }
            $db->commit();
        } catch (Throwable $ex) { $db->rollBack(); throw $ex; }
        flash('success', $id ? 'Periode diperbarui.' : 'Periode ditambahkan.');
        redirect('/periode/index.php');
    }
}
render_header($id ? 'Ubah Periode Iuran' : 'Tambah Periode Iuran');
?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:480px">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
  <div class="mb-3"><label class="form-label">Nama periode</label><input name="nama_periode" class="form-control" value="<?= e($data['nama_periode']) ?>" placeholder="Contoh: Desember 2026" required maxlength="100"></div>
  <div class="mb-3"><label class="form-label">Nominal iuran per anggota (Rp)</label><input type="number" name="nominal" class="form-control" value="<?= e($data['nominal']) ?>" min="1" step="1" required></div>
  <div class="mb-3"><label class="form-label">Jatuh tempo</label><input type="date" name="jatuh_tempo" class="form-control" value="<?= e($data['jatuh_tempo']) ?>" required></div>
  <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"><?= e($data['keterangan']) ?></textarea></div>
  <?php if (!$id): ?><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="buat_tagihan" id="bt" value="1" checked>
    <label class="form-check-label" for="bt">Buat tagihan otomatis untuk semua anggota</label></div><?php endif; ?>
  <div><button class="btn btn-primary">Simpan</button> <a href="index.php" class="btn btn-link">Batal</a></div>
</form>
<?php render_footer();
