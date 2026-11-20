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
    $error = 'Email/NIM atau password salah.';   // pesan sama agar akun tidak bisa ditebak
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - Kas Kelas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container" style="max-width:420px">
  <div class="card shadow-sm mt-5">
    <div class="card-body p-4">
      <h1 class="h4 text-center mb-1">Kas Kelas</h1>
      <p class="text-center text-muted small mb-4">Pengelolaan Iuran dan Transparansi Kas</p>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Email / NIM</label>
          <input type="text" name="nim_email" class="form-control" required autofocus></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required></div>
        <button class="btn btn-primary w-100">Masuk</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
