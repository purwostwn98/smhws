<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Konseling Saya<?= $this->endSection() ?>
<?php

$daftarJanji = $daftarJanji ?? [];

?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Konseling Saya</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Riwayat dan status seluruh pendaftaran konselingmu.</p>
  </div>
  <a href="<?= base_url('janji/buat') ?>" class="btn btn-primary d-none d-sm-flex align-items-center gap-2">
    <i class="ti tabler-calendar-plus"></i>Daftar Konseling Baru
  </a>
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
<?php
$statusList = ['semua', 'menunggu', 'dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
$activeTab  = $_GET['status'] ?? 'semua';
if (! in_array($activeTab, $statusList)) $activeTab = 'semua';

$filtered = $daftarJanji;
if ($activeTab !== 'semua') {
  $filtered = array_values(array_filter($daftarJanji, fn($j) => $j['status'] === $activeTab));
}

$counts = array_count_values(array_column($daftarJanji, 'status'));
$counts['semua'] = count($daftarJanji);
?>

<div class="mb-4">
  <ul class="nav nav-pills gap-1 flex-wrap" id="janjiTabs">
    <?php foreach (
      [
        'semua'        => ['label' => 'Semua',        'icon' => 'tabler-list'],
        'menunggu'     => ['label' => 'Menunggu',      'icon' => 'tabler-clock-hour-4'],
        'dikonfirmasi' => ['label' => 'Dikonfirmasi',  'icon' => 'tabler-calendar-check'],
        'terjadwal'    => ['label' => 'Terjadwal',     'icon' => 'tabler-calendar-time'],
        'berlangsung'  => ['label' => 'Berlangsung',   'icon' => 'tabler-activity'],
        'selesai'      => ['label' => 'Selesai',       'icon' => 'tabler-circle-check'],
        'dibatalkan'   => ['label' => 'Dibatalkan',    'icon' => 'tabler-circle-x'],
      ] as $key => $info
    ): ?>
      <li class="nav-item">
        <a href="?status=<?= $key ?>"
          class="nav-link py-2 px-3 <?= $activeTab === $key ? 'active' : '' ?>"
          style="font-size:.85rem;">
          <i class="ti <?= $info['icon'] ?> me-1"></i>
          <?= $info['label'] ?>
          <?php if (($counts[$key] ?? 0) > 0): ?>
            <span class="badge rounded-pill ms-1
              <?= $activeTab === $key ? 'bg-white text-primary' : 'bg-label-secondary' ?>">
              <?= $counts[$key] ?? 0 ?>
            </span>
          <?php endif ?>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</div>

<!-- List -->
<?php if (empty($filtered)): ?>
  <div class="card">
    <div class="card-body text-center py-5">
      <div class="avatar avatar-xl mx-auto mb-3">
        <div class="avatar-initial rounded-circle"
          style="background:rgba(26,95,122,.1);color:#1a5f7a;font-size:1.75rem;">
          <i class="ti tabler-calendar-off"></i>
        </div>
      </div>
      <?php if ($activeTab === 'semua'): ?>
        <h6 class="text-muted">Belum ada konseling</h6>
        <p class="text-muted mb-4" style="font-size:.85rem;">
          Mulai perjalanan kesehatanmu dengan mendaftar konseling pertama.
        </p>
        <a href="<?= base_url('janji/buat') ?>" class="btn btn-primary">
          <i class="ti tabler-calendar-plus me-1"></i>Daftar Konseling
        </a>
      <?php else: ?>
        <h6 class="text-muted">Tidak ada konseling dengan status "<?= ucfirst($activeTab) ?>"</h6>
        <a href="?status=semua" class="btn btn-label-primary btn-sm mt-2">Lihat Semua</a>
      <?php endif ?>
    </div>
  </div>

<?php else: ?>
  <div class="row g-4">
    <?php foreach ($filtered as $j): ?>
      <?php
      [$statusClass, $statusLabel] = match ($j['status']) {
        'menunggu'     => ['bg-label-warning', 'Menunggu'],
        'dikonfirmasi' => ['bg-label-primary',  'Dikonfirmasi'],
        'berlangsung'  => ['bg-label-info',     'Berlangsung'],
        'selesai'      => ['bg-label-success',  'Selesai'],
        'dibatalkan'   => ['bg-label-danger',   'Dibatalkan'],
        default        => ['bg-label-secondary', ucfirst($j['status'])],
      };

      [$metodeClass, $metodeIcon] = match ($j['metode']) {
        'offline' => ['bg-label-secondary', 'tabler-map-pin'],
        'online'  => ['bg-label-info',      'tabler-video'],
        'hybrid'  => ['bg-label-primary',   'tabler-arrows-exchange'],
        default   => ['bg-label-secondary', 'tabler-help-circle'],
      };

      $konselorNama = $konselorMap[$j['konselor_id']] ?? null;
      $noJanji      = '#' . str_pad($j['id'], 5, '0', STR_PAD_LEFT);
      $tglDaftar    = date('d M Y', strtotime($j['created_at']));
      ?>
      <div class="col-md-6 col-xl-4">
        <div class="card h-100 janji-card position-relative"
          style="border-left:3px solid <?= match ($j['status']) {
                                          'menunggu'     => '#f0a500',
                                          'dikonfirmasi' => '#1a5f7a',
                                          'berlangsung'  => '#57c5b6',
                                          'selesai'      => '#2d9b6e',
                                          'dibatalkan'   => '#dc3545',
                                          default        => '#6c757d',
                                        } ?>;">
          <div class="card-body">

            <!-- Row 1: Nomor + Status -->
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div>
                <div class="fw-bold" style="color:#1a2b40;font-size:1rem;"><?= $noJanji ?></div>
                <div class="text-muted" style="font-size:.78rem;">
                  <i class="ti tabler-calendar-event me-1"></i><?= $tglDaftar ?>
                </div>
              </div>
              <span class="badge <?= $statusClass ?> ms-2"><?= $statusLabel ?></span>
            </div>

            <!-- Metode + Psikolog -->
            <div class="d-flex flex-column gap-2 mb-3">

              <div class="d-flex align-items-center gap-2">
                <span class="badge <?= $metodeClass ?> px-2 py-1" style="font-size:.78rem;">
                  <i class="ti <?= $metodeIcon ?> me-1"></i><?= ucfirst($j['metode']) ?>
                </span>
              </div>

              <?php if ($konselorNama): ?>
                <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.82rem;">
                  <i class="ti tabler-user-check flex-shrink-0"></i>
                  <span><?= esc($konselorNama) ?></span>
                </div>
              <?php else: ?>
                <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.82rem;">
                  <i class="ti tabler-user-question flex-shrink-0"></i>
                  <span>Psikolog belum ditetapkan</span>
                </div>
              <?php endif ?>

              <?php if (! empty($j['tanggal_konseling'])): ?>
                <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.82rem;">
                  <i class="ti tabler-clock flex-shrink-0"></i>
                  <span>
                    <?= date('d M Y', strtotime($j['tanggal_konseling'])) ?>
                    <?= ! empty($j['jam_konseling']) ? ' · ' . substr($j['jam_konseling'], 0, 5) . ' WIB' : '' ?>
                  </span>
                </div>
              <?php endif ?>

            </div>

            <!-- Keluhan (preview) -->
            <div class="p-2 rounded-2 mb-3" style="background:#f8f9fa;font-size:.8rem;color:#555;">
              <i class="ti tabler-message-2 me-1 text-muted"></i>
              <?= esc(mb_strimwidth($j['keluhan_utama'], 0, 90, '…')) ?>
            </div>

            <!-- Catatan Admin -->
            <?php if (! empty($j['catatan_admin'])): ?>
              <div class="d-flex gap-2 p-2 rounded-2 mb-3"
                style="background:rgba(26,95,122,.06);font-size:.78rem;">
                <i class="ti tabler-info-circle mt-1 flex-shrink-0" style="color:#1a5f7a;"></i>
                <span><?= esc($j['catatan_admin']) ?></span>
              </div>
            <?php endif ?>

            <!-- Action -->
            <div class="d-flex gap-2">
              <a href="<?= base_url('janji/' . $j['id']) ?>"
                class="btn btn-sm btn-label-primary flex-grow-1">
                <i class="ti tabler-eye me-1"></i>Lihat Detail
              </a>
              <?php if ($j['status'] === 'menunggu'): ?>
                <button type="button"
                  class="btn btn-sm btn-label-danger btn-hapus-janji"
                  data-id="<?= $j['id'] ?>"
                  data-nomor="<?= $noJanji ?>"
                  title="Hapus konseling ini">
                  <i class="ti tabler-trash"></i>
                </button>
              <?php endif ?>
            </div>

          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

