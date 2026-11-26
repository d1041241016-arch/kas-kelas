<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
wajib_login();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lama = $_POST['lama'] ?? ''; $baru = $_POST['baru'] ?? ''; $ulang = $_POST['ulang'] ?? '';
    $s = pdo()->prepare('SELECT password FROM users WHERE id = ?');
    $s->execute([user_id()]);
    $hash = $s->fetchColumn();

    if (!password_verify($lama, $hash)) $errors[] = 'Password lama salah.';
    if (strlen($baru) < 6)              $errors[] = 'Password baru minimal 6 karakter.';
    if ($baru !== $ulang)               $errors[] = 'Konfirmasi password tidak sama.';

    if (!$errors) {
        pdo()->prepare('UPDATE users SET password = ? WHERE id = ?')
             ->execute([password_hash($baru, PASSWORD_DEFAULT), user_id()]);
        flash('success', 'Password berhasil diubah.');
        redirect('/dashboard.php');
    }
}
render_header('Ubah Password', 'Gunakan password yang mudah kamu ingat tapi sulit ditebak.');
?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:420px">
  <?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Password lama</label><input type="password" name="lama" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Password baru</label><input type="password" name="baru" class="form-control" required minlength="6"></div>
  <div class="mb-3"><label class="form-label">Ulangi password baru</label><input type="password" name="ulang" class="form-control" required></div>
  <button class="btn btn-primary">Simpan</button>
</form>
<?php render_footer();
