<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Rekap Skrining<?= $this->endSection() ?>
<?php
helper('tanggal');
use App\Models\DassAssessmentModel as Dass;

$th   = 'px-3 py-3 fw-semibold text-muted';
$thSt = 'font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;';
$subskala = ['depresi' => 'Depresi', 'anxiety' => 'Kecemasan', 'stress' => 'Stres'];
$warna = ['normal' => '#2d9b6e', 'ringan' => '#0dcaf0', 'sedang' => '#f0a500', 'berat' => '#e07a20', 'sangat_berat' => '#dc3545'];
$f = $filters;
?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Rekap Skrining</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Hasil Mental Health Check (DASS-21) yang diisi mahasiswa tanpa login.</p>
  </div>
  <a href="<?= base_url('admin/rekap-skrining/export' . ($queryString ? '?' . $queryString : '')) ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
    <i class="ti tabler-download"></i>Unduh CSV
  </a>
</div>

<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert"><?= esc($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif ?>

<!-- Filter -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="get" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Fakultas</label>
        <select name="fakultas" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['fakultas'] as $o): ?><option value="<?= esc($o) ?>" <?= $f['fakultas'] === $o ? 'selected' : '' ?>><?= esc($o) ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Program Studi</label>
        <select name="id_lembaga" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['prodi'] as $o): ?><option value="<?= esc($o['id_lembaga']) ?>" <?= $f['id_lembaga'] === $o['id_lembaga'] ? 'selected' : '' ?>><?= esc($o['nama_prodi']) ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select form-select-sm">
          <option value="">Semua</option>
          <option value="laki-laki" <?= $f['jenis_kelamin'] === 'laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="perempuan" <?= $f['jenis_kelamin'] === 'perempuan' ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Semester</label>
        <select name="semester" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['semester'] as $o): ?><option value="<?= $o ?>" <?= $f['semester'] === (string) $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2"></div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Dari tanggal</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= esc($f['dari']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Sampai tanggal</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= esc($f['sampai']) ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="ti tabler-filter me-1"></i>Terapkan</button>
        <a href="<?= base_url('admin/rekap-skrining') ?>" class="btn btn-label-secondary btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Ringkasan -->
<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-bold">Ringkasan <span class="text-muted fw-normal">· <?= number_format($total) ?> pengisian</span></h6>
  </div>
  <div class="card-body">
    <?php foreach ($subskala as $key => $label):
      $n = $stats[$key]; $jumlah = array_sum($n);
    ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between small mb-1"><span class="fw-semibold"><?= $label ?></span></div>
        <div class="d-flex rounded overflow-hidden" style="height:1.4rem;background:#eee;">
          <?php foreach ($n as $kat => $c): if (! $c || ! $jumlah) continue; $pct = $c / $jumlah * 100; ?>
            <div title="<?= Dass::labelKategori($kat) ?>: <?= $c ?> (<?= round($pct) ?>%)"
                 style="width:<?= $pct ?>%;background:<?= $warna[$kat] ?>;color:#fff;font-size:.7rem;text-align:center;line-height:1.4rem;"><?= $pct >= 8 ? $c : '' ?></div>
          <?php endforeach ?>
        </div>
      </div>
    <?php endforeach ?>
    <div class="d-flex flex-wrap gap-3 small mt-2">
      <?php foreach ($warna as $kat => $c): ?>
        <span><i class="ti tabler-square-filled" style="color:<?= $c ?>;"></i> <?= Dass::labelKategori($kat) ?></span>
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
              <?php foreach ($subskala as $label): ?><th class="<?= $th ?> text-center" style="<?= $thSt ?>"><?= $label ?></th><?php endforeach ?>
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
                <?php foreach (array_keys($subskala) as $key): ?>
                  <td class="px-3 py-3 text-center">
                    <div class="fw-semibold"><?= (int) $r['skor_' . $key] ?></div>
                    <span class="badge <?= Dass::badgeKelas($r['kategori_' . $key]) ?>"><?= Dass::labelKategori($r['kategori_' . $key]) ?></span>
                  </td>
                <?php endforeach ?>
                <td class="px-3 py-3 text-end">
                  <a href="<?= base_url('admin/rekap-skrining/' . $r['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Detail"><i class="ti tabler-eye"></i></a>
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