<?php endif ?>

<!-- Mobile FAB -->
<a href="<?= base_url('janji/buat') ?>"
  class="btn btn-primary btn-lg rounded-circle d-sm-none position-fixed"
  style="bottom:1.5rem;right:1.5rem;width:56px;height:56px;display:flex!important;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(26,95,122,.4);z-index:1050;">
  <i class="ti tabler-plus fs-5"></i>
</a>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="avatar avatar-lg mx-auto mb-3">
          <div class="avatar-initial rounded-circle" style="background:rgba(220,53,69,.1);color:#dc3545;font-size:1.5rem;">
            <i class="ti tabler-trash"></i>
          </div>
        </div>
        <h5 class="fw-bold mb-1" style="color:#1a2b40;">Hapus Konseling?</h5>
        <p class="text-muted mb-4" style="font-size:.875rem;">
          Konseling <strong id="modalNomorJanji"></strong> akan dihapus permanen dan tidak dapat dikembalikan.
        </p>
        <form id="formHapus" method="post" action="">
          <?= csrf_field() ?>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">
              <i class="ti tabler-trash me-1"></i>Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .janji-card {
    transition: transform .15s, box-shadow .15s;
  }

  .janji-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
  }
</style>

<script>
  document.querySelectorAll('.btn-hapus-janji').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.dataset.id;
      var nomor = this.dataset.nomor;
      document.getElementById('modalNomorJanji').textContent = nomor;
      document.getElementById('formHapus').action = '<?= base_url('janji/hapus/') ?>' + id;
      new bootstrap.Modal(document.getElementById('modalHapus')).show();
    });
  });
</script>

<?= $this->endSection() ?>