<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Dashboard Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php

use App\Models\KonselorModel;

$stats = $stats ?? [];
$janjiMenunggu = $janjiMenunggu ?? [];
$safetyFlagIds = $safetyFlagIds ?? [];
$konselorList = $konselorList ?? [];
$safetyAlerts = $safetyAlerts ?? [];
$dassStats = $dassStats ?? [];
$totalJanji = $totalJanji ?? 0;

?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Dashboard Admin</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Pantau aktivitas dan kelola konseling SMHWS.
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
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- ===== ROW 1: Stat Cards ===== -->
<div class="row g-4 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100" style="border-top:3px solid #f0a500;">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(240,165,0,.15);color:#f0a500;font-size:1.25rem;">
            <i class="ti tabler-clock-hour-4"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.75rem;color:#1a2b40;line-height:1;"><?= $stats['janji_menunggu'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Menunggu Konfirmasi</div>
          <div style="font-size:.72rem;color:#f0a500;" class="mt-1">Perlu ditindaklanjuti</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100" style="border-top:3px solid #57c5b6;">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(87,197,182,.15);color:#57c5b6;font-size:1.25rem;">
            <i class="ti tabler-calendar-check"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.75rem;color:#1a2b40;line-height:1;"><?= $stats['janji_hari_ini'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Jadwal Hari Ini</div>
          <div style="font-size:.72rem;color:#57c5b6;" class="mt-1">Dikonfirmasi & berlangsung</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100" style="border-top:3px solid #1a5f7a;">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(26,95,122,.15);color:#1a5f7a;font-size:1.25rem;">
            <i class="ti tabler-users"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.75rem;color:#1a2b40;line-height:1;"><?= $stats['total_mahasiswa'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Total Mahasiswa</div>
          <div style="font-size:.72rem;color:#1a5f7a;" class="mt-1">Total terdaftar</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100" style="border-top:3px solid #2d9b6e;">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(45,155,110,.15);color:#2d9b6e;font-size:1.25rem;">
            <i class="ti tabler-user-heart"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.75rem;color:#1a2b40;line-height:1;"><?= $stats['konselor_aktif'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Konselor Aktif</div>
          <div style="font-size:.72rem;color:#2d9b6e;" class="mt-1">Tersedia saat ini</div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ===== ROW 2: Janji Menunggu Konfirmasi ===== -->
<div class="card mb-4">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <h5 class="card-title mb-0">Konseling Menunggu Konfirmasi</h5>
      <?php if ($stats['janji_menunggu'] > 0): ?>
        <span class="badge bg-warning text-dark"><?= $stats['janji_menunggu'] ?></span>
      <?php endif ?>
    </div>
    <a href="<?= base_url('admin/janji?status=menunggu') ?>" class="btn btn-sm btn-label-primary">
      Lihat Semua <i class="ti tabler-arrow-right ms-1"></i>
    </a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($janjiMenunggu)): ?>
      <div class="text-center py-5">
        <i class="ti tabler-calendar-check text-success" style="font-size:2.5rem;"></i>
        <p class="text-muted mt-2 mb-0">Tidak ada konseling yang menunggu konfirmasi</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="px-4">#</th>
              <th>Mahasiswa</th>
              <th>Keluhan</th>
              <th>Metode</th>
              <th>Tgl Daftar</th>
              <th class="text-center">Flag</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($janjiMenunggu as $j): ?>
              <?php
              $isFlagged = in_array($j['id'], $safetyFlagIds);
              [$metodeClass, $metodeIcon] = match ($j['metode']) {
                'online'  => ['bg-label-info',    'tabler-video'],
                'hybrid'  => ['bg-label-primary', 'tabler-arrows-exchange'],
                default   => ['bg-label-secondary', 'tabler-map-pin'],
              };
              ?>
              <tr class="<?= $isFlagged ? 'table-danger' : '' ?>">
                <td class="px-4">
                  <span class="fw-semibold" style="font-size:.82rem;color:#1a2b40;">
                    #<?= str_pad($j['id'], 5, '0', STR_PAD_LEFT) ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm flex-shrink-0">
                      <div class="avatar-initial rounded-circle"
                        style="background:rgba(26,95,122,.12);color:#1a5f7a;font-size:.75rem;">
                        <?= strtoupper(mb_substr($j['name'], 0, 1)) ?>
                      </div>
                    </div>
                    <div>
                      <div class="fw-semibold" style="font-size:.83rem;"><?= esc($j['name']) ?></div>
                      <?php if (! empty($j['nim_nip'])): ?>
                        <small class="text-muted"><?= esc($j['nim_nip']) ?></small>
                      <?php endif ?>
                    </div>
                  </div>
                </td>
                <td style="max-width:220px;">
                  <span class="text-muted" style="font-size:.8rem;">
                    <?= esc(mb_strimwidth($j['keluhan_utama'], 0, 70, '…')) ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?= $metodeClass ?>" style="font-size:.75rem;">
                    <i class="ti <?= $metodeIcon ?> me-1"></i><?= ucfirst($j['metode']) ?>
                  </span>
                </td>
                <td style="font-size:.82rem;white-space:nowrap;">
                  <?= date('d M Y', strtotime($j['created_at'])) ?>
                </td>
                <td class="text-center">
                  <?php if ($isFlagged): ?>
                    <i class="ti tabler-alert-triangle text-danger" title="Ada safety screening berisiko"></i>
                  <?php else: ?>
                    <i class="ti tabler-shield-check text-success" style="opacity:.4;"></i>
                  <?php endif ?>
                </td>
                <td>
                  <a href="<?= base_url('admin/janji/' . $j['id']) ?>"
                    class="btn btn-icon btn-sm btn-label-primary"
                    title="Lihat & konfirmasi">
                    <i class="ti tabler-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php endif ?>
  </div>
