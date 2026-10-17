<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Rekap Skrining<?= $this->endSection() ?>
<?php
helper('tanggal');
use App\Libraries\PolaInternet as PI;

$th   = 'px-3 py-3 fw-semibold text-muted';
$thSt = 'font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;';
$f    = $filters;
$per  = $stats['kategori'];
$jumlah = array_sum($per);
?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Rekap Skrining</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Hasil Mental Health Check (Pola Penggunaan Internet) yang diisi mahasiswa tanpa login.</p>
  </div>
  <a href="<?= base_url('admin/rekap-skrining/internet/export' . ($queryString ? '?' . $queryString : '')) ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
    <i class="ti tabler-download"></i>Unduh CSV
  </a>
</div>

<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert"><?= esc($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif ?>

<?= $this->include('admin/rekap_skrining/_tabs') ?>

<?= $this->include('admin/rekap_skrining/_filter') ?>

<!-- Ringkasan -->
<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="mb-0 fw-bold">Ringkasan <span class="text-muted fw-normal">· <?= number_format($total) ?> pengisian</span></h6>
    <?php if ($stats['rata'] !== null): ?>
      <span class="small text-muted">Rata-rata skor: <strong><?= $stats['rata'] ?></strong> / <?= PI::SKOR_MAKS ?></span>
    <?php endif ?>
  </div>
  <div class="card-body">
    <div class="d-flex rounded overflow-hidden mb-3" style="height:1.6rem;background:#eee;">
      <?php foreach ($per as $kat => $c): if (! $c || ! $jumlah) continue; $pct = $c / $jumlah * 100; ?>
        <div title="<?= PI::labelKategori($kat) ?>: <?= $c ?> (<?= round($pct) ?>%)"
             style="width:<?= $pct ?>%;background:<?= PI::warna($kat) ?>;color:#fff;font-size:.72rem;text-align:center;line-height:1.6rem;"><?= $pct >= 8 ? $c : '' ?></div>
      <?php endforeach ?>
    </div>
    <div class="row g-3">
      <?php foreach (PI::KATEGORI as $kat => [$min, $max, $label]): ?>
        <div class="col-6 col-md-3">
          <div class="p-2 rounded" style="border-left:3px solid <?= PI::warna($kat) ?>;background:#f8f9fa;">
            <div class="small text-muted"><?= $label ?> <span style="font-size:.7rem;">(<?= $min ?>–<?= $max ?>)</span></div>
            <div class="fw-bold fs-5"><?= $per[$kat] ?> <span class="text-muted fw-normal small"><?= $jumlah ? round($per[$kat] / $jumlah * 100) . '%' : '' ?></span></div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($list)): ?>
      <div class="text-center py-5 text-muted">
        <i class="ti tabler-clipboard-off" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
        Belum ada data skrining<?= array_filter($f) ? ' untuk filter ini' : '' ?>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.85rem;">
          <thead style="background:#f8f9fa;border-bottom:2px solid #e9ecef;">
            <tr>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Tanggal</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">JK</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Smt</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Program Studi</th>
              <th class="<?= $th ?> text-center" style="<?= $thSt ?>">Skor</th>
              <th class="<?= $th ?> text-center" style="<?= $thSt ?>">Kategori</th>
              <th class="<?= $th ?> text-end" style="<?= $thSt ?>"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $r): ?>
              <tr>
                <td class="px-3 py-3"><?= esc(tanggal_id($r['created_at'], true)) ?></td>
                <td class="px-3 py-3"><?= $r['jenis_kelamin'] === 'laki-laki' ? 'L' : 'P' ?></td>
                <td class="px-3 py-3"><?= (int) $r['semester'] ?></td>
                <td class="px-3 py-3"><div><?= esc($r['nama_prodi']) ?></div><div class="text-muted small"><?= esc($r['nama_fakultas']) ?></div></td>
                <td class="px-3 py-3 text-center fw-semibold"><?= (int) $r['skor_total'] ?></td>
                <td class="px-3 py-3 text-center">
                  <span class="badge" style="background:<?= PI::warna($r['kategori']) ?>;"><?= PI::labelKategori($r['kategori']) ?></span>
                </td>
                <td class="px-3 py-3 text-end">
                  <a href="<?= base_url('admin/rekap-skrining/internet/' . $r['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Detail"><i class="ti tabler-eye"></i></a>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <div class="p-3"><?= $pager->links() ?></div>
    <?php endif ?>
  </div>
</div>

<?= $this->endSection() ?>
