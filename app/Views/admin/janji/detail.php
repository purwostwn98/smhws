<?= $this->extend('layouts/dashboard') ?>
<?php
use App\Models\KonselorModel;

$janji               = $janji ?? [];
$konselorList        = $konselorList ?? [];
$konselorNama        = $konselorNama ?? null;
$konselorPilihanList = $konselorPilihanList ?? [];
$dass                = $dass ?? null;
$safety              = $safety ?? null;
$hasil               = $hasil ?? null;
$checklistData       = $checklistData ?? [];
$instansiRujukan     = $instansiRujukan ?? [];

$statusMeta = [
    'menunggu'     => ['label' => 'Menunggu',      'color' => 'warning'],
    'dikonfirmasi' => ['label' => 'Jadwal ditetapkan - Menunggu Konfirmasi Mahasiswa',  'color' => 'info'],
    'terjadwal'    => ['label' => 'Terjadwal',     'color' => 'primary'],
    'berlangsung'  => ['label' => 'Berlangsung',   'color' => 'success'],
    'selesai'      => ['label' => 'Selesai',       'color' => 'dark'],
    'dibatalkan'   => ['label' => 'Dibatalkan',    'color' => 'danger'],
];
$sm = $statusMeta[$janji['status']] ?? ['label' => $janji['status'], 'color' => 'secondary'];

/* ── Helpers untuk layout "selesai" ─────────────────────────────── */
$secCard = function(string $num, string $title, string $iconClass = 'tabler-file-text', string $color = '#696cff', bool $readonly = false): void {
  if ($readonly): ?>
<div class="card mb-3" style="border-top:3px solid #adb5bd;">
  <div class="card-header py-2 px-4 d-flex align-items-center gap-2" style="background:#e9ecef;border-bottom:1px solid #dee2e6;">
    <i class="ti <?= $iconClass ?>" style="color:#6c757d;font-size:1rem;"></i>
    <span class="fw-bold" style="font-size:.9rem;color:#495057;"><?= $num ?>. <?= $title ?></span>
    <span class="badge ms-auto" style="background:#dee2e6;color:#6c757d;font-size:.7rem;font-weight:500;letter-spacing:.03em;border:1px solid #ced4da;">
      <i class="ti tabler-lock" style="font-size:.75rem;vertical-align:middle;"></i> Baca Saja
    </span>
  </div>
<?php else: ?>
<div class="card shadow-sm mb-3" style="border-top:3px solid <?= $color ?>;">
  <div class="card-header py-2 px-4 d-flex align-items-center gap-2"
       style="background:<?= $color ?>0d;">
    <i class="ti <?= $iconClass ?>" style="color:<?= $color ?>;font-size:1rem;"></i>
    <span class="fw-bold" style="font-size:.9rem;color:<?= $color ?>;"><?= $num ?>. <?= $title ?></span>
  </div>
<?php endif; };

