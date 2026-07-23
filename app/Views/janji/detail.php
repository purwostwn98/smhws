<?= $this->extend('layouts/dashboard') ?>
<?php
$janji               = $janji ?? [];
$konselorNama        = $konselorNama ?? null;
$konselorPilihanList = $konselorPilihanList ?? [];
$dass                = $dass ?? null;
$safety              = $safety ?? null;
$feedback            = $feedback ?? null;
?>
<?= $this->section('title') ?>Detail Janji #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
[$statusClass, $statusLabel] = match ($janji['status']) {
  'menunggu'     => ['bg-label-warning',   'Menunggu Konfirmasi Admin'],
  'dikonfirmasi' => ['bg-label-info',      'Jadwal Ditetapkan — Menunggu Konfirmasimu'],
  'terjadwal'    => ['bg-label-primary',   'Terjadwal'],
  'berlangsung'  => ['bg-label-success',   'Sedang Berlangsung'],
  'selesai'      => ['bg-label-dark',      'Selesai'],
  'dibatalkan'   => ['bg-label-danger',    'Dibatalkan'],
  default        => ['bg-label-secondary', ucfirst($janji['status'])],
};

$noJanji = '#' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT);
?>

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <a href="<?= base_url('janji') ?>" class="btn btn-icon btn-label-secondary">
    <i class="ti tabler-arrow-left"></i>
  </a>
  <div class="flex-grow-1">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <h4 class="fw-bold mb-0" style="color:#1a2b40;">Konseling <?= $noJanji ?></h4>
      <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
    </div>
    <p class="text-muted mb-0" style="font-size:.85rem;">
      Didaftarkan <?= date('d M Y, H:i', strtotime($janji['created_at'])) ?> WIB
    </p>
  </div>
</div>

