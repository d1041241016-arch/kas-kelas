<?php
function render_header(string $judul): void {
    $nama = $_SESSION['nama'] ?? '';
    $aktif = basename(dirname($_SERVER['SCRIPT_NAME']));
    $file  = basename($_SERVER['SCRIPT_NAME']);
    $nav = function (string $href, string $label, bool $on) {
        return '<li class="nav-item"><a class="nav-link' . ($on ? ' active' : '') . '" href="' . BASE_URL . $href . '">' . e($label) . '</a></li>';
    };
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> - Kas Kelas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard.php">Kas Kelas</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <?= $nav('/dashboard.php', 'Dashboard', $file === 'dashboard.php') ?>
        <?php if (is_bendahara()): ?>
          <?= $nav('/anggota/index.php', 'Data Anggota', $aktif === 'anggota') ?>
          <?= $nav('/periode/index.php', 'Periode Iuran', $aktif === 'periode') ?>
          <?= $nav('/pembayaran/index.php', 'Pembayaran', $aktif === 'pembayaran') ?>
          <?= $nav('/pengeluaran/index.php', 'Pengeluaran', $aktif === 'pengeluaran') ?>
        <?php else: ?>
          <?= $nav('/riwayat.php', 'Riwayat Iuran Saya', $file === 'riwayat.php') ?>
        <?php endif; ?>
        <?= $nav('/laporan/index.php', 'Laporan Kas', $aktif === 'laporan') ?>
      </ul>
      <span class="navbar-text me-3 small"><?= e($nama) ?> (<?= e($_SESSION['role']) ?>)</span>
      <a class="btn btn-outline-light btn-sm me-2" href="<?= BASE_URL ?>/password.php">Ubah Password</a>
      <a class="btn btn-light btn-sm" href="<?= BASE_URL ?>/logout.php">Keluar</a>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container py-4">
<?php if ($f = flash_get()): ?>
  <div class="alert alert-<?= e($f[0]) ?> alert-dismissible fade show no-print"><?= e($f[1]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<?php
}

function render_footer(): void { ?>
</main>
<footer class="text-center text-muted small py-3 no-print">Website Pengelolaan Iuran dan Transparansi Kas Kelas</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php }
