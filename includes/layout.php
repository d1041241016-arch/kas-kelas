<?php
function render_header(string $judul, string $sub = ''): void {
    $nama  = $_SESSION['nama'] ?? '';
    $peran = $_SESSION['role'] ?? '';
    $aktif = basename(dirname($_SERVER['SCRIPT_NAME']));
    $file  = basename($_SERVER['SCRIPT_NAME']);
    $nav = function (string $href, string $label, string $ikon, bool $on) {
        return '<li class="nav-item"><a class="nav-link' . ($on ? ' active' : '') . '" href="' . BASE_URL . $href . '"><i class="bi ' . $ikon . '"></i>' . e($label) . '</a></li>';
    };
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> - Kas Kelas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-kk no-print">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-wallet2 me-1"></i> Kas Kelas</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto mt-2 mt-lg-0">
        <?= $nav('/dashboard.php', 'Beranda', 'bi-house-door', $file === 'dashboard.php') ?>
        <?php if (is_bendahara()): ?>
          <?= $nav('/anggota/index.php', 'Anggota', 'bi-people', $aktif === 'anggota') ?>
          <?= $nav('/periode/index.php', 'Periode', 'bi-calendar3', $aktif === 'periode') ?>
          <?= $nav('/pembayaran/index.php', 'Pembayaran', 'bi-cash-coin', $aktif === 'pembayaran') ?>
          <?= $nav('/pengeluaran/index.php', 'Pengeluaran', 'bi-receipt', $aktif === 'pengeluaran') ?>
        <?php else: ?>
          <?= $nav('/riwayat.php', 'Iuran Saya', 'bi-clock-history', $file === 'riwayat.php') ?>
        <?php endif; ?>
        <?= $nav('/laporan/index.php', 'Laporan', 'bi-bar-chart-line', $aktif === 'laporan') ?>
      </ul>
      <div class="dropdown mt-2 mt-lg-0">
        <a class="btn btn-light btn-sm dropdown-toggle" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i> <?= e($nama) ?>
          <span class="badge bg-primary ms-1"><?= e($peran) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= BASE_URL ?>/password.php"><i class="bi bi-key me-2"></i>Ubah Password</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container py-4">
<?php if ($f = flash_get()): ?>
  <div class="alert alert-<?= e($f[0]) ?> alert-dismissible fade show no-print"><?= e($f[1]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<div class="page-head">
  <h1><?= e($judul) ?></h1>
  <?php if ($sub !== ''): ?><p><?= e($sub) ?></p><?php endif; ?>
</div>
<?php
}

function render_footer(): void { ?>
</main>
<footer class="text-center small py-3 no-print">Website Pengelolaan Iuran dan Transparansi Kas Kelas</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php }
