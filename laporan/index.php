<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
wajib_login();   // anggota boleh LIHAT; cetak/ekspor hanya bendahara

function tgl_valid(string $v): ?string {
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $v : null;
}
$dari   = tgl_valid(trim($_GET['dari'] ?? ''));
$sampai = tgl_valid(trim($_GET['sampai'] ?? ''));

$where = []; $par = [];
if ($dari)   { $where[] = 'tanggal >= ?'; $par[] = $dari; }
if ($sampai) { $where[] = 'tanggal <= ?'; $par[] = $sampai; }
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$q = pdo()->prepare("SELECT tanggal, jenis, uraian, jumlah FROM (" . SQL_LAPORAN . ") AS l $sqlWhere ORDER BY tanggal, jenis");
$q->execute($par);
$rows = $q->fetchAll();

$masuk = $keluar = 0;
foreach ($rows as $r) { if ($r['jenis'] === 'pemasukan') $masuk += $r['jumlah']; else $keluar += $r['jumlah']; }

// ekspor CSV (hanya bendahara)
if (($_GET['export'] ?? '') === 'csv') {
    wajib_bendahara();
    $safe = fn($t) => preg_match('/^[=+\-@]/', (string)$t) ? "'" . $t : $t;   // cegah CSV injection
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_kas.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Tanggal', 'Jenis', 'Uraian', 'Jumlah']);
    foreach ($rows as $r) fputcsv($out, [$r['tanggal'], $r['jenis'], $safe($r['uraian']), $r['jumlah']]);
    fputcsv($out, ['', '', 'Total pemasukan', $masuk]);
    fputcsv($out, ['', '', 'Total pengeluaran', $keluar]);
    fputcsv($out, ['', '', 'Selisih', $masuk - $keluar]);
    fclose($out);
    exit;
}

$k = saldo_kas();
$qs = http_build_query(array_filter(['dari' => $dari, 'sampai' => $sampai]));
render_header('Laporan Kas', 'Semua uang masuk dan keluar. Anggota hanya dapat melihat.');
?>
<form method="get" class="row g-2 mb-3 align-items-end no-print">
  <div class="col-auto"><label class="form-label mb-0 small">Dari tanggal</label><input type="date" name="dari" class="form-control" value="<?= e($dari) ?>"></div>
  <div class="col-auto"><label class="form-label mb-0 small">Sampai tanggal</label><input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>"></div>
  <div class="col-auto"><button class="btn btn-primary">Tampilkan</button> <a href="index.php" class="btn btn-outline-secondary">Reset</a></div>
  <?php if (is_bendahara()): ?>
  <div class="col-auto ms-auto">
    <button type="button" class="btn btn-outline-dark" onclick="window.print()">Cetak</button>
    <a class="btn btn-outline-success" href="?<?= e($qs ? $qs . '&' : '') ?>export=csv">Ekspor CSV</a>
  </div><?php endif; ?>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card card-stat"><div class="card-body"><div class="text-muted small">Pemasukan (periode ditampilkan)</div><div class="nilai text-success"><?= rupiah($masuk) ?></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-body"><div class="text-muted small">Pengeluaran (periode ditampilkan)</div><div class="nilai text-danger"><?= rupiah($keluar) ?></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat border-primary"><div class="card-body"><div class="text-muted small">Saldo kas saat ini</div><div class="nilai text-primary"><?= rupiah($k['saldo']) ?></div></div></div></div>
</div>

<div class="card"><div class="table-responsive"><table class="table table-sm table-hover mb-0">
  <thead><tr><th>Tanggal</th><th>Jenis</th><th>Uraian</th><th class="text-end">Jumlah</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= tgl($r['tanggal']) ?></td>
    <td><span class="badge bg-<?= $r['jenis'] === 'pemasukan' ? 'success' : 'danger' ?>"><?= e($r['jenis']) ?></span></td>
    <td><?= e($r['uraian']) ?></td><td class="text-end"><?= rupiah($r['jumlah']) ?></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="4" class="text-center text-muted">Tidak ada transaksi pada rentang ini.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php render_footer();
