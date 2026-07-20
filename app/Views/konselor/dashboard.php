<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Dashboard Konselor<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$konselor      = $konselor ?? [];
$stats         = $stats ?? [];
$sesiMendatang = $sesiMendatang ?? [];
$menungguHasil = $menungguHasil ?? [];
?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Dashboard Konselor</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Selamat datang kembali! Pantau sesi dan kelola konseling Anda.
    </p>
  </div>
  <span class="badge bg-label-secondary align-self-center" style="font-size:.8rem;">
    <i class="ti tabler-calendar-event me-1"></i><?= date('l, d F Y') ?>
  </span>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
  <?php
  $cards = [
    ['label' => 'Sesi Mendatang',     'value' => $stats['terjadwal'],     'icon' => 'tabler-calendar-time',   'color' => 'primary'],
    ['label' => 'Sedang Berlangsung', 'value' => $stats['berlangsung'],    'icon' => 'tabler-activity',        'color' => 'success'],
    ['label' => 'Menunggu Hasil',     'value' => $stats['menunggu_hasil'], 'icon' => 'tabler-clipboard-list',  'color' => 'warning'],
    ['label' => 'Total Selesai',      'value' => $stats['selesai'],        'icon' => 'tabler-circle-check',    'color' => 'info'],
  ];
  foreach ($cards as $c): ?>
  <div class="col-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center bg-label-<?= $c['color'] ?>"
             style="width:44px;height:44px;">
          <i class="ti <?= $c['icon'] ?> text-<?= $c['color'] ?>" style="font-size:1.3rem;"></i>
        </div>
        <div>
          <div class="fw-bold fs-4 lh-1 mb-1"><?= $c['value'] ?></div>
          <div class="text-muted" style="font-size:.78rem;"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach ?>
</div>

<div class="row g-4">

  <!-- Sesi Mendatang -->
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-calendar-time me-2 text-primary"></i>Sesi Mendatang</h6>
        <a href="<?= base_url('konselor/janji') ?>" class="btn btn-sm btn-outline-primary px-3">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($sesiMendatang)): ?>
          <div class="text-center py-5 text-muted">
            <i class="ti tabler-calendar-off" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
            Belum ada sesi mendatang.
          </div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($sesiMendatang as $s):
              $isToday = $s['tanggal_konseling'] === date('Y-m-d');
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-start gap-2 py-3 px-4">
              <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.875rem;"><?= esc($s['name'] ?? '—') ?></div>
                <div class="text-muted" style="font-size:.75rem;"><?= esc($s['nim_nip'] ?? '') ?></div>
                <?php if (! empty($s['lokasi_link'])): ?>
                  <div class="text-muted mt-1" style="font-size:.75rem;">
                    <i class="ti tabler-map-pin me-1"></i><?= esc($s['lokasi_link']) ?>
                  </div>
                <?php endif ?>
              </div>
              <div class="text-end flex-shrink-0">
                <?php if ($s['tanggal_konseling']): ?>
                  <div class="fw-semibold <?= $isToday ? 'text-success' : '' ?>" style="font-size:.82rem;">
                    <?= $isToday ? 'Hari ini' : date('d M Y', strtotime($s['tanggal_konseling'])) ?>
                  </div>
                  <?php if ($s['jam_konseling']): ?>
                    <div class="text-muted" style="font-size:.75rem;"><?= date('H:i', strtotime($s['jam_konseling'])) ?> WIB</div>
                  <?php endif ?>
                <?php endif ?>
                <a href="<?= base_url('konselor/janji/' . $s['id']) ?>"
                   class="btn btn-sm mt-1 px-3"
                   style="background:<?= $isToday ? 'linear-gradient(135deg,#28c76f,#1a9e5a)' : 'linear-gradient(135deg,#1a5f7a,#0d3f52)' ?>;color:#fff;font-weight:600;letter-spacing:.01em;">
                  <i class="ti tabler-eye me-1"></i>Detail
                </a>
              </div>
            </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- Perlu Diisi Hasilnya -->
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-clipboard-list me-2 text-warning"></i>Perlu Pengisian Hasil</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($menungguHasil)): ?>
          <div class="text-center py-5 text-muted">
            <i class="ti tabler-circle-check" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#28c76f;"></i>
            Semua sesi sudah terisi.
          </div>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($menungguHasil as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center gap-2 py-3 px-4">
              <div>
                <div class="fw-semibold" style="font-size:.875rem;"><?= esc($s['name'] ?? '—') ?></div>
                <div class="text-muted" style="font-size:.75rem;">
                  <?= $s['tanggal_konseling'] ? date('d M Y', strtotime($s['tanggal_konseling'])) : 'Tanggal tidak diset' ?>
                </div>
              </div>
              <a href="<?= base_url('konselor/janji/' . $s['id']) ?>" class="btn btn-sm btn-warning">
                <i class="ti tabler-edit me-1"></i>Isi
              </a>
            </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