</div>

<!-- ===== ROW 3: Status Distribution + Konselor ===== -->
<div class="row g-4 mb-4">

  <!-- Distribusi Status -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Distribusi Status Konseling</h5>
      </div>
      <div class="card-body">
        <?php
        $statusInfo = [
          'menunggu'     => ['label' => 'Menunggu',      'color' => '#f0a500', 'badge' => 'bg-label-warning'],
          'dikonfirmasi' => ['label' => 'Dikonfirmasi',  'color' => '#1a5f7a', 'badge' => 'bg-label-primary'],
          'berlangsung'  => ['label' => 'Berlangsung',   'color' => '#57c5b6', 'badge' => 'bg-label-info'],
          'selesai'      => ['label' => 'Selesai',       'color' => '#2d9b6e', 'badge' => 'bg-label-success'],
          'dibatalkan'   => ['label' => 'Dibatalkan',    'color' => '#dc3545', 'badge' => 'bg-label-danger'],
        ];
        ?>
        <?php if ($totalJanji === 0): ?>
          <p class="text-muted text-center py-4 mb-0">Belum ada data konseling.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($statusInfo as $key => $info): ?>
              <?php
              $count = $statusDist[$key] ?? 0;
              $pct   = $totalJanji > 0 ? round($count / $totalJanji * 100) : 0;
              ?>
              <div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="badge <?= $info['badge'] ?>" style="font-size:.75rem;min-width:90px;text-align:center;">
                    <?= $info['label'] ?>
                  </span>
                  <span class="fw-bold" style="font-size:.85rem;color:#1a2b40;"><?= $count ?></span>
                </div>
                <div class="progress" style="height:6px;border-radius:3px;">
                  <div class="progress-bar" role="progressbar"
                    style="width:<?= $pct ?>%;background:<?= $info['color'] ?>;"
                    aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            <?php endforeach ?>
          </div>
          <div class="text-muted text-center mt-3" style="font-size:.78rem;">
            Total <?= $totalJanji ?> konseling terdaftar
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- Daftar Konselor -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Daftar Konselor</h5>
        <a href="<?= base_url('admin/konselor') ?>" class="btn btn-sm btn-label-primary">Kelola</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($konselorList)): ?>
          <div class="text-center py-5">
            <i class="ti tabler-user-off text-muted" style="font-size:2rem;"></i>
            <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">Belum ada konselor terdaftar</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="px-4">Konselor</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Total Sesi</th>
                  <th class="text-center">Rating</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($konselorList as $k): ?>
                  <tr>
                    <td class="px-4">
                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm flex-shrink-0">
                          <div class="avatar-initial rounded-circle"
                            style="background:linear-gradient(135deg,#1a5f7a,#2d9b6e);color:#fff;font-size:.75rem;">
                            <?= strtoupper(mb_substr($k['name'], 0, 1)) ?>
                          </div>
                        </div>
                        <div>
                          <div class="fw-semibold" style="font-size:.83rem;">
                            <?= esc(KonselorModel::namaLengkap($k)) ?>
                          </div>
                          <?php if (! empty($k['email'])): ?>
                            <small class="text-muted"><?= esc($k['email']) ?></small>
                          <?php endif ?>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge <?= $k['is_available'] ? 'bg-label-success' : 'bg-label-danger' ?>"
                        style="font-size:.72rem;">
                        <?= $k['is_available'] ? 'Aktif' : 'Tidak Aktif' ?>
                      </span>
                    </td>
                    <td class="text-center fw-semibold" style="font-size:.85rem;"><?= $k['total_sesi'] ?></td>
                    <td class="text-center">
                      <?php if ($k['rating'] > 0): ?>
                        <span style="font-size:.83rem;color:#f0a500;">
                          <i class="ti tabler-star-filled me-1" style="font-size:.75rem;"></i><?= number_format($k['rating'], 1) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted" style="font-size:.78rem;">—</span>
                      <?php endif ?>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

</div>

