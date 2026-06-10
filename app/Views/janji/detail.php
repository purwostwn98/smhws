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
      <h4 class="fw-bold mb-0" style="color:#1a2b40;">Janji <?= $noJanji ?></h4>
      <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
    </div>
    <p class="text-muted mb-0" style="font-size:.85rem;">
      Didaftarkan <?= date('d M Y, H:i', strtotime($janji['created_at'])) ?> WIB
    </p>
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
            <div class="text-muted mb-1" style="font-size:.78rem;">Konselor</div>
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
              <?php foreach ($jadwal as $j2): ?>
                <span class="badge bg-label-primary px-3 py-2" style="font-size:.82rem;">
                  <i class="ti tabler-calendar-week me-1"></i>
                  <?= ucfirst($j2['hari']) ?> — <?= esc($j2['waktu']) ?>
                </span>
              <?php endforeach ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada preferensi jadwal.</p>
          <?php endif ?>
        </div>

        <!-- Konselor Pilihan -->
        <div>
          <div class="text-muted mb-2" style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
            <i class="ti tabler-user-check me-1"></i>Konselor yang Dipilih
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
            <p class="text-muted mb-0" style="font-size:.85rem;">Tidak ada preferensi konselor.</p>
          <?php endif ?>
        </div>

      </div>
    </div>

    <!-- DASS Assessment -->
    <?php if ($dass): ?>
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">
            <i class="ti tabler-brain me-2" style="color:#f0a500;"></i>Hasil DASS-21
          </h5>
          <span class="text-muted" style="font-size:.78rem;">
            <?= date('d M Y', strtotime($dass['tanggal_pengisian'])) ?>
          </span>
        </div>
        <div class="card-body">
          <div class="row g-3">

            <?php
            $subscales = [
              ['label' => 'Depresi', 'raw' => $dass['skor_depresi_raw'], 'skor' => $dass['skor_depresi'], 'kategori' => $dass['kategori_depresi'], 'icon' => 'tabler-mood-sad', 'color' => '#1a5f7a'],
              ['label' => 'Anxietas', 'raw' => $dass['skor_anxiety_raw'], 'skor' => $dass['skor_anxiety'], 'kategori' => $dass['kategori_anxiety'], 'icon' => 'tabler-heart-rate-monitor', 'color' => '#f0a500'],
              ['label' => 'Stres', 'raw' => $dass['skor_stress_raw'], 'skor' => $dass['skor_stress'], 'kategori' => $dass['kategori_stress'], 'icon' => 'tabler-bolt', 'color' => '#2d9b6e'],
            ];

            $badgeMap = [
              'normal'       => 'bg-label-success',
              'ringan'       => 'bg-label-info',
              'sedang'       => 'bg-label-warning',
              'berat'        => 'bg-label-danger',
              'sangat_berat' => 'bg-danger text-white',
            ];
            $labelMap = [
              'normal'       => 'Normal',
              'ringan'       => 'Ringan',
              'sedang'       => 'Sedang',
              'berat'        => 'Berat',
              'sangat_berat' => 'Sangat Berat',
            ];
            ?>

            <?php foreach ($subscales as $s): ?>
              <div class="col-sm-4">
                <div class="p-3 rounded-3 text-center h-100" style="background:#f8f9fa;">
                  <i class="ti <?= $s['icon'] ?> mb-2" style="font-size:1.5rem;color:<?= $s['color'] ?>;"></i>
                  <div class="fw-semibold mb-1" style="font-size:.85rem;"><?= $s['label'] ?></div>
                  <div class="fw-bold mb-1" style="font-size:1.4rem;color:<?= $s['color'] ?>;"><?= $s['skor'] ?></div>
                  <div class="text-muted mb-2" style="font-size:.75rem;">Raw: <?= $s['raw'] ?></div>
                  <span class="badge <?= $badgeMap[$s['kategori']] ?? 'bg-label-secondary' ?>">
                    <?= $labelMap[$s['kategori']] ?? ucfirst($s['kategori']) ?>
                  </span>
                </div>
              </div>
            <?php endforeach ?>

          </div>

          <?php if ($dass['is_reviewed']): ?>
            <div class="mt-3 p-3 rounded-2" style="background:rgba(45,155,110,.06);border-left:3px solid #2d9b6e;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-check mt-1 flex-shrink-0" style="color:#2d9b6e;"></i>
                <div style="font-size:.82rem;">
                  <strong>Sudah ditinjau konselor</strong>
                  <?php if (! empty($dass['catatan_konselor'])): ?>
                    <div class="mt-1 text-muted"><?= nl2br(esc($dass['catatan_konselor'])) ?></div>
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
    <div class="card mb-4 border-primary" style="border-left:4px solid #696cff!important;">
      <div class="card-body">
        <div class="fw-bold mb-1"><i class="ti tabler-calendar-check text-primary me-1"></i>Jadwal Ditetapkan!</div>
        <p class="text-muted mb-3" style="font-size:.82rem;">
          Admin telah menetapkan jadwal konselingmu. Konfirmasi kehadiranmu agar sesi dapat berlangsung.
        </p>
        <?php if ($janji['tanggal_konseling']): ?>
          <div class="mb-2" style="font-size:.82rem;">
            <i class="ti tabler-calendar me-1 text-muted"></i>
            <?= date('l, d F Y', strtotime($janji['tanggal_konseling'])) ?>
            <?php if ($janji['jam_konseling']): ?>· <?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB<?php endif ?>
          </div>
          <?php if (! empty($janji['lokasi_link'])): ?>
            <div class="mb-3" style="font-size:.82rem;">
              <i class="ti tabler-map-pin me-1 text-muted"></i><?= esc($janji['lokasi_link']) ?>
            </div>
          <?php endif ?>
        <?php endif ?>
        <form action="<?= base_url('janji/konfirmasi/' . $janji['id']) ?>" method="post">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="ti tabler-circle-check me-1"></i>Konfirmasi Kehadiran
          </button>
        </form>
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
              <i class="ti tabler-brand-whatsapp me-1"></i>WhatsApp Konselor
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<?= $this->endSection() ?>