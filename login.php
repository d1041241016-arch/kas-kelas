<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (isset($_SESSION['user_id'])) redirect('/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ident = trim($_POST['nim_email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $s = pdo()->prepare('SELECT id, nama, password, role FROM users WHERE nim_email = ?');
    $s->execute([$ident]);
    $u = $s->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        session_regenerate_id(true);          // cegah session fixation
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['nama']    = $u['nama'];
        $_SESSION['role']    = $u['role'];
        redirect('/dashboard.php');
    }
    $error = 'Email/NIM atau password salah. Coba lagi.';   // pesan sama agar akun tidak bisa ditebak
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - Kas Kelas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-bg"><div class="container"><div class="login-card">
  <div class="card shadow">
    <div class="card-body p-4 p-md-5">
      <div class="login-logo"><i class="bi bi-wallet2"></i></div>
      <h1 class="h4 text-center fw-bold mb-1">Kas Kelas</h1>
      <p class="text-center text-muted mb-4">Masuk untuk melihat iuran dan kas kelas</p>
      <?php if ($error): ?><div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i><?= e($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Email / NIM</label>
          <div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="nim_email" class="form-control" required autofocus></div></div>
        <div class="mb-4"><label class="form-label">Password</label>
          <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="pw" class="form-control" required>
            <button class="btn btn-outline-secondary" type="button" id="lihat" title="Tampilkan / sembunyikan password"><i class="bi bi-eye"></i></button></div></div>
        <button class="btn btn-primary w-100 py-2">Masuk</button>
      </form>
    </div>
  </div>
</div></div></div>
<script>
document.getElementById('lihat').addEventListener('click', function () {
  var f = document.getElementById('pw'), i = this.querySelector('i');
  var tampil = f.type === 'password';
  f.type = tampil ? 'text' : 'password';
  i.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
</body>
</html>
