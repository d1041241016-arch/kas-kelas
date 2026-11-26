<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_bendahara();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$data = ['nama' => '', 'nim_email' => ''];
if ($id) {
    $s = pdo()->prepare("SELECT id, nama, nim_email FROM users WHERE id = ? AND role = 'anggota'");
    $s->execute([$id]);
    $data = $s->fetch();
    if (!$data) { flash('danger', 'Anggota tidak ditemukan.'); redirect('/anggota/index.php'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nama = trim($_POST['nama'] ?? '');
    $ident = trim($_POST['nim_email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $data = ['nama' => $nama, 'nim_email' => $ident];

    if ($nama === '' || panjang($nama) > 100)   $errors[] = 'Nama wajib diisi (maksimal 100 karakter).';
    if ($ident === '' || panjang($ident) > 100) $errors[] = 'Email/NIM wajib diisi (maksimal 100 karakter).';
    if (!$id && strlen($pass) < 6)                $errors[] = 'Password wajib diisi, minimal 6 karakter.';
    if ($id && $pass !== '' && strlen($pass) < 6) $errors[] = 'Password baru minimal 6 karakter.';

    if (!$errors) {
        $c = pdo()->prepare('SELECT COUNT(*) FROM users WHERE nim_email = ? AND id <> ?');
        $c->execute([$ident, $id]);
        if ($c->fetchColumn() > 0) $errors[] = 'Email/NIM sudah dipakai akun lain.';
    }

    if (!$errors) {
        $db = pdo();
        $db->beginTransaction();
        try {
            if ($id) {
                $db->prepare("UPDATE users SET nama = ?, nim_email = ? WHERE id = ? AND role = 'anggota'")->execute([$nama, $ident, $id]);
                if ($pass !== '') $db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            } else {
                $db->prepare("INSERT INTO users (nama, nim_email, password, role) VALUES (?, ?, ?, 'anggota')")
                   ->execute([$nama, $ident, password_hash($pass, PASSWORD_DEFAULT)]);
                $baru = (int)$db->lastInsertId();
                // tagihan untuk semua periode yang sudah ada
                $db->prepare("INSERT INTO pembayaran_iuran (user_id, periode_id, jumlah_bayar, status) SELECT ?, id, 0, 'belum' FROM periode_iuran")->execute([$baru]);
            }
            $db->commit();
        } catch (Throwable $ex) { $db->rollBack(); throw $ex; }
        flash('success', $id ? 'Data anggota diperbarui.' : 'Anggota ditambahkan.');
        redirect('/anggota/index.php');
    }
}
render_header($id ? 'Ubah Anggota' : 'Tambah Anggota');
?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:480px">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
  <div class="mb-3"><label class="form-label">Nama</label><input name="nama" class="form-control" value="<?= e($data['nama']) ?>" required maxlength="100"></div>
  <div class="mb-3"><label class="form-label">Email / NIM (untuk login)</label><input name="nim_email" class="form-control" value="<?= e($data['nim_email']) ?>" required maxlength="100"></div>
  <div class="mb-3"><label class="form-label">Password <?= $id ? '(kosongkan jika tidak diubah)' : '' ?></label>
    <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?> minlength="6"></div>
  <div><button class="btn btn-primary">Simpan</button> <a href="index.php" class="btn btn-link">Batal</a></div>
</form>
<?php render_footer();