<?php
$steps = [
    'menunggu'     => ['label' => 'Menunggu',      'icon' => 'tabler-clock-hour-4',   'desc' => 'Pendaftaran diterima'],
    'dikonfirmasi' => ['label' => 'Jadwal Dibuat', 'icon' => 'tabler-calendar-plus',  'desc' => 'Admin menetapkan jadwal'],
    'terjadwal'    => ['label' => 'Terkonfirmasi', 'icon' => 'tabler-circle-check',   'desc' => 'Kehadiran dikonfirmasi'],
    'berlangsung'  => ['label' => 'Berlangsung',   'icon' => 'tabler-activity',       'desc' => 'Sesi sedang berjalan'],
    'selesai'      => ['label' => 'Selesai',       'icon' => 'tabler-rosette-check',  'desc' => 'Sesi selesai'],
];
$stepKeys    = array_keys($steps);
$currentStep = $janji['status'] === 'dibatalkan' ? -1 : array_search($janji['status'], $stepKeys);
?>
<!-- Progress Timeline -->
<div class="col-12 mb-4">
  <div class="card shadow-sm" style="border-radius:.75rem;overflow:hidden;">
    <div class="card-body py-4 px-3 px-md-4">
      <?php if ($janji['status'] === 'dibatalkan'): ?>
        <div class="d-flex align-items-center gap-3 py-2">
          <div style="width:2.8rem;height:2.8rem;border-radius:50%;background:#dc3545;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ti tabler-x" style="color:#fff;font-size:1.3rem;"></i>
          </div>
          <div>
            <div class="fw-bold" style="color:#dc3545;">Konseling Dibatalkan</div>
            <div class="text-muted" style="font-size:.8rem;">Sesi ini telah dibatalkan dan tidak dapat dilanjutkan.</div>
          </div>
        </div>
      <?php else: ?>
      <div class="d-flex align-items-start justify-content-between position-relative" style="gap:0;">
        <!-- Garis penghubung -->
        <div style="position:absolute;top:1.35rem;left:calc(10% + 1.4rem);right:calc(10% + 1.4rem);height:2px;background:#e0e0e0;z-index:0;">
          <?php $pct = $currentStep > 0 ? min(100, round($currentStep / (count($steps) - 1) * 100)) : 0; ?>
          <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,#28c76f,#1a5f7a);transition:width .4s;"></div>
        </div>

        <?php foreach ($steps as $key => $step):
            $idx    = array_search($key, $stepKeys);
            $done   = $idx < $currentStep || ($idx === $currentStep && $janji['status'] === 'selesai');
            $active = $idx === $currentStep && $janji['status'] !== 'selesai';
        ?>
        <div class="d-flex flex-column align-items-center text-center position-relative" style="flex:1;z-index:1;">
          <div style="
            width:2.8rem;height:2.8rem;border-radius:50%;
            background:<?= $done ? '#28c76f' : ($active ? '#1a5f7a' : '#e0e0e0') ?>;
            display:flex;align-items:center;justify-content:center;
            box-shadow:<?= $active ? '0 0 0 4px rgba(26,95,122,.2)' : 'none' ?>;
            transition:.3s;
          ">
            <i class="ti <?= $done ? 'tabler-check' : $step['icon'] ?>"
               style="color:<?= ($done || $active) ? '#fff' : '#aaa' ?>;font-size:<?= $active ? '1.15rem' : '1rem' ?>;"></i>
          </div>
          <div class="mt-2" style="font-size:.73rem;font-weight:<?= $active ? '700' : '500' ?>;color:<?= $done ? '#28c76f' : ($active ? '#1a2b40' : '#aaa') ?>;">
            <?= $step['label'] ?>
          </div>
          <div class="d-none d-md-block" style="font-size:.68rem;color:#bbb;line-height:1.3;margin-top:.15rem;">
            <?= $step['desc'] ?>
          </div>
        </div>
        <?php endforeach ?>
      </div>

      <?php endif ?>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- Kiri: Info Utama -->
  <div class="col-lg-8">

    <!-- Info Konseling -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="ti tabler-calendar-event me-2" style="color:#1a5f7a;"></i>Informasi Konseling
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-sm-6">
            <div class="text-muted mb-1" style="font-size:.78rem;">Metode</div>
            <?php [$mc, $mi] = match ($janji['metode']) {
              'offline' => ['bg-label-secondary', 'tabler-map-pin'],
              'online'  => ['bg-label-info',      'tabler-video'],
              'hybrid'  => ['bg-label-primary',   'tabler-arrows-exchange'],
              default   => ['bg-label-secondary', 'tabler-help-circle'],
            }; ?>
            <span class="badge <?= $mc ?>">
              <i class="ti <?= $mi ?> me-1"></i><?= ucfirst($janji['metode']) ?>
            </span>
          </div>

          <div class="col-sm-6">
            <div class="text-muted mb-1" style="font-size:.78rem;">Psikolog</div>
            <div class="fw-semibold" style="font-size:.9rem;">
              <?= $konselorNama ? esc($konselorNama) : '<span class="text-muted">Belum ditetapkan</span>' ?>
            </div>
          </div>

          <?php if (! empty($janji['tanggal_konseling'])): ?>
            <div class="col-sm-6">
              <div class="text-muted mb-1" style="font-size:.78rem;">Tanggal Konseling</div>
              <div class="fw-semibold" style="font-size:.9rem;">
                <?= date('d M Y', strtotime($janji['tanggal_konseling'])) ?>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="text-muted mb-1" style="font-size:.78rem;">Jam</div>
              <div class="fw-semibold" style="font-size:.9rem;">
                <?= ! empty($janji['jam_konseling']) ? substr($janji['jam_konseling'], 0, 5) . ' WIB' : '-' ?>
              </div>
            </div>
          <?php endif ?>

          <?php if (! empty($janji['lokasi_link'])): ?>
            <div class="col-12">
              <div class="text-muted mb-1" style="font-size:.78rem;">Lokasi / Link</div>
              <div style="font-size:.9rem;"><?= esc($janji['lokasi_link']) ?></div>
            </div>
          <?php endif ?>

          <?php if (! empty($janji['catatan_admin'])): ?>
            <div class="col-12">
              <div class="text-muted mb-1" style="font-size:.78rem;">Catatan Admin</div>
              <div class="p-3 rounded-2" style="background:rgba(26,95,122,.06);font-size:.85rem;">
                <?= nl2br(esc($janji['catatan_admin'])) ?>
              </div>
            </div>
          <?php endif ?>

        </div>
      </div>
    </div>

    <!-- Keluhan & Kondisi -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="ti tabler-message-2 me-2" style="color:#2d9b6e;"></i>Keluhan & Kondisi
        </h5>
      </div>
      <div class="card-body">
        <?php if (! empty($janji['tema_konseling'])):
            $temaLabel = [
                'akademik'          => ['label' => 'Akademik',          'icon' => 'tabler-school'],
                'keorganisasian'    => ['label' => 'Keorganisasian',    'icon' => 'tabler-building-community'],
                'pengembangan_diri' => ['label' => 'Pengembangan Diri', 'icon' => 'tabler-plant-2'],
                'relasi'            => ['label' => 'Relasi',            'icon' => 'tabler-users'],
                'pribadi'           => ['label' => 'Pribadi',           'icon' => 'tabler-user-heart'],
                'keluarga'          => ['label' => 'Keluarga',          'icon' => 'tabler-home-heart'],
                'lainnya'           => ['label' => 'Lainnya',           'icon' => 'tabler-dots-circle-horizontal'],
            ];
            $tema = $temaLabel[$janji['tema_konseling']] ?? ['label' => ucfirst($janji['tema_konseling']), 'icon' => 'tabler-tag'];
        ?>
        <div class="mb-4">
          <div class="text-muted mb-1" style="font-size:.78rem;">Tema Konseling</div>
          <span class="badge bg-label-primary d-inline-flex align-items-center gap-1" style="font-size:.82rem;padding:.35em .7em;">
            <i class="ti <?= $tema['icon'] ?>" style="font-size:.9rem;"></i>
            <?= $tema['label'] ?>
          </span>
        </div>
        <?php endif ?>

        <div class="mb-4">
          <div class="text-muted mb-1" style="font-size:.78rem;">Keluhan Utama</div>
          <div class="p-3 rounded-2" style="background:#f8f9fa;font-size:.88rem;line-height:1.6;">
            <?= nl2br(esc($janji['keluhan_utama'])) ?>
          </div>
        </div>
        <?php if (! empty($janji['urgensi'])):
            $urgensiMeta = [
                'biasa'        => ['label' => 'Biasa',        'color' => '#2d9b6e', 'bg' => 'bg-label-success', 'icon' => 'tabler-circle'],
                'cukup_urgen'  => ['label' => 'Cukup Urgen',  'color' => '#f0a500', 'bg' => 'bg-label-warning', 'icon' => 'tabler-alert-circle'],
                'sangat_urgen' => ['label' => 'Sangat Urgen', 'color' => '#dc3545', 'bg' => 'bg-label-danger',  'icon' => 'tabler-alert-triangle'],
            ];
            $urg = $urgensiMeta[$janji['urgensi']] ?? ['label' => $janji['urgensi'], 'bg' => 'bg-label-secondary', 'icon' => 'tabler-circle'];
        ?>
        <div class="mb-4">
          <div class="text-muted mb-1" style="font-size:.78rem;">Tingkat Urgensi</div>
          <span class="badge <?= $urg['bg'] ?> d-inline-flex align-items-center gap-1" style="font-size:.82rem;padding:.35em .7em;">
            <i class="ti <?= $urg['icon'] ?>"></i>
            <?= $urg['label'] ?>
          </span>
        </div>
        <?php endif ?>

        <?php if (! empty($janji['mulai_keluhan'])): ?>
          <div class="mb-4">
            <div class="text-muted mb-1" style="font-size:.78rem;">Mulai Muncul</div>
            <div style="font-size:.88rem;"><?= nl2br(esc($janji['mulai_keluhan'])) ?></div>
          </div>
        <?php endif ?>
        <?php if (! empty($janji['upaya_dilakukan'])): ?>
          <div>
            <div class="text-muted mb-1" style="font-size:.78rem;">Upaya yang Sudah Dilakukan</div>
            <div style="font-size:.88rem;"><?= nl2br(esc($janji['upaya_dilakukan'])) ?></div>
          </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Preferensi Konseling -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="ti tabler-adjustments-horizontal me-2" style="color:#57c5b6;"></i>Preferensi Konseling
        </h5>
      </div>
      <div class="card-body">

        <!-- Jadwal Pilihan -->
        <div class="mb-4">
          <div class="text-muted mb-2" style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
            <i class="ti tabler-clock me-1"></i>Jadwal yang Dipilih
          </div>
          <?php
          $jadwal = $janji['jadwal_pilihan'] ?? [];
          if (! is_array($jadwal)) $jadwal = json_decode($jadwal, true) ?: [];
          ?>
          <?php if (! empty($jadwal)): ?>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($jadwal as $j2):
                $metode = $j2['metode'] ?? null;
                [$mc, $mi, $ml] = match ($metode) {
                  'online'   => ['bg-label-info',      'tabler-video',           'Online'],
                  'offline'  => ['bg-label-success',   'tabler-map-pin',         'Offline'],
                  'keduanya' => ['bg-label-primary',   'tabler-arrows-exchange', 'Online & Offline'],
                  default    => ['', '', ''],
                };
                $tanggalPilihan = $j2['tanggal'] ?? null;
              ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <?php if ($tanggalPilihan): ?>
                    <span class="badge bg-label-secondary px-3 py-2" style="font-size:.82rem;">
                      <i class="ti tabler-calendar me-1"></i>
                      <?= date('d M Y', strtotime($tanggalPilihan)) ?>
                    </span>
                  <?php endif ?>
                  <span class="badge bg-label-primary px-3 py-2" style="font-size:.82rem;">
                    <i class="ti tabler-calendar-week me-1"></i>
                    <?= ucfirst($j2['hari']) ?> — <?= esc($j2['waktu']) ?>
                  </span>
                  <?php if ($mc): ?>
                    <span class="badge <?= $mc ?> px-3 py-2" style="font-size:.82rem;">
                      <i class="ti <?= $mi ?> me-1"></i><?= $ml ?>
                    </span>
                  <?php endif ?>
                </div>
              <?php endforeach ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada preferensi jadwal.</p>
          <?php endif ?>
        </div>

        <!-- Psikolog Pilihan -->
        <div>
          <div class="text-muted mb-2" style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
            <i class="ti tabler-user-check me-1"></i>Psikolog yang Dipilih
          </div>
          <?php if (! empty($konselorPilihanList)): ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($konselorPilihanList as $kNama): ?>
                <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#f8f9fa;">
                  <div class="avatar avatar-sm flex-shrink-0">
                    <div class="avatar-initial rounded-circle"
                      style="background:linear-gradient(135deg,#1a5f7a,#2d9b6e);color:#fff;font-size:.75rem;">
                      <?= strtoupper(substr($kNama, 0, 1)) ?>
                    </div>
                  </div>
                  <span style="font-size:.88rem;font-weight:500;"><?= esc($kNama) ?></span>
                </div>
              <?php endforeach ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada preferensi psikolog.</p>
          <?php endif ?>
        </div>

      </div>
    </div>

    <!-- DASS Assessment -->
    <?php if ($dass): ?>
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">
            <i class="ti tabler-brain me-2" style="color:#f0a500;"></i>Hasil Asesmen Awal
          </h5>
          <span class="text-muted" style="font-size:.78rem;">
            <?= date('d M Y', strtotime($dass['tanggal_pengisian'])) ?>
          </span>
        </div>
        <div class="card-body">
          <?php
          $dassInterpretasi = [
            'depresi' => [
              'normal'       => 'Kamu mengalami perubahan suasana hati, namun masih dalam batas wajar.',
              'ringan'       => 'Kamu tampaknya mulai mengalami sedikit penurunan suasana hati, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering mengalami penurunan suasana hati, dan ini mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami penurunan suasana hati yang lumayan besar, sehingga aktivitas harianmu agak terganggu.',
              'sangat_berat' => 'Kamu tampaknya sedang mengalami penurunan suasana hati yang besar dan sudah mengganggu aktivitas harianmu.',
            ],
            'anxiety' => [
              'normal'       => 'Kamu merasa cemas namun masih dalam batas wajar.',
              'ringan'       => 'Kamu tampaknya mulai mengalami peningkatan rasa cemas, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering mengalami kecemasan yang mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami kecemasan yang cukup tinggi, dan mengganggu aktivitas harianmu.',
              'sangat_berat' => 'Kamu tampaknya sedang mengalami rasa cemas yang tinggi, dan ini sudah mengganggu aktivitas harianmu.',
            ],
            'stress' => [
              'normal'       => 'Kamu mengalami sedikit tekanan, namun masih berada dalam tahap wajar.',
              'ringan'       => 'Kamu tampaknya mulai merasakan peningkatan tekanan dan ketegangan, namun masih tergolong ringan.',
              'sedang'       => 'Kamu tampaknya cukup sering merasakan tekanan dan ketegangan yang mulai mempengaruhi aktivitas harianmu.',
              'berat'        => 'Kamu tampaknya sedang mengalami tekanan dan ketegangan yang cukup tinggi, dan ini mengganggu aktivitas harianmu.',
              'sangat_berat' => 'Kamu tampaknya dalam kondisi tertekan dan ketegangan yang tinggi, dan ini sudah mengganggu aktivitas harianmu.',
            ],
          ];

          $dassSubscales = [
            ['label' => 'Depresi',    'skor' => $dass['skor_depresi'], 'raw' => $dass['skor_depresi_raw'], 'kategori' => $dass['kategori_depresi'], 'icon' => 'tabler-mood-sad',           'color' => '#1a5f7a', 'key' => 'depresi'],
            ['label' => 'Kecemasan',  'skor' => $dass['skor_anxiety'], 'raw' => $dass['skor_anxiety_raw'], 'kategori' => $dass['kategori_anxiety'], 'icon' => 'tabler-heart-rate-monitor', 'color' => '#f0a500', 'key' => 'anxiety'],
            ['label' => 'Stres',      'skor' => $dass['skor_stress'],  'raw' => $dass['skor_stress_raw'],  'kategori' => $dass['kategori_stress'],  'icon' => 'tabler-bolt',               'color' => '#2d9b6e', 'key' => 'stress'],
          ];

          $dassBadge = [
            'normal'       => ['class' => 'bg-label-success', 'label' => 'Normal'],
            'ringan'       => ['class' => 'bg-label-info',    'label' => 'Ringan'],
            'sedang'       => ['class' => 'bg-label-warning', 'label' => 'Sedang'],
            'berat'        => ['class' => 'bg-label-danger',  'label' => 'Berat'],
            'sangat_berat' => ['class' => 'bg-danger text-white', 'label' => 'Sangat Berat'],
          ];
          ?>

          <div class="row g-3">
            <?php foreach ($dassSubscales as $s):
              $interp = $dassInterpretasi[$s['key']][$s['kategori']] ?? '';
            ?>
              <div class="col-md-4">
                <div class="h-100 rounded-3 p-3" style="background:#f8f9fa;border-top:3px solid <?= $s['color'] ?>;">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ti <?= $s['icon'] ?>" style="font-size:1.1rem;color:<?= $s['color'] ?>;"></i>
                    <span class="fw-semibold" style="font-size:.88rem;"><?= $s['label'] ?></span>
                  </div>
                  <div class="d-flex align-items-end gap-1 mb-2">
                    <span class="fw-bold" style="font-size:2rem;color:<?= $s['color'] ?>;line-height:1;"><?= $s['skor'] ?></span>
                    <span class="text-muted mb-1" style="font-size:.73rem;">skor &nbsp;·&nbsp; raw <?= $s['raw'] ?></span>
                  </div>
                  <?php if ($interp): ?>
                    <div class="rounded-2 px-2 py-2" style="background:rgba(0,0,0,.04);font-size:.78rem;line-height:1.55;color:#444;">
                      <?= $interp ?>
                    </div>
                  <?php endif ?>
                </div>
              </div>
            <?php endforeach ?>
          </div>

          <?php
          // Rekomendasi berdasarkan kategori tertinggi
          $katOrder = ['normal' => 0, 'ringan' => 1, 'sedang' => 2, 'berat' => 3, 'sangat_berat' => 4];
          $maxLevel = max(
            $katOrder[$dass['kategori_depresi']] ?? 0,
            $katOrder[$dass['kategori_anxiety']]  ?? 0,
            $katOrder[$dass['kategori_stress']]   ?? 0
          );
          $rekMap = [
            0 => "Kondisimu saat ini tampaknya cukup baik. Usahakan untuk menjaganya, misalnya dengan pola hidup sehat, seperti tidur yang cukup, makan bergizi, berolahraga, menjaga hubungan baik dengan keluarga, teman, dosen, dll., dan meluangkan waktu untuk melakukan aktivitas positif yang kamu sukai. Jangan ragu untuk mencari bantuan jika suatu saat kamu merasa membutuhkan.",
            1 => "Kondisimu saat ini mungkin sedikit kurang bagus. Cobalah untuk mengenali hal apa yang mungkin mempengaruhi suasana perasaanmu, luangkan waktu untuk beristirahat, lakukan aktivitas positif yang bisa membuatmu lebih nyaman, dan berbicaralah kepada orang-orang di sekitarmu yang bisa kamu percaya terkait hal-hal yang sedang mengganggu suasana perasaanmu.",
            2 => "Kondisi yang kamu rasakan saat ini tampaknya mulai mempengaruhi aktivitas harianmu. Akan sangat baik jika kamu mencari bantuan dari teman, keluarga, dosen, atau psikolog untuk berbagi cerita dan memperoleh dukungan supaya kondisimu lebih baik.",
            3 => "Kondisi yang kamu rasakan saat ini tampaknya sudah cukup mengganggu keseharianmu. Kamu sebaiknya tidak menghadapinya sendirian. Pertimbangkan untuk berbicara dengan psikolog atau mengunjungi layanan kesehatan mental agar kamu mendapatkan bantuan yang sesuai.",
            4 => "Kondisi yang kamu rasakan tampaknya sudah mempengaruhi kesejahteraan psikologis dan aktivitas harianmu. Kamu sangat disarankan untuk segera berbicara dengan psikolog atau mengunjungi layanan kesehatan mental guna mendapatkan bantuan profesional. Kamu butuh bantuan agar tidak menghadapi kondisi ini sendirian.",
          ];
          $rekMeta = [
            0 => ['icon' => 'tabler-mood-happy',   'color' => '#2d9b6e', 'bg' => 'rgba(45,155,110,.07)',  'border' => '#2d9b6e'],
            1 => ['icon' => 'tabler-mood-smile',   'color' => '#1a5f7a', 'bg' => 'rgba(26,95,122,.07)',   'border' => '#1a5f7a'],
            2 => ['icon' => 'tabler-mood-neutral', 'color' => '#d48f00', 'bg' => 'rgba(240,165,0,.09)',   'border' => '#f0a500'],
            3 => ['icon' => 'tabler-mood-sad',     'color' => '#c8600a', 'bg' => 'rgba(200,96,10,.08)',   'border' => '#e07a20'],
            4 => ['icon' => 'tabler-mood-cry',     'color' => '#dc3545', 'bg' => 'rgba(220,53,69,.08)',   'border' => '#dc3545'],
          ];
          $rm = $rekMeta[$maxLevel];
          ?>
          <div class="mt-3 p-3 rounded-3 d-flex gap-3 align-items-start"
               style="background:<?= $rm['bg'] ?>;border-left:3px solid <?= $rm['border'] ?>;">
            <i class="ti <?= $rm['icon'] ?> flex-shrink-0 mt-1" style="font-size:1.3rem;color:<?= $rm['color'] ?>;"></i>
            <div>
              <div class="fw-semibold mb-1" style="font-size:.83rem;color:<?= $rm['color'] ?>;">Rekomendasi</div>
              <div style="font-size:.82rem;line-height:1.6;color:#444;"><?= $rekMap[$maxLevel] ?></div>
            </div>
          </div>

          <?php if ($dass['is_reviewed']): ?>
            <div class="mt-3 p-3 rounded-2" style="background:rgba(45,155,110,.06);border-left:3px solid #2d9b6e;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-check mt-1 flex-shrink-0" style="color:#2d9b6e;"></i>
                <div style="font-size:.82rem;">
                  <strong>Sudah ditinjau psikolog</strong>
                  <?php if (! empty($dass['catatan_psikolog'])): ?>
                    <div class="mt-1 text-muted"><?= nl2br(esc($dass['catatan_psikolog'])) ?></div>
                  <?php endif ?>
                </div>
              </div>
            </div>
          <?php endif ?>
        </div>
      </div>
    <?php endif ?>

  </div>

  <!-- Kanan: Info Pribadi + Safety -->
  <div class="col-lg-4">

    <!-- Aksi Mahasiswa -->
    <?php if ($janji['status'] === 'dikonfirmasi'): ?>
    <div class="card mb-4" style="border:2px solid #696cff;border-radius:.75rem;box-shadow:0 4px 20px rgba(105,108,255,.18);">
      <div class="card-header py-3" style="background:linear-gradient(135deg,#696cff 0%,#9155fd 100%);border-radius:.6rem .6rem 0 0;">
        <div class="d-flex align-items-center gap-2">
          <i class="ti tabler-calendar-check text-white" style="font-size:1.3rem;"></i>
          <div>
            <div class="fw-bold text-white" style="font-size:.95rem;">Jadwal Ditetapkan!</div>
            <div class="text-white" style="font-size:.73rem;opacity:.85;">Konfirmasi kehadiranmu agar sesi dapat berlangsung</div>
          </div>
        </div>
      </div>
      <div class="card-body pt-3">
        <?php if ($konselorNama): ?>
          <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2" style="background:#f4f3ff;font-size:.85rem;">
            <i class="ti tabler-user-check" style="color:#696cff;font-size:1rem;flex-shrink:0;"></i>
            <div>
              <div style="font-size:.7rem;color:#9155fd;">Psikolog</div>
              <div class="fw-semibold" style="color:#1a2b40;"><?= esc($konselorNama) ?></div>
            </div>
          </div>
        <?php endif ?>
        <?php if ($janji['tanggal_konseling']): ?>
          <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2" style="background:#f4f3ff;font-size:.85rem;">
            <i class="ti tabler-calendar" style="color:#696cff;font-size:1rem;flex-shrink:0;"></i>
            <span class="fw-semibold" style="color:#1a2b40;">
              <?= date('l, d F Y', strtotime($janji['tanggal_konseling'])) ?>
              <?php if ($janji['jam_konseling']): ?>
                &nbsp;·&nbsp; <?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB
              <?php endif ?>
            </span>
          </div>
          <?php if (! empty($janji['lokasi_link'])): ?>
            <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded-2" style="background:#f4f3ff;font-size:.85rem;">
              <i class="ti tabler-map-pin" style="color:#696cff;font-size:1rem;flex-shrink:0;"></i>
              <span style="color:#1a2b40;"><?= esc($janji['lokasi_link']) ?></span>
            </div>
          <?php endif ?>
        <?php endif ?>
        <?php if (! empty($janji['catatan_admin'])): ?>
          <div class="p-2 rounded-2 mb-2" style="background:#f4f3ff;font-size:.82rem;">
            <div style="font-size:.7rem;color:#9155fd;margin-bottom:.25rem;">
              <i class="ti tabler-message-2 me-1"></i>Catatan dari Admin
            </div>
            <div style="color:#1a2b40;line-height:1.5;"><?= nl2br(esc($janji['catatan_admin'])) ?></div>
          </div>
        <?php endif ?>
        <div class="d-flex flex-column gap-2 mt-3">
          <form action="<?= base_url('janji/konfirmasi/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn w-100 fw-semibold"
                    style="background:linear-gradient(135deg,#696cff 0%,#9155fd 100%);color:#fff;padding:.6rem;font-size:.9rem;letter-spacing:.01em;">
              <i class="ti tabler-circle-check me-1"></i>Konfirmasi Kehadiran
            </button>
          </form>
          <form action="<?= base_url('janji/batal/' . $janji['id']) ?>" method="post"
                onsubmit="return confirm('Yakin ingin membatalkan konseling ini?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
              <i class="ti tabler-x me-1"></i>Batalkan Konseling
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endif ?>

    <?php if ($janji['status'] === 'terjadwal'): ?>
    <div class="alert alert-primary d-flex gap-2 align-items-start mb-4">
      <i class="ti tabler-circle-check" style="font-size:1.3rem;flex-shrink:0;margin-top:.1rem;"></i>
      <div style="font-size:.82rem;">
        <div class="fw-semibold mb-1">Kehadiran Dikonfirmasi</div>
        Sesi sudah terjadwal. Harap hadir tepat waktu.
        <?php if ($janji['tanggal_konseling']): ?>
          <div class="mt-1 fw-semibold"><?= date('d M Y', strtotime($janji['tanggal_konseling'])) ?>
            <?php if ($janji['jam_konseling']): ?> · <?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB<?php endif ?>
          </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <?php if ($janji['status'] === 'selesai'): ?>
    <?php if ($feedback): ?>
    <!-- Feedback sudah diberikan -->
    <div class="card mb-4 border-success" style="border-left:4px solid #28c76f!important;">
      <div class="card-header py-3" style="border-bottom:1px solid #eee;">
        <h6 class="fw-semibold mb-0" style="color:#1a2b40;font-size:.875rem;">
          <i class="ti tabler-star-filled me-2" style="color:#f0a500;"></i>Feedback Kamu
        </h6>
      </div>
      <div class="card-body">
        <!-- Bintang rating -->
        <div class="d-flex gap-1 mb-2">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="ti <?= $i <= $feedback['rating'] ? 'tabler-star-filled' : 'tabler-star' ?>"
              style="font-size:1.3rem;color:<?= $i <= $feedback['rating'] ? '#f0a500' : '#ccc' ?>;"></i>
          <?php endfor ?>
          <span class="ms-1 fw-semibold align-self-center" style="font-size:.9rem;color:#1a2b40;">
            <?= $feedback['rating'] ?>/5
          </span>
        </div>
        <?php if (! empty($feedback['komentar'])): ?>
          <div class="p-3 rounded-2 mb-2" style="background:#f8f9fa;font-size:.85rem;line-height:1.6;">
            <?= nl2br(esc($feedback['komentar'])) ?>
          </div>
        <?php endif ?>
        <div class="text-muted" style="font-size:.75rem;">
          Dikirim <?= date('d M Y, H:i', strtotime($feedback['created_at'])) ?> WIB
        </div>
      </div>
    </div>
    <?php else: ?>
    <!-- Belum ada feedback -->
    <div class="card mb-4 border-success" style="border-left:4px solid #28c76f!important;">
      <div class="card-body text-center py-4">
        <i class="ti tabler-mood-smile text-success" style="font-size:2.5rem;display:block;margin-bottom:.5rem;"></i>
        <div class="fw-semibold mb-1">Sesi selesai!</div>
        <p class="text-muted mb-3" style="font-size:.82rem;">Bagikan pengalamanmu untuk membantu kami berkembang.</p>
        <a href="<?= base_url('feedback/' . $janji['id']) ?>" class="btn btn-warning btn-sm w-100">
          <i class="ti tabler-star me-1"></i>Berikan Feedback
        </a>
      </div>
    </div>
    <?php endif ?>
    <?php endif ?>

    <!-- Data Pribadi -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="ti tabler-user me-2" style="color:#1a5f7a;"></i>Data Pribadi
        </h5>
      </div>
      <div class="card-body">
        <?php
        $agamaMap = ['islam' => 'Islam', 'kristen' => 'Kristen', 'katolik' => 'Katolik', 'hindu' => 'Hindu', 'budha' => 'Buddha', 'konghucu' => 'Konghucu'];
        $fields = [
          ['Jenis Kelamin', ucfirst($janji['jenis_kelamin'])],
          ['Usia', $janji['usia'] . ' tahun'],
          ['Agama', $agamaMap[strtolower($janji['agama'] ?? '')] ?? $janji['agama']],
          ['Semester', 'Semester ' . $janji['semester']],
          ['Dosen PA', $janji['dosen_pa'] ?? '-'],
          ['Domisili', $janji['domisili'] ?? '-'],
          ...(!empty($janji['pekerjaan']) ? [['Pekerjaan', $janji['pekerjaan']]] : []),
          ['Status Pernikahan', ucfirst(str_replace('_', ' ', $janji['status_pernikahan'] ?? '-'))],
          ['Pernah Konseling SMHWS', $janji['pernah_konseling_smhws'] ? 'Ya' : 'Belum pernah'],
        ];
        ?>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($fields as [$label, $val]): ?>
            <div class="d-flex justify-content-between py-1 border-bottom" style="font-size:.83rem;">
              <span class="text-muted"><?= $label ?></span>
              <span class="fw-semibold text-end ms-2"><?= esc($val) ?></span>
            </div>
          <?php endforeach ?>
        </div>
      </div>
    </div>

    <!-- Safety Screening -->
    <?php if ($safety): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="ti tabler-shield-check me-2" style="color:#2d9b6e;"></i>Safety Screening
          </h5>
        </div>
        <div class="card-body">
          <?php
          $sfItems = [
            ['Pernah menyakiti diri sendiri?', $safety['pernah_selfharm']],
            ['Merasa aman saat ini?', $safety['merasa_aman']],
            ['Pikiran mengakhiri hidup?', $safety['pikiran_mengakhiri_hidup']],
            ['Pikiran mengganggu?', $safety['pikiran_mengganggu']],
          ];
          $sfBadge = static function (string $val): string {
            return match ($val) {
              'ya'           => '<span class="badge bg-label-success">Ya</span>',
              'tidak'        => '<span class="badge bg-label-secondary">Tidak</span>',
              'tidak_berlaku' => '<span class="badge bg-label-secondary">Tidak Berlaku</span>',
              'kadang'       => '<span class="badge bg-label-warning">Kadang</span>',
              'sering'       => '<span class="badge bg-label-danger">Sering</span>',
              default        => '<span class="badge bg-label-secondary">' . esc($val) . '</span>',
            };
          };
          ?>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($sfItems as [$label, $val]): ?>
              <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:.82rem;">
                <span class="text-muted"><?= $label ?></span>
                <?= $sfBadge($val) ?>
              </div>
            <?php endforeach ?>
            <?php if (! empty($safety['riwayat_selfharm_keterangan'])): ?>
              <div class="pt-2" style="font-size:.8rem;">
                <div class="text-muted mb-1">Keterangan:</div>
                <div><?= nl2br(esc($safety['riwayat_selfharm_keterangan'])) ?></div>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    <?php endif ?>

    <!-- Emergency Box -->
    <div class="card smhws-emergency">
      <div class="card-body">
        <div class="d-flex gap-2 align-items-start">
          <i class="ti tabler-phone-call mt-1 flex-shrink-0" style="color:#f0a500;font-size:1.1rem;"></i>
          <div style="font-size:.82rem;">
            <div class="fw-semibold mb-1">Butuh Bantuan Segera?</div>
            <div class="text-muted mb-2">Hubungi hotline SMHWS:</div>
            <a href="tel:02712855" class="btn btn-sm btn-warning w-100 mb-1">
              <i class="ti tabler-phone me-1"></i>(0271) 2855 / Ext. 417
            </a>
            <a href="https://wa.me/6282145678900" class="btn btn-sm btn-success w-100">
              <i class="ti tabler-brand-whatsapp me-1"></i>WhatsApp Psikolog
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<?= $this->endSection() ?>