$displaySavedSection = function(?string $json, array $checklistData, string $sectionKey): string {
    if (! $json) return '<span class="text-muted fst-italic" style="font-size:.82rem;">—</span>';
    $data = json_decode($json, true);
    if (! $data) return '<span class="text-muted fst-italic" style="font-size:.82rem;">—</span>';
    $subs = $checklistData[$sectionKey]['subsections'] ?? [];
    $html = '<div class="row g-2">';
    foreach ($subs as $subKey => $subInfo) {
        if (! isset($data[$subKey])) continue;
        $values     = $data[$subKey];
        $subLabel   = $subInfo['subsection_label'] ?? $subKey;
        $lainnyaVal = $data[$subKey . '_lainnya'] ?? '';
        $html .= '<div class="col-12"><div class="text-muted mb-1" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em;">'
               . esc($subLabel) . '</div>';
        $vals = is_array($values) ? $values : [$values];
        foreach ($vals as $v) {
            $display = ($v === 'Lainnya' && $lainnyaVal) ? 'Lainnya: ' . $lainnyaVal : $v;
            $html .= '<span class="badge bg-label-secondary me-1 mb-1 fw-normal" style="font-size:.78rem;">'
                   . esc($display) . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
};

$renderSectionDisplay = function(string $secKey, array $secData, ?string $json) use ($displaySavedSection, $checklistData): void {
    $sec = $secData['section']; ?>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:.65rem;overflow:hidden;">
      <div class="card-header py-2 px-3 d-flex align-items-center gap-2"
           style="background:<?= esc($sec['color']) ?>18;border-left:4px solid <?= esc($sec['color']) ?>;">
        <i class="ti <?= esc($sec['icon']) ?>" style="color:<?= esc($sec['color']) ?>;font-size:1rem;flex-shrink:0;"></i>
        <span class="fw-semibold" style="font-size:.88rem;color:<?= esc($sec['color']) ?>;">
          <?= esc($sec['huruf']) ?>. <?= esc($sec['label']) ?>
        </span>
      </div>
      <div class="card-body px-3 py-3" style="font-size:.875rem;">
        <?= $displaySavedSection($json, $checklistData, $secKey) ?>
      </div>
    </div>
<?php };

$statusNikahMap = [
    'belum_menikah' => 'Belum Menikah',
    'menikah'       => 'Menikah',
    'cerai'         => 'Cerai',
    'janda_duda'    => 'Janda/Duda',
];
$statusNikah = $statusNikahMap[$janji['status_pernikahan'] ?? ''] ?? ucwords(str_replace('_', ' ', $janji['status_pernikahan'] ?? '—'));
?>

<?= $this->section('title') ?>Detail Konseling #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
  <a href="<?= base_url('admin/janji') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Kelola Konseling
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;">
    #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?>
  </span>
  <span class="badge bg-label-<?= $sm['color'] ?> ms-1"><?= $sm['label'] ?></span>
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

<!-- Safety Alert -->
<?php if ($safety && ($safety['pikiran_mengakhiri_hidup'] === 'ya' || $safety['pernah_selfharm'] === 'ya')): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start mb-4" role="alert">
    <i class="ti tabler-alert-triangle" style="font-size:1.5rem;flex-shrink:0;margin-top:.1rem;"></i>
    <div>
      <div class="fw-bold mb-1">Safety Flag Aktif</div>
      <ul class="mb-0" style="font-size:.875rem;">
        <?php if ($safety['pikiran_mengakhiri_hidup'] === 'ya'): ?>
          <li>Mahasiswa memiliki pikiran mengakhiri hidup</li>
        <?php endif ?>
        <?php if ($safety['pernah_selfharm'] === 'ya'): ?>
          <li>Mahasiswa pernah melakukan self-harm</li>
        <?php endif ?>
      </ul>
    </div>
  </div>
<?php endif ?>

<?php if ($janji['status'] === 'selesai'): ?>

<!-- Tombol Ekspor PDF (hanya status selesai) -->
<div class="d-flex justify-content-end gap-2 mb-3">
  <a href="<?= base_url('admin/janji/' . $janji['id'] . '/pdf') ?>" target="_blank"
     class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2"
     style="font-size:.82rem;padding:.35rem .85rem;">
    <i class="ti tabler-file-type-pdf" style="font-size:1rem;"></i>
    Laporan PDF
  </a>
  <?php if ($hasil && $hasil['ada_rujukan']): ?>
  <a href="<?= base_url('admin/janji/' . $janji['id'] . '/surat-rujukan') ?>" target="_blank"
     class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-2"
     style="font-size:.82rem;padding:.35rem .85rem;">
    <i class="ti tabler-send" style="font-size:1rem;"></i>
    Surat Rujukan
  </a>
  <?php endif ?>
</div>

<?php
/* ════════════════════════════════════════════════════════════════
   LAYOUT SELESAI — tampilan seperti halaman psikolog (display only)
═══════════════════════════════════════════════════════════════ */
$asesmen_keys  = ['stressor','kerentanan','protektif','koping'];
$lanjutan_keys = ['diagnosis','intervensi','rekomendasi','prognosis'];
$asesmen_secs  = array_filter($checklistData, fn($k) => in_array($k, $asesmen_keys),  ARRAY_FILTER_USE_KEY);
$lanjutan_secs = array_filter($checklistData, fn($k) => in_array($k, $lanjutan_keys), ARRAY_FILTER_USE_KEY);

$isDisplayMode = (bool) $hasil;
?>

<!-- ── Zone: Informasi Klien ──────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-3 mt-2">
  <span class="badge px-3 py-2 d-flex align-items-center gap-1"
        style="background:#e9ecef;color:#6c757d;font-size:.74rem;font-weight:600;letter-spacing:.06em;white-space:nowrap;">
    <i class="ti tabler-info-circle" style="font-size:.85rem;"></i>
    INFORMASI KLIEN
  </span>
  <div class="flex-grow-1" style="border-top:1px dashed #ced4da;"></div>
  <small class="text-muted fst-italic" style="font-size:.72rem;white-space:nowrap;">Data dari sistem dan pengisian mahasiswa</small>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     I. INFORMASI UMUM
═══════════════════════════════════════════════════════════════ -->
<?php $secCard('I', 'Informasi Umum', 'tabler-info-circle', '#696cff', true) ?>
  <div class="card-body py-3 px-4" style="background:#f8f9fa;">
    <div class="row g-2" style="font-size:.875rem;">
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Nomor Klien (ID Konseling)</div>
        <div class="fw-semibold">#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Nama Psikolog</div>
        <div><?= esc($konselorNama ?: '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Tanggal Konseling</div>
        <div><?= $janji['tanggal_konseling'] ? date('d F Y', strtotime($janji['tanggal_konseling'])) : '—' ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Waktu Konseling</div>
        <div><?= $janji['jam_konseling'] ? date('H:i', strtotime($janji['jam_konseling'])) . ' WIB' : '—' ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Media Konseling</div>
        <?php
        $metodeMap = ['online' => 'Tatap Muka Daring (Online)', 'offline' => 'Tatap Muka Luring (Offline)', 'keduanya' => 'Online & Offline'];
        $metode    = $metodeMap[$janji['metode'] ?? ''] ?? ucfirst($janji['metode'] ?? '—');
        ?>
        <div><?= esc($metode) ?></div>
      </div>
      <?php if (! empty($janji['lokasi_link'])): ?>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Lokasi / Link</div>
        <div>
          <?php if (str_starts_with($janji['lokasi_link'], 'http')): ?>
            <a href="<?= esc($janji['lokasi_link']) ?>" target="_blank" rel="noopener" style="font-size:.875rem;"><?= esc($janji['lokasi_link']) ?></a>
          <?php else: ?>
            <?= esc($janji['lokasi_link']) ?>
          <?php endif ?>
        </div>
      </div>
      <?php endif ?>
      <?php if (! empty($janji['catatan_admin'])): ?>
      <div class="col-12">
        <div class="text-muted" style="font-size:.73rem;">Catatan Admin</div>
        <div class="mt-1 p-2 rounded-2" style="background:#f0f4ff;font-size:.82rem;"><?= nl2br(esc($janji['catatan_admin'])) ?></div>
      </div>
      <?php endif ?>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     II. IDENTITAS KLIEN
═══════════════════════════════════════════════════════════════ -->
<?php $secCard('II', 'Identitas Klien', 'tabler-user', '#28a745', true) ?>
  <div class="card-body py-3 px-4" style="background:#f8f9fa;">
    <div class="row g-2" style="font-size:.875rem;">
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Nama</div>
        <div class="fw-semibold"><?= esc($janji['name'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">NIM/NIP</div>
        <div><?= esc($janji['uniid'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Jenis Kelamin</div>
        <div class="text-capitalize"><?= esc(str_replace('-', ' ', $janji['jenis_kelamin'] ?? '—')) ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Usia</div>
        <div><?= esc($janji['usia'] ?? '—') ?> tahun</div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Status Pernikahan</div>
        <div><?= esc($statusNikah) ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Semester</div>
        <div>Semester <?= esc($janji['semester'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Fakultas</div>
        <div><?= esc($janji['fakultas'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Program Studi</div>
        <div><?= esc($janji['prodi'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Agama</div>
        <div><?= esc($janji['agama'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">No. HP</div>
        <div><?= esc($janji['phone'] ?? '—') ?></div>
      </div>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Email</div>
        <div><?= esc($janji['email'] ?? '—') ?></div>
      </div>
      <?php if (! empty($janji['pekerjaan'])): ?>
      <div class="col-sm-6 col-md-4">
        <div class="text-muted" style="font-size:.73rem;">Pekerjaan</div>
        <div><?= esc($janji['pekerjaan']) ?></div>
      </div>
      <?php endif ?>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     III. KELUHAN UTAMA
═══════════════════════════════════════════════════════════════ -->
<?php $secCard('III', 'Keluhan Utama', 'tabler-notes', '#fd7e14', true) ?>
  <div class="card-body py-3 px-4" style="background:#f8f9fa;font-size:.875rem;">
    <?php if (! empty($janji['tema_konseling'])): ?>
      <div class="mb-2">
        <span class="text-muted" style="font-size:.73rem;">Tema: </span>
        <span class="badge bg-label-primary ms-1"><?= esc(ucwords(str_replace('_', ' ', $janji['tema_konseling']))) ?></span>
        <?php if (! empty($janji['urgensi'])):
          $urgMap = ['biasa' => ['Biasa','bg-label-success'], 'cukup_urgen' => ['Cukup Urgen','bg-label-warning'], 'sangat_urgen' => ['Sangat Urgen','bg-label-danger']];
          [$urgLabel, $urgBg] = $urgMap[$janji['urgensi']] ?? [ucfirst($janji['urgensi']), 'bg-label-secondary'];
        ?>
          <span class="badge <?= $urgBg ?> ms-1"><?= $urgLabel ?></span>
        <?php endif ?>
      </div>
    <?php endif ?>
    <div class="p-3 rounded-2" style="background:#fffbf0;border-left:3px solid #fd7e14;white-space:pre-wrap;line-height:1.7;">
      <?= esc($janji['keluhan_utama'] ?? '—') ?>
    </div>
    <?php if (! empty($janji['mulai_keluhan']) || ! empty($janji['upaya_dilakukan'])): ?>
      <div class="row g-2 mt-2">
        <?php if (! empty($janji['mulai_keluhan'])): ?>
          <div class="col-sm-6">
            <span class="text-muted" style="font-size:.73rem;">Sejak: </span><?= esc($janji['mulai_keluhan']) ?>
          </div>
        <?php endif ?>
        <?php if (! empty($janji['upaya_dilakukan'])): ?>
          <div class="col-sm-6">
            <span class="text-muted" style="font-size:.73rem;">Upaya: </span><?= esc($janji['upaya_dilakukan']) ?>
          </div>
        <?php endif ?>
      </div>
    <?php endif ?>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     IV. HASIL ASESMEN AWAL (Read Only)
═══════════════════════════════════════════════════════════════ -->
<?php $secCard('IV', 'Hasil Asesmen Awal', 'tabler-chart-bar', '#dc3545', true) ?>
  <div class="card-body py-3 px-4" style="background:#f8f9fa;">

    <!-- DASS-21 -->
    <?php if ($dass):
      $dassInterpretasi = [
        'depresi' => [
          'normal'       => 'Tidak menunjukkan gejala depresi yang berarti.',
          'ringan'       => 'Terdapat gejala depresi ringan yang perlu diperhatikan.',
          'sedang'       => 'Mengalami depresi pada tingkat sedang.',
          'berat'        => 'Mengalami depresi pada tingkat berat.',
          'sangat_berat' => 'Mengalami depresi yang sangat berat dan memerlukan penanganan segera.',
        ],
        'anxiety' => [
          'normal'       => 'Tidak menunjukkan gejala ansietas yang signifikan.',
          'ringan'       => 'Terdapat gejala ansietas ringan.',
          'sedang'       => 'Mengalami ansietas pada tingkat sedang.',
          'berat'        => 'Mengalami ansietas pada tingkat berat.',
          'sangat_berat' => 'Mengalami ansietas yang sangat berat, perlu perhatian segera.',
        ],
        'stress' => [
          'normal'       => 'Tidak menunjukkan gejala stres yang berlebihan.',
          'ringan'       => 'Mengalami stres ringan yang masih dapat dikelola.',
          'sedang'       => 'Mengalami stres pada tingkat sedang.',
          'berat'        => 'Mengalami stres berat yang mengganggu fungsi sehari-hari.',
          'sangat_berat' => 'Mengalami stres yang sangat berat dan mengganggu secara signifikan.',
        ],
      ];
      $borderColors = ['depresi' => '#696cff', 'anxiety' => '#ff9f43', 'stress' => '#28c76f'];
      $dList = [
        'depresi' => ['Depresi',  $dass['skor_depresi'] ?? 0, $dass['kategori_depresi'] ?? 'normal'],
        'anxiety' => ['Ansietas', $dass['skor_anxiety'] ?? 0, $dass['kategori_anxiety'] ?? 'normal'],
        'stress'  => ['Stres',    $dass['skor_stress']  ?? 0, $dass['kategori_stress']  ?? 'normal'],
      ];
    ?>
    <div class="mb-4">
      <div class="text-muted fw-semibold mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Hasil Asesmen Awal (DASS-21)</div>
      <div class="row g-3">
        <?php foreach ($dList as $dk => [$dlabel, $dskor, $dkat]): ?>
          <div class="col-md-4">
            <div class="p-3 rounded-2 h-100" style="border-left:3px solid <?= $borderColors[$dk] ?>;background:#fff;">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="fw-semibold" style="font-size:.85rem;"><?= $dlabel ?></span>
                <span class="fw-bold" style="font-size:1.3rem;color:<?= $borderColors[$dk] ?>;"><?= $dskor ?></span>
              </div>
              <div style="font-size:.78rem;color:#555;line-height:1.5;">
                <?= esc($dassInterpretasi[$dk][$dkat] ?? ucfirst($dkat)) ?>
              </div>
            </div>
          </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Safety Screening -->
    <?php if ($safety): ?>
    <div>
      <div class="text-muted fw-semibold mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Safety Screening</div>
      <div class="row g-2" style="font-size:.82rem;">
        <?php
        $siItems = [
          ['Pernah self-harm',         'pernah_selfharm',           'ya'],
          ['Pikiran mengakhiri hidup',  'pikiran_mengakhiri_hidup',  'ya'],
          ['Pikiran yang mengganggu',   'pikiran_mengganggu',        'ya'],
          ['Merasa aman saat ini',      'merasa_aman',               'tidak'],
        ];
        foreach ($siItems as [$siLabel, $siKey, $siDanger]):
          $siVal    = $safety[$siKey] ?? '—';
          $isDanger = $siVal === $siDanger;
        ?>
          <div class="col-sm-6 d-flex justify-content-between border-bottom pb-1">
            <span class="text-muted"><?= $siLabel ?></span>
            <span class="fw-semibold text-<?= $isDanger ? 'danger' : 'success' ?>"><?= ucfirst($siVal) ?></span>
          </div>
        <?php endforeach ?>
      </div>
      <?php if (! empty($safety['riwayat_selfharm_keterangan'])): ?>
        <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded" style="font-size:.82rem;">
          <strong>Keterangan:</strong> <?= esc($safety['riwayat_selfharm_keterangan']) ?>
        </div>
      <?php endif ?>
    </div>
    <?php endif ?>

  </div>
</div>

<!-- ── Zone separator: Hasil Penilaian Psikolog ─────────────────── -->
<?php if ($isDisplayMode): ?>
<div class="d-flex align-items-center gap-3 mt-4 mb-3">
  <span class="badge px-3 py-2 d-flex align-items-center gap-1"
        style="background:#dbeafe;color:#1e40af;font-size:.74rem;font-weight:600;letter-spacing:.06em;white-space:nowrap;">
    <i class="ti tabler-clipboard-check" style="font-size:.85rem;"></i>
    HASIL PENILAIAN KONSELOR
  </span>
  <div class="flex-grow-1" style="border-top:1.5px solid #3b82f640;"></div>
  <small class="text-muted fst-italic" style="font-size:.72rem;white-space:nowrap;">Data hasil penilaian psikolog</small>
</div>
<?php endif ?>

<!-- Waktu Sesi -->
<?php if ($isDisplayMode && (! empty($hasil['jam_mulai']) || ! empty($hasil['jam_selesai']))): ?>
<div class="card mb-3" style="border-top:3px solid #0dcaf0;">
  <div class="card-header py-2 px-4 d-flex align-items-center gap-2" style="background:#0dcaf00d;">
    <i class="ti tabler-clock" style="color:#0896b0;font-size:1rem;"></i>
    <span class="fw-bold" style="font-size:.9rem;color:#0896b0;">Waktu Sesi</span>
  </div>
  <div class="card-body px-4 py-2 d-flex flex-wrap gap-4" style="font-size:.875rem;">
    <?php if (! empty($hasil['jam_mulai'])): ?>
      <div><span class="text-muted" style="font-size:.73rem;">Jam Mulai</span><br>
        <span class="fw-semibold"><?= date('H:i', strtotime($hasil['jam_mulai'])) ?> WIB</span></div>
    <?php endif ?>
    <?php if (! empty($hasil['jam_selesai'])): ?>
      <div><span class="text-muted" style="font-size:.73rem;">Jam Selesai</span><br>
        <span class="fw-semibold"><?= date('H:i', strtotime($hasil['jam_selesai'])) ?> WIB</span></div>
    <?php endif ?>
    <?php if (! empty($hasil['jam_mulai']) && ! empty($hasil['jam_selesai'])):
      $durMenit = (strtotime($hasil['jam_selesai']) - strtotime($hasil['jam_mulai'])) / 60;
      if ($durMenit > 0): ?>
        <div><span class="text-muted" style="font-size:.73rem;">Durasi</span><br>
          <span class="fw-semibold"><?= $durMenit ?> menit</span></div>
      <?php endif ?>
    <?php endif ?>
  </div>
</div>
<?php endif ?>

<!-- Checklist Asesmen (B-E display) -->
<?php if ($isDisplayMode && ! empty($asesmen_secs)): ?>
  <div class="text-muted fw-semibold mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Hasil Checklist Asesmen</div>
  <?php
  $jsnMap = [
    'stressor'   => $hasil['stressor'] ?? null,
    'kerentanan' => $hasil['faktor_kerentanan'] ?? null,
    'protektif'  => $hasil['faktor_protektif'] ?? null,
    'koping'     => $hasil['strategi_koping'] ?? null,
  ];
  foreach ($asesmen_secs as $secKey => $secData):
    $renderSectionDisplay($secKey, $secData, $jsnMap[$secKey] ?? null);
  endforeach ?>
<?php endif ?>

<!-- ═══════════════════════════════════════════════════════════════
     V. FORMULASI RINGKAS PERMASALAHAN KLIEN
═══════════════════════════════════════════════════════════════ -->
<?php $secCard('V', 'Formulasi Ringkas Permasalahan Klien', 'tabler-clipboard-list', '#6f42c1') ?>
  <div class="card-body py-3 px-4">
    <?php
    $formulasiItems = [
      'kerentanan' => ['label' => 'Faktor Kerentanan Utama (Diathesis)',         'json_col' => 'faktor_kerentanan'],
      'stressor'   => ['label' => 'Stressor atau Pemicu Utama',                  'json_col' => 'stressor'],
      'protektif'  => ['label' => 'Faktor Protektif yang Dimiliki Klien',        'json_col' => 'faktor_protektif'],
      'koping'     => ['label' => 'Strategi Coping Dominan yang Digunakan',      'json_col' => 'strategi_koping'],
    ];
    ?>
    <div class="row g-3">
      <?php foreach ($formulasiItems as $fKey => $fItem): ?>
      <div class="col-12">
        <div class="p-3 rounded-2" style="background:#faf9ff;border-left:3px solid #6f42c1;">
          <div class="text-muted mb-1" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
            <?= esc($fItem['label']) ?>
          </div>
          <?php if ($isDisplayMode): ?>
            <?= $displaySavedSection($hasil[$fItem['json_col']] ?? null, $checklistData, $fKey) ?>
          <?php else: ?>
            <span class="text-muted fst-italic" style="font-size:.82rem;">—</span>
          <?php endif ?>
        </div>
      </div>
      <?php endforeach ?>

      <!-- Manifestasi/Respons Psikologis -->
      <div class="col-12">
        <div class="p-3 rounded-2" style="background:#faf9ff;border-left:3px solid #6f42c1;">
          <div class="text-muted mb-1" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
            Manifestasi atau Respons Psikologis yang Muncul
          </div>
          <div style="font-size:.875rem;white-space:pre-wrap;line-height:1.6;">
            <?= esc($janji['keluhan_utama'] ?? '—') ?>
          </div>
        </div>
      </div>

      <!-- Kesimpulan Psikolog -->
      <div class="col-12">
        <div class="p-3 rounded-2" style="background:#f5f3ff;border-left:3px solid #6f42c1;">
          <div class="text-muted mb-2" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
            Kesimpulan Psikolog
          </div>
          <?php if ($isDisplayMode && ! empty($hasil['catatan_sesi'])): ?>
            <div style="font-size:.875rem;white-space:pre-wrap;line-height:1.7;"><?= esc($hasil['catatan_sesi']) ?></div>
          <?php else: ?>
            <span class="text-muted fst-italic" style="font-size:.82rem;">—</span>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
/* ── Sections VI-IX ────────────────────────────────────────────── */
$lanjutanMeta = [
    'diagnosis'   => ['num' => 'VI',   'icon' => 'tabler-stethoscope', 'color' => '#e83e8c'],
    'intervensi'  => ['num' => 'VII',  'icon' => 'tabler-bolt',        'color' => '#6f42c1'],
    'rekomendasi' => ['num' => 'VIII', 'icon' => 'tabler-checklist',   'color' => '#20c997'],
    'prognosis'   => ['num' => 'IX',   'icon' => 'tabler-trending-up', 'color' => '#fd7e14'],
];
$jsnLanjutan = [
    'diagnosis'   => $hasil['diagnosis']   ?? null,
    'intervensi'  => $hasil['intervensi']  ?? null,
    'rekomendasi' => $hasil['rekomendasi'] ?? null,
    'prognosis'   => $hasil['prognosis']   ?? null,
];
$lanjutanTitle = [
    'diagnosis'   => 'Diagnosis Problem Normal Bermasalah (DSM-5-TR)',
    'intervensi'  => 'Intervensi yang Diberikan',
    'rekomendasi' => 'Rekomendasi',
    'prognosis'   => 'Prognosis (Kemungkinan Perkembangan Permasalahan Klien)',
];

foreach ($lanjutan_secs as $secKey => $secData):
    $lm = $lanjutanMeta[$secKey];
    $secCard($lm['num'], $lanjutanTitle[$secKey], $lm['icon'], $lm['color']);
?>
  <div class="card-body py-3 px-4">
    <?php if ($isDisplayMode):
        $renderSectionDisplay($secKey, $secData, $jsnLanjutan[$secKey]);
    else: ?>
      <span class="text-muted fst-italic" style="font-size:.82rem;">—</span>
    <?php endif ?>
  </div>
</div>
<?php endforeach ?>

<!-- Sesi Lanjutan -->
<?php if ($isDisplayMode && $hasil): ?>
<div class="card mb-3" style="border-top:3px solid #6c757d;">
  <div class="card-body px-4 py-3 d-flex flex-wrap gap-4" style="font-size:.875rem;">
    <div>
      <span class="text-muted" style="font-size:.73rem;">Sesi Lanjutan</span><br>
      <span class="badge bg-label-<?= $hasil['sesi_lanjutan'] ? 'warning' : 'secondary' ?>">
        <?= $hasil['sesi_lanjutan'] ? 'Diperlukan' : 'Tidak Diperlukan' ?>
      </span>
    </div>
    <div>
      <span class="text-muted" style="font-size:.73rem;">Rujukan</span><br>
      <span class="badge bg-label-<?= $hasil['ada_rujukan'] ? 'danger' : 'success' ?>">
        <?= $hasil['ada_rujukan'] ? 'Ya, dirujuk' : 'Tidak dirujuk' ?>
      </span>
    </div>
  </div>
</div>
<?php endif ?>

<!-- Detail Rujukan tersimpan -->
<?php if ($isDisplayMode && $hasil && $hasil['ada_rujukan']): ?>
<div class="card shadow-sm mb-3" style="border-top:3px solid #fd7e14;">
  <div class="card-header py-2 px-4 d-flex align-items-center justify-content-between" style="background:#fff8f30d;">
    <span class="fw-bold" style="font-size:.88rem;color:#c4620a;"><i class="ti tabler-arrow-forward me-1"></i>Detail Rujukan</span>
    <a href="<?= base_url('admin/janji/' . $janji['id'] . '/surat-rujukan') ?>" target="_blank"
       class="btn btn-sm d-inline-flex align-items-center gap-1"
       style="font-size:.75rem;padding:.25rem .65rem;background:#fd7e14;color:#fff;border-radius:4px;">
      <i class="ti tabler-file-download" style="font-size:.9rem;"></i> Surat Rujukan PDF
    </a>
  </div>
  <div class="card-body px-4 py-3" style="font-size:.875rem;">
    <div class="row g-2">
      <div class="col-sm-6">
        <div class="text-muted" style="font-size:.73rem;">Instansi Rujukan</div>
        <?php
        $namaInstansi = '—';
        if (! empty($hasil['instansi_rujukan_id'])) {
            foreach ($instansiRujukan as $inst) {
                if ($inst['id'] == $hasil['instansi_rujukan_id']) {
                    $namaInstansi = $inst['singkatan'] . ' — ' . $inst['nama_instansi'];
                    break;
                }
            }
        } elseif (! empty($hasil['instansi_rujukan'])) {
            $namaInstansi = $hasil['instansi_rujukan'];
        }
        ?>
        <div><?= esc($namaInstansi) ?></div>
      </div>
      <?php if (! empty($hasil['alasan_rujukan'])): ?>
      <div class="col-sm-6">
        <div class="text-muted" style="font-size:.73rem;">Alasan Rujukan</div>
        <div><?= esc($hasil['alasan_rujukan']) ?></div>
      </div>
      <?php endif ?>
    </div>
  </div>
</div>
<?php endif ?>

<!-- Tombol kembali -->
<a href="<?= base_url('admin/janji') ?>" class="btn btn-outline-secondary btn-sm mb-4">
  <i class="ti tabler-arrow-left me-1"></i>Kembali ke Daftar
</a>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════════
     LAYOUT NON-SELESAI — 2 kolom (admin default)
═══════════════════════════════════════════════════════════════ -->
<div class="row g-4">
  <!-- Kolom Kiri: Info Mahasiswa + Keluhan -->
  <div class="col-lg-7">

    <!-- Info Mahasiswa -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-user me-2 text-primary"></i>Identitas Mahasiswa</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Nama</div>
            <div class="fw-semibold"><?= esc($janji['name'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">NIM/NIP</div>
            <div><?= esc($janji['uniid'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Email</div>
            <div><?= esc($janji['email'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">No. HP</div>
            <div><?= esc($janji['phone'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Fakultas / Prodi</div>
            <div><?= esc($janji['fakultas'] ?? '—') ?> / <?= esc($janji['prodi'] ?? '—') ?></div>
          </div>
          <div class="col-sm-3">
            <div class="text-muted" style="font-size:.75rem;">Usia / Semester</div>
            <div><?= esc($janji['usia']) ?> th / Smt <?= esc($janji['semester']) ?></div>
          </div>
          <div class="col-sm-3">
            <div class="text-muted" style="font-size:.75rem;">Agama</div>
            <div><?= esc($janji['agama']) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Jenis Kelamin</div>
            <div class="text-capitalize"><?= esc(str_replace('-', ' ', $janji['jenis_kelamin'])) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Metode</div>
            <div class="text-capitalize"><?= esc($janji['metode']) ?></div>
          </div>
          <?php if (! empty($janji['pekerjaan'])): ?>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Pekerjaan</div>
            <div><?= esc($janji['pekerjaan']) ?></div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>

    <!-- Keluhan -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-notes me-2 text-warning"></i>Keluhan & Kondisi</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php if (! empty($janji['tema_konseling'])): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Tema Konseling</div>
            <span class="badge bg-label-primary mt-1"><?= esc(ucwords(str_replace('_', ' ', $janji['tema_konseling']))) ?></span>
          </div>
          <?php endif ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Keluhan Utama</div>
            <div class="mt-1" style="font-size:.875rem;white-space:pre-wrap;"><?= esc($janji['keluhan_utama']) ?></div>
          </div>
          <?php if (! empty($janji['urgensi'])):
            $urgensiMeta = [
                'biasa'        => ['label' => 'Biasa',        'bg' => 'bg-label-success', 'icon' => 'tabler-circle'],
                'cukup_urgen'  => ['label' => 'Cukup Urgen',  'bg' => 'bg-label-warning', 'icon' => 'tabler-alert-circle'],
                'sangat_urgen' => ['label' => 'Sangat Urgen', 'bg' => 'bg-label-danger',  'icon' => 'tabler-alert-triangle'],
            ];
            $urg = $urgensiMeta[$janji['urgensi']] ?? ['label' => $janji['urgensi'], 'bg' => 'bg-label-secondary', 'icon' => 'tabler-circle'];
          ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Tingkat Urgensi</div>
            <span class="badge <?= $urg['bg'] ?> d-inline-flex align-items-center gap-1 mt-1" style="font-size:.8rem;padding:.35em .7em;">
              <i class="ti <?= $urg['icon'] ?>"></i><?= $urg['label'] ?>
            </span>
          </div>
          <?php endif ?>
          <?php if (! empty($janji['mulai_keluhan'])): ?>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Sejak Kapan</div>
            <div><?= esc($janji['mulai_keluhan']) ?></div>
          </div>
          <?php endif ?>
          <?php if (! empty($janji['upaya_dilakukan'])): ?>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Upaya Dilakukan</div>
            <div style="font-size:.875rem;"><?= esc($janji['upaya_dilakukan']) ?></div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>

    <!-- DASS -->
    <?php if ($dass): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-chart-bar me-2 text-danger"></i>Hasil DASS-21</h6>
      </div>
      <div class="card-body">
        <?php
        $dassKategori = [
            'normal'       => ['label' => 'Normal',        'color' => 'success'],
            'ringan'       => ['label' => 'Ringan',        'color' => 'info'],
            'sedang'       => ['label' => 'Sedang',        'color' => 'warning'],
            'berat'        => ['label' => 'Berat',         'color' => 'danger'],
            'sangat_berat' => ['label' => 'Sangat Berat',  'color' => 'danger'],
        ];
        $dList = [
            ['label' => 'Depresi',  'skor' => $dass['skor_depresi'] ?? 0,  'kat' => $dass['kategori_depresi'] ?? 'normal'],
            ['label' => 'Ansietas', 'skor' => $dass['skor_anxiety'] ?? 0,  'kat' => $dass['kategori_anxiety'] ?? 'normal'],
            ['label' => 'Stres',    'skor' => $dass['skor_stress'] ?? 0,   'kat' => $dass['kategori_stress']  ?? 'normal'],
        ];
        ?>
        <div class="row g-3">
          <?php foreach ($dList as $d):
            $k = $dassKategori[$d['kat']] ?? ['label' => $d['kat'], 'color' => 'secondary'];
          ?>
          <div class="col-4 text-center">
            <div class="text-muted mb-1" style="font-size:.75rem;"><?= $d['label'] ?></div>
            <div class="fw-bold fs-4"><?= $d['skor'] ?></div>
            <span class="badge bg-label-<?= $k['color'] ?>" style="font-size:.7rem;"><?= $k['label'] ?></span>
          </div>
          <?php endforeach ?>
        </div>
      </div>
    </div>
    <?php endif ?>

    <!-- Safety Screening -->
    <?php if ($safety): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-shield-check me-2 text-info"></i>Safety Screening</h6>
      </div>
      <div class="card-body">
        <div class="row g-2" style="font-size:.875rem;">
          <?php
          $safetyItems = [
              ['label' => 'Pernah self-harm',               'key' => 'pernah_selfharm',             'danger' => 'ya'],
              ['label' => 'Merasa aman saat ini',            'key' => 'merasa_aman',                 'danger' => 'tidak'],
              ['label' => 'Pikiran mengakhiri hidup',        'key' => 'pikiran_mengakhiri_hidup',    'danger' => 'ya'],
              ['label' => 'Pikiran yang mengganggu',         'key' => 'pikiran_mengganggu',          'danger' => 'ya'],
          ];
          foreach ($safetyItems as $si):
            $val     = $safety[$si['key']] ?? '—';
            $isDanger = $val === $si['danger'];
          ?>
          <div class="col-sm-6 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="text-muted"><?= $si['label'] ?></span>
            <span class="fw-semibold text-<?= $isDanger ? 'danger' : 'success' ?>">
              <?= esc(ucfirst($val)) ?>
            </span>
          </div>
          <?php endforeach ?>
        </div>
        <?php if (! empty($safety['riwayat_selfharm_keterangan'])): ?>
          <div class="mt-3 p-2 bg-danger bg-opacity-10 rounded" style="font-size:.82rem;">
            <strong>Keterangan:</strong> <?= esc($safety['riwayat_selfharm_keterangan']) ?>
          </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Hasil Konseling (baca-saja untuk admin) -->
    <?php if ($hasil): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-clipboard-check me-2 text-success"></i>Hasil Konseling</h6>
      </div>
      <div class="card-body" style="font-size:.875rem;">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Rujukan</div>
            <span class="badge bg-label-<?= $hasil['ada_rujukan'] ? 'danger' : 'success' ?>">
              <?= $hasil['ada_rujukan'] ? 'Ya, dirujuk' : 'Tidak dirujuk' ?>
            </span>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Sesi Lanjutan</div>
            <span class="badge bg-label-<?= $hasil['sesi_lanjutan'] ? 'warning' : 'secondary' ?>">
              <?= $hasil['sesi_lanjutan'] ? 'Diperlukan' : 'Tidak' ?>
            </span>
          </div>
          <?php if ($hasil['ada_rujukan'] && $hasil['instansi_rujukan']): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Instansi Rujukan</div>
            <div><?= esc($hasil['instansi_rujukan']) ?></div>
          </div>
          <?php endif ?>
          <?php if (! empty($hasil['catatan_sesi'])): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Catatan Sesi</div>
            <div class="mt-1" style="white-space:pre-wrap;"><?= esc($hasil['catatan_sesi']) ?></div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>
    <?php endif ?>

  </div>

  <!-- Kolom Kanan: Tindakan Admin -->
  <div class="col-lg-5">

    <!-- Jadwal & Psikolog Saat Ini -->
    <?php if (! empty($janji['tanggal_konseling'])): ?>
    <div class="card shadow-sm mb-4 border-primary" style="border-left:4px solid #696cff!important;">
      <div class="card-body py-3">
        <div class="text-muted mb-2" style="font-size:.75rem;">JADWAL DITETAPKAN</div>
        <div class="fw-bold mb-1">
          <i class="ti tabler-calendar me-1"></i>
          <?= date('l, d F Y', strtotime($janji['tanggal_konseling'])) ?>
        </div>
        <?php if ($janji['jam_konseling']): ?>
          <div class="text-muted mb-1"><i class="ti tabler-clock me-1"></i><?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB</div>
        <?php endif ?>
        <?php if (! empty($janji['lokasi_link'])): ?>
          <div class="text-muted"><i class="ti tabler-map-pin me-1"></i><?= esc($janji['lokasi_link']) ?></div>
        <?php endif ?>
        <?php if ($konselorNama): ?>
          <hr class="my-2">
          <div class="text-muted" style="font-size:.75rem;">Psikolog</div>
          <div class="fw-semibold"><?= esc($konselorNama) ?></div>
        <?php endif ?>
        <?php if (! empty($janji['mahasiswa_konfirmasi_at'])): ?>
          <div class="mt-2 text-success" style="font-size:.78rem;">
            <i class="ti tabler-circle-check me-1"></i>
            Dikonfirmasi mahasiswa: <?= date('d M Y H:i', strtotime($janji['mahasiswa_konfirmasi_at'])) ?>
          </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Preferensi Jadwal & Psikolog Mahasiswa -->
    <?php
    $jadwalPilihan2 = $janji['jadwal_pilihan'] ?? [];
    if (! is_array($jadwalPilihan2)) $jadwalPilihan2 = json_decode($jadwalPilihan2, true) ?: [];
    $adaPreferensi = ! empty($jadwalPilihan2) || ! empty($konselorPilihanList);
    ?>
    <?php if ($adaPreferensi): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:.85rem;">
          <i class="ti tabler-adjustments-horizontal me-2 text-info"></i>Preferensi Mahasiswa
        </h6>
      </div>
      <div class="card-body py-3">

        <?php if (! empty($jadwalPilihan2)): ?>
          <div class="mb-3">
            <div class="text-muted mb-2" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
              <i class="ti tabler-clock me-1"></i>Jadwal yang Dipilih
            </div>
            <?php foreach ($jadwalPilihan2 as $jItem):
              $jMetode   = $jItem['metode']   ?? null;
              $jTanggal  = $jItem['tanggal']  ?? null;
              [$jmc, $jmi, $jml] = match ($jMetode) {
                'online'   => ['bg-label-info',    'tabler-video',           'Online'],
                'offline'  => ['bg-label-success', 'tabler-map-pin',         'Offline'],
                'keduanya' => ['bg-label-primary',  'tabler-arrows-exchange', 'Online & Offline'],
                default    => ['', '', ''],
              };
            ?>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($jTanggal): ?>
                  <span class="badge bg-label-secondary px-3 py-2" style="font-size:.8rem;">
                    <i class="ti tabler-calendar me-1"></i>
                    <?= date('d M Y', strtotime($jTanggal)) ?>
                  </span>
                <?php endif ?>
                <span class="badge bg-label-primary px-3 py-2" style="font-size:.8rem;">
                  <i class="ti tabler-calendar-week me-1"></i>
                  <?= ucfirst($jItem['hari']) ?> — <?= esc($jItem['waktu']) ?>
                </span>
                <?php if ($jmc): ?>
                  <span class="badge <?= $jmc ?> px-3 py-2" style="font-size:.8rem;">
                    <i class="ti <?= $jmi ?> me-1"></i><?= $jml ?>
                  </span>
                <?php endif ?>
              </div>
            <?php endforeach ?>
          </div>
        <?php endif ?>

        <?php if (! empty($konselorPilihanList)): ?>
          <?php if (! empty($jadwalPilihan2)): ?>
            <hr class="my-2">
          <?php endif ?>
          <div>
            <div class="text-muted mb-2" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">
              <i class="ti tabler-user-check me-1"></i>Psikolog yang Dipilih
            </div>
            <?php foreach ($konselorPilihanList as $kNama): ?>
              <div class="d-flex align-items-center gap-2 py-1">
                <i class="ti tabler-user text-info"></i>
                <span style="font-size:.875rem;"><?= esc($kNama) ?></span>
              </div>
            <?php endforeach ?>
          </div>
        <?php endif ?>

      </div>
    </div>
    <?php endif ?>

    <!-- Form Tetapkan Jadwal -->
    <?php if (in_array($janji['status'], ['menunggu', 'dikonfirmasi'])): ?>
    <div class="card mb-4" style="border:2px solid #696cff;border-radius:.75rem;box-shadow:0 4px 20px rgba(105,108,255,.15);">
      <div class="card-header py-3" style="background:linear-gradient(135deg,#696cff 0%,#9155fd 100%);border-radius:.6rem .6rem 0 0;">
        <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
          <i class="ti tabler-calendar-plus" style="font-size:1.1rem;"></i>
          <?= $janji['status'] === 'dikonfirmasi' ? 'Ubah Jadwal & Psikolog' : 'Tetapkan Jadwal & Psikolog' ?>
        </h6>
        <div class="text-white mt-1" style="font-size:.75rem;opacity:.85;">Lengkapi form berikut lalu simpan</div>
      </div>
      <div class="card-body pt-4">
        <form action="<?= base_url('admin/janji/proses/' . $janji['id']) ?>" method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Psikolog <span class="text-danger">*</span></label>
            <select name="konselor_id" id="konselorSelect" class="form-select form-select-sm" required>
              <option value="">— Pilih Psikolog —</option>
              <?php foreach ($konselorList as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($janji['konselor_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                  <?= esc(KonselorModel::namaLengkap($k)) ?>
                </option>
              <?php endforeach ?>
            </select>
            <div id="jadwalHariInfo" class="mt-1" style="font-size:.73rem;color:#666;display:none;">
              <i class="ti tabler-calendar-week me-1"></i>
              Hari tersedia: <span id="hariTersedia" class="fw-semibold"></span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_konseling" id="tanggalInput" class="form-control form-control-sm"
                   value="<?= esc($janji['tanggal_konseling'] ?? '') ?>" required
                   min="<?= date('Y-m-d') ?>">
            <div id="tanggalWarning" class="form-text text-danger" style="display:none;">
              <i class="ti tabler-alert-triangle me-1"></i>Psikolog tidak tersedia pada hari yang dipilih.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Sesi / Jam <span class="text-danger">*</span></label>
            <select name="jam_konseling" id="jamSelect" class="form-select form-select-sm" required>
              <option value="">— Pilih psikolog &amp; tanggal dulu —</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Lokasi / Link Meeting</label>
            <input type="text" name="lokasi_link" class="form-control form-control-sm"
                   placeholder="cth: Ruang BK Lt. 2 / https://meet.google.com/..."
                   value="<?= esc($janji['lokasi_link'] ?? '') ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Catatan untuk Mahasiswa</label>
            <textarea name="catatan_admin" class="form-control form-control-sm" rows="2"
                      placeholder="Informasi tambahan..."><?= esc($janji['catatan_admin'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-sm w-100 fw-semibold"
                  style="background:linear-gradient(135deg,#696cff 0%,#9155fd 100%);color:#fff;padding:.55rem;letter-spacing:.01em;">
            <i class="ti tabler-calendar-check me-1"></i>Tetapkan Jadwal
          </button>
        </form>
      </div>
    </div>
    <?php endif ?>

    <!-- Aksi Status -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-settings me-2"></i>Aksi</h6>
      </div>
      <div class="card-body d-flex flex-column gap-2">

        <?php if ($janji['status'] === 'dikonfirmasi'): ?>
          <form id="formKonfirmasiKehadiran"
                action="<?= base_url('admin/janji/konfirmasi-kehadiran/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-info btn-sm w-100 text-white"
                    data-bs-toggle="modal" data-bs-target="#modalKonfirmasiKehadiran">
              <i class="ti tabler-circle-check me-1"></i>Konfirmasi Kehadiran Mahasiswa
            </button>
          </form>
        <?php endif ?>

        <?php if ($janji['status'] === 'terjadwal'): ?>
          <form action="<?= base_url('admin/janji/mulai/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success btn-sm w-100">
              <i class="ti tabler-player-play me-1"></i>Tandai Berlangsung
            </button>
          </form>
        <?php endif ?>

        <?php if (! in_array($janji['status'], ['selesai', 'dibatalkan'])): ?>
          <form id="formBatalkanKonseling"
                action="<?= base_url('admin/janji/batal/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                    data-bs-toggle="modal" data-bs-target="#modalBatalkanKonseling">
              <i class="ti tabler-ban me-1"></i>Batalkan Konseling
            </button>
          </form>
        <?php endif ?>

        <a href="<?= base_url('admin/janji') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="ti tabler-arrow-left me-1"></i>Kembali ke Daftar
        </a>
      </div>
    </div>

  </div>
</div>
<?php endif ?>

<?php if ($janji['status'] === 'dikonfirmasi'): ?>
<!-- Modal: Konfirmasi Kehadiran -->
<div class="modal fade" id="modalKonfirmasiKehadiran" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold">Konfirmasi Kehadiran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0">Konfirmasi kehadiran mahasiswa <strong><?= esc($janji['name'] ?? '') ?></strong> atas nama mahasiswa?<br>
        <span class="text-muted" style="font-size:.875rem;">Status akan berubah menjadi <strong>Terjadwal</strong>.</span></p>
      </div>
      <div class="modal-footer border-0 pt-1">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-info btn-sm text-white"
                onclick="document.getElementById('formKonfirmasiKehadiran').submit()">
          <i class="ti tabler-circle-check me-1"></i>Ya, Konfirmasi
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif ?>

<?php if (! in_array($janji['status'], ['selesai', 'dibatalkan'])): ?>
<!-- Modal: Batalkan Konseling -->
<div class="modal fade" id="modalBatalkanKonseling" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold text-danger">Batalkan Konseling</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0">Yakin ingin membatalkan sesi konseling <strong><?= esc($janji['name'] ?? '') ?></strong>?<br>
        <span class="text-muted" style="font-size:.875rem;">Tindakan ini tidak dapat dibatalkan.</span></p>
      </div>
      <div class="modal-footer border-0 pt-1">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Kembali</button>
        <button type="button" class="btn btn-danger btn-sm"
                onclick="document.getElementById('formBatalkanKonseling').submit()">
          <i class="ti tabler-ban me-1"></i>Ya, Batalkan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<?php if ($janji['status'] !== 'selesai'): ?>
<?php $konselorJadwalMap = $konselorJadwalMap ?? []; ?>
<script>
(function () {
  'use strict';

  const ALL_JADWAL = <?= json_encode($konselorJadwalMap) ?>;
  const INIT_JAM   = '<?= esc($janji['jam_konseling'] ?? '') ?>';

  const SLOTS = {
    s1: { jam: '08:00:00', label: '08.00 – 09.00 WIB' },
    s2: { jam: '09:30:00', label: '09.30 – 10.30 WIB' },
    s3: { jam: '11:00:00', label: '11.00 – 12.00 WIB' },
    s4: { jam: '12:30:00', label: '12.30 – 13.30 WIB' },
    s5: { jam: '14:00:00', label: '14.00 – 15.00 WIB' },
  };
  const METODE_LABEL = { online: 'Online', offline: 'Offline', keduanya: 'Online & Offline' };
  const DAY_KEYS     = ['minggu','senin','selasa','rabu','kamis','jumat','sabtu'];
  const DAY_LABELS   = ['Minggu','Senin','Selasa','Rabu','Kamis',"Jum'at",'Sabtu'];

  const konselorEl = document.getElementById('konselorSelect');
  const tanggalEl  = document.getElementById('tanggalInput');
  const jamEl      = document.getElementById('jamSelect');
  const hariInfoEl = document.getElementById('jadwalHariInfo');
  const hariTextEl = document.getElementById('hariTersedia');
  const warnEl     = document.getElementById('tanggalWarning');

  function getJadwalData() {
    const id = parseInt(konselorEl.value);
    return id ? (ALL_JADWAL[id] || {}) : {};
  }

  function getSelectedHari() {
    if (!tanggalEl.value) return null;
    const d = new Date(tanggalEl.value + 'T00:00:00');
    return DAY_KEYS[d.getDay()];
  }

  function updateHariInfo() {
    const jadwal   = getJadwalData();
    const hariList = Object.keys(jadwal);
    if (!hariList.length) { hariInfoEl.style.display = 'none'; return; }
    const labels = hariList.map(h => DAY_LABELS[DAY_KEYS.indexOf(h)] || h);
    hariTextEl.textContent = labels.join(', ');
    hariInfoEl.style.display = '';
  }

  function renderJamOptions() {
    const jadwal    = getJadwalData();
    const hari      = getSelectedHari();
    const hasData   = Object.keys(jadwal).length > 0;
    const hariSlots = hari ? (jadwal[hari] || null) : null;

    if (konselorEl.value && hasData && hari && hari !== 'minggu' && !jadwal[hari]) {
      warnEl.style.display = '';
    } else {
      warnEl.style.display = 'none';
    }

    jamEl.innerHTML = '';

    if (!konselorEl.value || !hari) {
      jamEl.add(new Option('— Pilih psikolog & tanggal dulu —', ''));
      return;
    }

    if (!hariSlots) {
      jamEl.add(new Option('— Psikolog tidak tersedia di hari ini —', ''));
      return;
    }

    jamEl.add(new Option('— Pilih sesi —', ''));

    let hasSlot = false;
    Object.entries(SLOTS).forEach(([key, slot]) => {
      if (!hariSlots[key] && hariSlots[key] !== 0) return;
      hasSlot = true;
      const metode    = hariSlots[key];
      const metodeStr = metode ? ` · ${METODE_LABEL[metode] || metode}` : '';
      const opt       = new Option(slot.label + metodeStr, slot.jam);
      if (INIT_JAM && (INIT_JAM === slot.jam || INIT_JAM.startsWith(slot.jam.substring(0, 5)))) {
        opt.selected = true;
      }
      jamEl.add(opt);
    });

    if (!hasSlot) {
      jamEl.innerHTML = '';
      jamEl.add(new Option('— Tidak ada sesi di hari ini —', ''));
    }
  }

  konselorEl.addEventListener('change', () => { updateHariInfo(); renderJamOptions(); });
  tanggalEl.addEventListener('change', renderJamOptions);

  updateHariInfo();
  renderJamOptions();
})();
</script>
<?php endif ?>
<?= $this->endSection() ?>
