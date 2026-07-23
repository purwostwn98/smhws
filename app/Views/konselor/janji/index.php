<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Sesi Konseling<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$daftarJanji = $daftarJanji ?? [];
$counts      = $counts ?? [];
$activeTab   = $activeTab ?? 'semua';

$statusMeta = [
    'semua'        => ['label' => 'Semua',        'color' => 'secondary'],
    'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'color' => 'info'],
    'terjadwal'    => ['label' => 'Terjadwal',    'color' => 'primary'],
    'berlangsung'  => ['label' => 'Berlangsung',  'color' => 'success'],
    'selesai'      => ['label' => 'Selesai',      'color' => 'dark'],
    'dibatalkan'   => ['label' => 'Dibatalkan',   'color' => 'danger'],
];
?>

<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Sesi Konseling Saya</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Daftar seluruh sesi yang telah ditetapkan untukmu.</p>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Tabs -->
<div class="mb-4">
  <ul class="nav nav-pills gap-1 flex-wrap">
    <?php foreach ($statusMeta as $key => $meta): ?>
      <li class="nav-item">
        <a href="<?= base_url('konselor/janji?status=' . $key) ?>"
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

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:40px;">#</th>
          <th>Mahasiswa</th>
          <th>Jadwal</th>
          <th>Metode</th>
          <th>Status</th>
          <th style="width:80px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($daftarJanji)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="ti tabler-calendar-off" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
              Tidak ada sesi ditemukan.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($daftarJanji as $j):
            $meta = $statusMeta[$j['status']] ?? ['label' => $j['status'], 'color' => 'secondary'];
            $isToday = $j['tanggal_konseling'] === date('Y-m-d');
          ?>
          <tr>
            <td><span class="text-muted" style="font-size:.75rem;">#<?= str_pad($j['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
            <td>
              <div class="fw-semibold" style="font-size:.875rem;"><?= esc($j['name'] ?? '—') ?></div>
              <div class="text-muted" style="font-size:.75rem;"><?= esc($j['uniid'] ?? '') ?></div>
            </td>
            <td style="font-size:.82rem;">
              <?php if ($j['tanggal_konseling']): ?>
                <span class="<?= $isToday ? 'text-success fw-semibold' : '' ?>">
                  <?= $isToday ? 'Hari ini' : date('d M Y', strtotime($j['tanggal_konseling'])) ?>
                </span>
                <?php if ($j['jam_konseling']): ?>
                  <span class="text-muted"> <?= date('H:i', strtotime($j['jam_konseling'])) ?></span>
                <?php endif ?>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif ?>
            </td>
            <td><span class="text-capitalize" style="font-size:.82rem;"><?= esc($j['metode']) ?></span></td>
            <td>
              <span class="badge bg-label-<?= $meta['color'] ?>" style="font-size:.72rem;"><?= $meta['label'] ?></span>
            </td>
            <td>
              <a href="<?= base_url('konselor/janji/' . $j['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Detail">
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
