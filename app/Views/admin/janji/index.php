<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Kelola Konseling<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$daftarJanji   = $daftarJanji ?? [];
$counts        = $counts ?? [];
$activeTab     = $activeTab ?? 'semua';
$safetyFlagIds = $safetyFlagIds ?? [];

$statusMeta = [
    'semua'        => ['label' => 'Semua',         'color' => 'secondary'],
    'menunggu'     => ['label' => 'Menunggu',       'color' => 'warning'],
    'dikonfirmasi' => ['label' => 'Dikonfirmasi',   'color' => 'info'],
    'terjadwal'    => ['label' => 'Terjadwal',      'color' => 'primary'],
    'berlangsung'  => ['label' => 'Berlangsung',    'color' => 'success'],
    'selesai'      => ['label' => 'Selesai',        'color' => 'dark'],
    'dibatalkan'   => ['label' => 'Dibatalkan',     'color' => 'danger'],
];
?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Kelola Konseling</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Tinjau, tetapkan konselor, dan atur jadwal sesi konseling.</p>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Filter Tabs -->
<div class="mb-4">
  <ul class="nav nav-pills gap-1 flex-wrap">
    <?php foreach ($statusMeta as $key => $meta): ?>
      <li class="nav-item">
        <a href="<?= base_url('admin/janji?status=' . $key) ?>"
           class="nav-link <?= $activeTab === $key ? 'active' : '' ?> d-flex align-items-center gap-1 px-3 py-1">
          <?= $meta['label'] ?>
          <span class="badge bg-<?= $activeTab === $key ? 'white text-primary' : 'label-' . $meta['color'] ?> ms-1" style="font-size:.7rem;">
            <?= $counts[$key] ?? 0 ?>
          </span>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</div>

<!-- Table -->
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:40px;">#</th>
          <th>Mahasiswa</th>
          <th>Tema</th>
          <th>Metode</th>
          <th>Status</th>
          <th>Jadwal</th>
          <th>Konselor</th>
          <th style="width:80px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($daftarJanji)): ?>
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="ti tabler-calendar-off" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
              Tidak ada konseling ditemukan.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($daftarJanji as $j):
            $meta   = $statusMeta[$j['status']] ?? ['label' => $j['status'], 'color' => 'secondary'];
            $isSafe = in_array($j['id'], $safetyFlagIds);
          ?>
          <tr>
            <td>
              <span class="fw-semibold text-muted" style="font-size:.75rem;">#<?= str_pad($j['id'], 5, '0', STR_PAD_LEFT) ?></span>
              <?php if ($isSafe): ?>
                <i class="ti tabler-alert-triangle text-danger" title="Safety flag aktif" style="font-size:.85rem;"></i>
              <?php endif ?>
            </td>
            <td>
              <div class="fw-semibold" style="font-size:.875rem;"><?= esc($j['name'] ?? '-') ?></div>
              <div class="text-muted" style="font-size:.75rem;"><?= esc($j['nim_nip'] ?? '') ?></div>
            </td>
            <td>
              <?php if (! empty($j['tema_konseling'])): ?>
                <span class="badge bg-label-info" style="font-size:.72rem;"><?= esc(ucwords(str_replace('_', ' ', $j['tema_konseling']))) ?></span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif ?>
            </td>
            <td><span class="text-capitalize" style="font-size:.82rem;"><?= esc($j['metode']) ?></span></td>
            <td>
              <span class="badge bg-label-<?= $meta['color'] ?>" style="font-size:.72rem;">
                <?= $meta['label'] ?>
              </span>
            </td>
            <td style="font-size:.82rem;">
              <?php if ($j['tanggal_konseling']): ?>
                <?= date('d M Y', strtotime($j['tanggal_konseling'])) ?>
                <?php if ($j['jam_konseling']): ?>
                  <span class="text-muted"><?= date('H:i', strtotime($j['jam_konseling'])) ?></span>
                <?php endif ?>
              <?php else: ?>
                <span class="text-muted">Belum ditetapkan</span>
              <?php endif ?>
            </td>
            <td style="font-size:.82rem;">
              <?php if (! empty($j['konselor_id'])): ?>
                <span class="text-truncate d-inline-block" style="max-width:120px;">Terpilih</span>
              <?php else: ?>
                <span class="text-warning">Belum</span>
              <?php endif ?>
            </td>
            <td>
              <a href="<?= base_url('admin/janji/' . $j['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
                <i class="ti tabler-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