<!-- ===== ROW 4: Safety Alerts + DASS Overview ===== -->
<div class="row g-4">

  <!-- Safety Screening Alerts -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center gap-2">
        <h5 class="card-title mb-0">Safety Screening Berisiko</h5>
        <?php if (! empty($safetyAlerts)): ?>
          <span class="badge bg-danger"><?= count($safetyAlerts) ?></span>
        <?php endif ?>
      </div>
      <div class="card-body">
        <?php if (empty($safetyAlerts)): ?>
          <div class="text-center py-4">
            <i class="ti tabler-shield-check" style="font-size:2.5rem;color:#2d9b6e;"></i>
            <p class="text-muted mt-2 mb-0" style="font-size:.85rem;">
              Tidak ada laporan safety screening berisiko saat ini.
            </p>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($safetyAlerts as $sf): ?>
              <?php
              [$statusClass, $statusLabel] = match ($sf['status']) {
                'menunggu'     => ['bg-label-warning', 'Menunggu'],
                'dikonfirmasi' => ['bg-label-primary',  'Dikonfirmasi'],
                'berlangsung'  => ['bg-label-info',     'Berlangsung'],
                default        => ['bg-label-secondary', ucfirst($sf['status'])],
              };
              $flags = [];
              if ($sf['pernah_selfharm'] === 'ya')               $flags[] = 'Riwayat self-harm';
              if ($sf['pikiran_mengakhiri_hidup'] === 'ya')       $flags[] = 'Pikiran mengakhiri hidup';
              if ($sf['pikiran_mengganggu'] === 'ya')             $flags[] = 'Pikiran sulit dikendalikan';
              ?>
              <div class="d-flex align-items-start gap-3 p-3 rounded-2"
                style="background:rgba(220,53,69,.05);border-left:3px solid #dc3545;">
                <i class="ti tabler-alert-triangle text-danger mt-1" style="font-size:1.1rem;flex-shrink:0;"></i>
                <div class="flex-grow-1 overflow-hidden">
                  <div class="fw-semibold" style="font-size:.85rem;"><?= esc($sf['mahasiswa_nama']) ?></div>
                  <div class="text-danger" style="font-size:.78rem;"><?= implode(' · ', $flags) ?></div>
                  <small class="text-muted"><?= date('d M Y', strtotime($sf['created_at'])) ?></small>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                  <span class="badge <?= $statusClass ?>" style="font-size:.72rem;"><?= $statusLabel ?></span>
                  <a href="<?= base_url('admin/janji/' . $sf['id']) ?>"
                    class="btn btn-icon btn-sm btn-label-danger" title="Lihat janji">
                    <i class="ti tabler-eye"></i>
                  </a>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- DASS Overview -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Ringkasan DASS-21</h5>
      </div>
      <div class="card-body">
        <div class="text-center mb-4">
          <div class="fw-bold" style="font-size:2.5rem;color:#1a2b40;line-height:1;">
            <?= $dassStats['total'] ?>
          </div>
          <div class="text-muted" style="font-size:.8rem;">Total Asesmen</div>
        </div>

        <?php
        $dassInfo = [
          ['label' => 'Depresi',  'key' => 'depresi_berat', 'color' => '#dc3545', 'bg' => 'rgba(220,53,69,.08)',  'icon' => 'tabler-mood-sad'],
          ['label' => 'Anxiety',  'key' => 'anxiety_berat', 'color' => '#f0a500', 'bg' => 'rgba(240,165,0,.08)', 'icon' => 'tabler-brain'],
          ['label' => 'Stres',    'key' => 'stress_berat',  'color' => '#57c5b6', 'bg' => 'rgba(87,197,182,.08)', 'icon' => 'tabler-bolt'],
        ];
        ?>

        <div class="d-flex flex-column gap-3">
          <?php foreach ($dassInfo as $d): ?>
            <?php
            $count = (int) ($dassStats[$d['key']] ?? 0);
            $total = (int) ($dassStats['total'] ?? 0);
            $pct   = $total > 0 ? round($count / $total * 100, 1) : 0;
            ?>
            <div class="d-flex align-items-center gap-3 p-3 rounded-2" style="background:<?= $d['bg'] ?>;">
              <div class="avatar flex-shrink-0">
                <div class="avatar-initial rounded" style="background:<?= $d['color'] ?>20;color:<?= $d['color'] ?>;font-size:1.1rem;">
                  <i class="ti <?= $d['icon'] ?>"></i>
                </div>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.85rem;color:#1a2b40;"><?= $d['label'] ?> Berat</div>
                <div class="text-muted" style="font-size:.75rem;">Kategori berat & sangat berat</div>
              </div>
              <div class="text-end">
                <div class="fw-bold" style="font-size:1.4rem;color:<?= $d['color'] ?>;line-height:1;"><?= $count ?></div>
                <small class="text-muted"><?= $pct ?>%</small>
              </div>
            </div>
          <?php endforeach ?>
        </div>

        <p class="text-muted text-center mt-3 mb-0" style="font-size:.75rem;">
          Kasus berat &amp; sangat berat yang memerlukan perhatian lebih
        </p>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>