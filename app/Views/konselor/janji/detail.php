<?= $this->extend('layouts/dashboard') ?>
<?php
$janji           = $janji ?? [];
$safety          = $safety ?? null;
$dass            = $dass ?? null;
$hasil           = $hasil ?? null;
$instansiRujukan = $instansiRujukan ?? [];
$checklistData   = $checklistData ?? [];
$editMode        = $editMode ?? false;

/* ── Nama psikolog lengkap dengan gelar ─────────────────────────── */
$namaKonselor = trim(
    (! empty($konselor['gelar_depan'])  ? $konselor['gelar_depan'] . ' ' : '')
    . ($konselor['name'] ?? '')
    . (! empty($konselor['gelar_belakang']) ? ', ' . $konselor['gelar_belakang'] : '')
);
$noSTR = $konselor['no_str'] ?? '—';

/* ── Split checklistData → bagian asesmen (B-E) vs lanjutan (VI-IX) */
$asesmen_keys  = ['stressor','kerentanan','protektif','koping'];
$lanjutan_keys = ['diagnosis','intervensi','rekomendasi','prognosis'];
$asesmen_secs  = array_filter($checklistData, fn($k) => in_array($k, $asesmen_keys),  ARRAY_FILTER_USE_KEY);
$lanjutan_secs = array_filter($checklistData, fn($k) => in_array($k, $lanjutan_keys), ARRAY_FILTER_USE_KEY);

/* ── Helper: render header kartu section ────────────────────────── */
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

/* ── Helper: decode saved JSON + render badges ───────────────────── */
$displaySavedSection = function(?string $json, array $checklistData, string $sectionKey): string {
    if (! $json) return '<span class="text-muted fst-italic" style="font-size:.82rem;">—</span>';
    $data = json_decode($json, true);
    if (! $data) return '<span class="text-muted fst-italic" style="font-size:.82rem;">—</span>';

    $subs  = $checklistData[$sectionKey]['subsections'] ?? [];
    $html  = '<div class="row g-2">';
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

/* ── Helper: render satu grup item checklist (form) ─────────────── */
$ckGroup = function(string $fieldName, string $subKey, array $dbItems, string $inputType = 'checkbox', array $savedData = []) {
    $idPfx        = preg_replace('/[^\w]/', '_', $fieldName . '_' . $subKey);
    $textName     = htmlspecialchars($fieldName . '[' . $subKey . '_lainnya]');
    $savedVals    = $savedData[$subKey] ?? [];
    if (! is_array($savedVals)) $savedVals = [$savedVals];
    $savedLainnya = $savedData[$subKey . '_lainnya'] ?? '';
    $html         = '';
    foreach ($dbItems as $row) {
        $uid       = $idPfx . '_' . $row['id'];
        $type      = $inputType === 'radio' ? 'radio' : 'checkbox';
        $rname     = $type === 'radio'
            ? htmlspecialchars($fieldName . '[' . $subKey . ']')
            : htmlspecialchars($fieldName . '[' . $subKey . '][]');
        $val       = htmlspecialchars($row['item_label']);
        $isLainnya = trim($row['item_label']) === 'Lainnya';
        $chk       = in_array($row['item_label'], $savedVals) ? ' checked' : '';

        if ($isLainnya) {
            $txtDisp = ($chk && $savedLainnya) ? 'inline-block' : 'none';
            $html .= '<div class="form-check mb-1 d-flex align-items-center flex-wrap gap-1 lainnya-row">'
                   . '<input class="form-check-input flex-shrink-0" type="' . $type . '" name="' . $rname . '" id="' . $uid . '" value="' . $val . '"' . $chk . '>'
                   . '<label class="form-check-label" for="' . $uid . '" style="font-size:.82rem;">Lainnya:</label>'
                   . '<input type="text" name="' . $textName . '" class="form-control form-control-sm lainnya-text ms-1"'
                   . ' style="width:180px;display:' . $txtDisp . ';font-size:.8rem;" placeholder="sebutkan..."'
                   . ' value="' . htmlspecialchars($savedLainnya) . '">'
                   . '</div>';
        } else {
            $html .= '<div class="form-check mb-1">'
                   . '<input class="form-check-input" type="' . $type . '" name="' . $rname . '" id="' . $uid . '" value="' . $val . '"' . $chk . '>'
                   . '<label class="form-check-label" for="' . $uid . '" style="font-size:.82rem;">' . $row['item_label'] . '</label>'
                   . '</div>';
        }
    }
    return $html;
};

/* ── Helper: render satu blok section checklist (form) ──────────── */
$renderSectionForm = function(string $secKey, array $secData, array $savedData = []) use ($ckGroup): void {
    $sec = $secData['section']; ?>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:.65rem;overflow:hidden;">
      <div class="card-header py-2 px-3 d-flex align-items-center gap-2"
           style="background:<?= esc($sec['color']) ?>18;border-left:4px solid <?= esc($sec['color']) ?>;">
        <i class="ti <?= esc($sec['icon']) ?>" style="color:<?= esc($sec['color']) ?>;font-size:1rem;flex-shrink:0;"></i>
        <span class="fw-semibold" style="font-size:.88rem;color:<?= esc($sec['color']) ?>;">
          <?= esc($sec['huruf']) ?>. <?= esc($sec['label']) ?>
        </span>
      </div>
      <div class="card-body px-3 py-3">
        <div class="row g-3">
          <?php foreach ($secData['subsections'] as $subKey => $grp): ?>
            <div class="col-md-6">
              <div class="fw-semibold mb-2" style="font-size:.8rem;color:#444;border-bottom:1px solid #eee;padding-bottom:.35rem;">
                <?= esc($grp['subsection_label']) ?>
                <?php if ($grp['input_type'] === 'radio'): ?>
                  <em class="fw-normal text-muted"> — pilih satu</em>
                <?php else: ?>
                  <em class="fw-normal text-muted"> — boleh lebih dari satu</em>
                <?php endif ?>
              </div>
              <?= $ckGroup($secKey, $subKey, $grp['items'], $grp['input_type'], $savedData) ?>
            </div>
          <?php endforeach ?>
        </div>
      </div>
    </div>
<?php };

/* ── Helper: render section hasil tersimpan (read-only) ─────────── */
$renderSectionDisplay = function(string $secKey, array $secData, ?string $json) use ($displaySavedSection, $checklistData): void {
    $sec = $secData['section'];
    ?>
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

$statusMeta = [
    'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'color' => 'info'],
    'terjadwal'    => ['label' => 'Terjadwal',    'color' => 'primary'],
    'berlangsung'  => ['label' => 'Berlangsung',  'color' => 'success'],
    'selesai'      => ['label' => 'Selesai',      'color' => 'dark'],
    'dibatalkan'   => ['label' => 'Dibatalkan',   'color' => 'danger'],
];
$sm = $statusMeta[$janji['status']] ?? ['label' => $janji['status'], 'color' => 'secondary'];

/* ── Status pernikahan map ───────────────────────────────────────── */
$statusNikahMap = [
    'belum_menikah' => 'Belum Menikah',
    'menikah'       => 'Menikah',
    'cerai'         => 'Cerai',
    'janda_duda'    => 'Janda/Duda',
];
$statusNikah = $statusNikahMap[$janji['status_pernikahan'] ?? ''] ?? ucwords(str_replace('_', ' ', $janji['status_pernikahan'] ?? '—'));
?>

<?= $this->section('title') ?>Detail Sesi #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>
<?= $this->section('extra_css') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= base_url('konselor/janji') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Sesi Saya
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;">#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></span>
  <span class="badge bg-label-<?= $sm['color'] ?> ms-1"><?= $sm['label'] ?></span>
</div>

<!-- Flash messages -->
<?php if ($msg = session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-3">
    <i class="ti tabler-circle-check me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-3">
    <i class="ti tabler-alert-circle me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Safety Alert -->
<?php if ($safety && ($safety['pikiran_mengakhiri_hidup'] === 'ya' || $safety['pernah_selfharm'] === 'ya')): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start mb-3">
    <i class="ti tabler-alert-triangle" style="font-size:1.5rem;flex-shrink:0;"></i>
    <div>
      <div class="fw-bold mb-1">Perhatian: Safety Flag Aktif</div>
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

<!-- Tombol aksi status terjadwal -->
<?php if ($janji['status'] === 'terjadwal'): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body text-center py-4">
      <i class="ti tabler-player-play" style="font-size:2.5rem;color:#696cff;display:block;margin-bottom:.5rem;"></i>
      <div class="fw-semibold mb-2">Sesi siap dimulai</div>
      <p class="text-muted mb-3" style="font-size:.875rem;">Konfirmasi kehadiran klien sebelum memulai sesi.</p>
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <form action="<?= base_url('konselor/janji/mulai/' . $janji['id']) ?>" method="post">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary">
            <i class="ti tabler-user-check me-1"></i>Klien Hadir, Mulai Sesi
          </button>
        </form>
        <form action="<?= base_url('konselor/janji/tidak-hadir/' . $janji['id']) ?>" method="post"
              onsubmit="return confirm('Yakin klien tidak hadir? Sesi ini akan dibatalkan.')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-danger">
            <i class="ti tabler-user-off me-1"></i>Klien Tidak Hadir
          </button>
        </form>
      </div>
    </div>
  </div>
<?php endif ?>

<?php if ($janji['status'] === 'selesai'): ?>
<!-- Tombol Ekspor PDF -->
<div class="d-flex justify-content-end gap-2 mb-3">
  <a href="<?= base_url('konselor/janji/' . $janji['id'] . '/pdf') ?>" target="_blank"
     class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2"
     style="font-size:.82rem;padding:.35rem .85rem;">
    <i class="ti tabler-file-type-pdf" style="font-size:1rem;"></i>
    Laporan PDF
  </a>
  <?php if ($hasil && $hasil['ada_rujukan']): ?>
  <a href="<?= base_url('konselor/janji/' . $janji['id'] . '/surat-rujukan') ?>" target="_blank"
     class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-2"
     style="font-size:.82rem;padding:.35rem .85rem;">
    <i class="ti tabler-send" style="font-size:1rem;"></i>
    Surat Rujukan
  </a>
  <?php endif ?>
</div>
<?php endif ?>

<?php
/* ════════════════════════════════════════════════════════════════
   Tentukan apakah ini MODE FORM, EDIT, atau DISPLAY
   • form_mode    : berlangsung && belum ada hasil
   • edit_mode    : selesai && hasil ada && ?edit=1
   • display_mode : hasil ada && bukan edit mode
═══════════════════════════════════════════════════════════════ */
$isFormMode    = ($janji['status'] === 'berlangsung' && ! $hasil);
$isEditMode    = ($editMode && (bool) $hasil && $janji['status'] === 'selesai');
$isDisplayMode = ((bool) $hasil && ! $isEditMode);
$isInputMode   = $isFormMode || $isEditMode;

/* ── Buka <form> jika form/edit mode ────────────────────────────── */
if ($isFormMode): ?>
<form action="<?= base_url('konselor/janji/hasil/' . $janji['id']) ?>" method="post" id="hasilForm">
  <?= csrf_field() ?>
<?php elseif ($isEditMode): ?>
<form action="<?= base_url('konselor/janji/edit-hasil/' . $janji['id']) ?>" method="post" id="hasilForm">
  <?= csrf_field() ?>
<?php endif ?>

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
        <div><?= esc($namaKonselor ?: '—') ?></div>
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
        <div class="text-muted" style="font-size:.73rem;">NIM</div>
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
            <div class="p-3 rounded-2 h-100" style="border-left:3px solid <?= $borderColors[$dk] ?>;background:#f8f9fa;">
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

    <!-- Safety Screening (ringkas) -->
    <?php if ($safety && array_filter([$safety['pernah_selfharm'] ?? null, $safety['pikiran_mengakhiri_hidup'] ?? null, $safety['pikiran_mengganggu'] ?? null], fn($v) => $v === 'ya')): ?>
    <div class="mb-4">
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
          $siVal     = $safety[$siKey] ?? '—';
          $isDanger  = $siVal === $siDanger;
        ?>
          <div class="col-sm-6 d-flex justify-content-between border-bottom pb-1">
            <span class="text-muted"><?= $siLabel ?></span>
            <span class="fw-semibold text-<?= $isDanger ? 'danger' : 'success' ?>"><?= ucfirst($siVal) ?></span>
          </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

  </div>
</div>

<!-- ── Zone separator ──────────────────────────────────────────── -->
<?php if ($isFormMode || $isDisplayMode || $isEditMode): ?>
<div class="d-flex align-items-center gap-3 mt-4 mb-3">
  <span class="badge px-3 py-2 d-flex align-items-center gap-1"
        style="background:<?= $isInputMode ? '#d1e7dd' : '#dbeafe' ?>;color:<?= $isInputMode ? '#0a5c36' : '#1e40af' ?>;font-size:.74rem;font-weight:600;letter-spacing:.06em;white-space:nowrap;">
    <i class="ti <?= $isInputMode ? 'tabler-pencil' : 'tabler-clipboard-check' ?>" style="font-size:.85rem;"></i>
    <?= $isFormMode ? 'FORM ISIAN KONSELOR' : ($isEditMode ? 'EDIT HASIL KONSELOR' : 'HASIL PENILAIAN KONSELOR') ?>
  </span>
  <div class="flex-grow-1" style="border-top:1.5px solid <?= $isInputMode ? '#0d6efd40' : '#3b82f640' ?>;"></div>
  <?php if ($isDisplayMode): ?>
    <a href="<?= base_url('konselor/janji/' . $janji['id']) ?>?edit=1"
       class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
       style="font-size:.78rem;padding:.28rem .7rem;">
      <i class="ti tabler-edit" style="font-size:.9rem;"></i> Edit Hasil
    </a>
  <?php elseif ($isEditMode): ?>
    <a href="<?= base_url('konselor/janji/' . $janji['id']) ?>"
       class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
       style="font-size:.78rem;padding:.28rem .7rem;">
      <i class="ti tabler-x" style="font-size:.9rem;"></i> Batal Edit
    </a>
  <?php else: ?>
    <small class="text-muted fst-italic" style="font-size:.72rem;white-space:nowrap;">Bagian ini harus diisi oleh psikolog</small>
  <?php endif ?>
</div>
<?php endif ?>

<!-- Jam Mulai — awal form -->
<?php if ($isInputMode): ?>
<div class="card shadow-sm mb-3" style="border-top:3px solid #0dcaf0;">
  <div class="card-header py-2 px-4 d-flex align-items-center gap-2" style="background:#0dcaf00d;">
    <i class="ti tabler-clock-play" style="color:#0896b0;font-size:1rem;"></i>
    <span class="fw-bold" style="font-size:.9rem;color:#0896b0;">Jam Mulai Sesi</span>
  </div>
  <div class="card-body px-4 py-3">
    <div class="col-sm-4 col-md-3">
      <input type="time" name="jam_mulai" class="form-control form-control-sm"
             value="<?= esc($hasil['jam_mulai'] ?? '') ?>">
      <div class="text-muted mt-1" style="font-size:.75rem;">Catat waktu sesi dimulai</div>
    </div>
  </div>
</div>
<?php elseif ($isDisplayMode && (! empty($hasil['jam_mulai']) || ! empty($hasil['jam_selesai']))): ?>
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

<!-- B-E: FORM inputs atau DISPLAY tersimpan -->
<?php if ($isFormMode): ?>
  <?php foreach ($asesmen_secs as $secKey => $secData):
    $renderSectionForm($secKey, $secData);
  endforeach ?>

<?php elseif ($isEditMode): ?>
  <?php
  $asmSavedMap = [
    'stressor'   => json_decode($hasil['stressor']          ?? '{}', true) ?? [],
    'kerentanan' => json_decode($hasil['faktor_kerentanan'] ?? '{}', true) ?? [],
    'protektif'  => json_decode($hasil['faktor_protektif']  ?? '{}', true) ?? [],
    'koping'     => json_decode($hasil['strategi_koping']   ?? '{}', true) ?? [],
  ];
  foreach ($asesmen_secs as $secKey => $secData):
    $renderSectionForm($secKey, $secData, $asmSavedMap[$secKey] ?? []);
  endforeach ?>

<?php elseif ($isDisplayMode): ?>
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
    /* ── label & source mapping ────────────────────────────────── */
    $formulasiItems = [
      'kerentanan' => ['label' => 'Faktor Kerentanan Utama (Diathesis)',         'json_col' => 'faktor_kerentanan', 'js_key' => 'kerentanan'],
      'stressor'   => ['label' => 'Stressor atau Pemicu Utama',                  'json_col' => 'stressor',          'js_key' => 'stressor'],
      'protektif'  => ['label' => 'Faktor Protektif yang Dimiliki Klien',        'json_col' => 'faktor_protektif',  'js_key' => 'protektif'],
      'koping'     => ['label' => 'Strategi Coping Dominan yang Digunakan',      'json_col' => 'strategi_koping',   'js_key' => 'koping'],
    ];
    ?>
    <div class="row g-3">

      <?php foreach ($formulasiItems as $fKey => $fItem): ?>
      <div class="col-12">
        <div class="p-3 rounded-2" style="background:#faf9ff;border-left:3px solid #6f42c1;">
          <div class="text-muted mb-1" style="font-size:.73rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
            <?= esc($fItem['label']) ?>
          </div>
          <?php if ($isInputMode): ?>
            <div id="formulasi_<?= $fKey ?>" style="min-height:1.4rem;">
              <span class="text-muted fst-italic" style="font-size:.82rem;">akan terisi otomatis dari pilihan di atas</span>
            </div>
          <?php elseif ($isDisplayMode): ?>
            <?= $displaySavedSection($hasil[$fItem['json_col']] ?? null, $checklistData, $fKey) ?>
          <?php endif ?>
        </div>
      </div>
      <?php endforeach ?>

      <!-- Manifestasi/Respons Psikologis — dari keluhan utama -->
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
            <span class="fw-normal text-lowercase" style="letter-spacing:0;">: dinamika kaitan faktor — intervensi — penilaian respon klien</span>
          </div>
          <?php if ($isInputMode): ?>
            <textarea name="catatan_sesi" id="catatanSesiInput" class="form-control" rows="4"
                      maxlength="450" placeholder="Tuliskan kesimpulan secara ringkas..."
                      oninput="updateCatatanCounter(this)"><?= esc($hasil['catatan_sesi'] ?? '') ?></textarea>
            <div class="d-flex justify-content-end mt-1">
              <small id="catatanCounter" class="text-muted">0 / 450</small>
            </div>
          <?php elseif ($isDisplayMode && ! empty($hasil['catatan_sesi'])): ?>
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
/* ── Sections VI-IX ───────────────────────────────────────────── */
/* Map: section_key → roman numeral heading */
$lanjutanMeta = [
    'diagnosis'  => ['num' => 'VI',   'icon' => 'tabler-stethoscope', 'color' => '#e83e8c'],
    'intervensi' => ['num' => 'VII',  'icon' => 'tabler-bolt',        'color' => '#6f42c1'],
    'rekomendasi'=> ['num' => 'VIII', 'icon' => 'tabler-checklist',   'color' => '#20c997'],
    'prognosis'  => ['num' => 'IX',   'icon' => 'tabler-trending-up', 'color' => '#fd7e14'],
];
$jsnLanjutan = [
    'diagnosis'  => $hasil['diagnosis']   ?? null,
    'intervensi' => $hasil['intervensi']  ?? null,
    'rekomendasi'=> $hasil['rekomendasi'] ?? null,
    'prognosis'  => $hasil['prognosis']   ?? null,
];
$lanjutanTitle = [
    'diagnosis'  => 'Diagnosis Problem Normal Bermasalah (DSM-5-TR)',
    'intervensi' => 'Intervensi yang Diberikan',
    'rekomendasi'=> 'Rekomendasi',
    'prognosis'  => 'Prognosis (Kemungkinan Perkembangan Permasalahan Klien)',
];

foreach ($lanjutan_secs as $secKey => $secData):
    $lm = $lanjutanMeta[$secKey];
    $secCard($lm['num'], $lanjutanTitle[$secKey], $lm['icon'], $lm['color']);
?>
  <div class="card-body py-3 px-4">
    <?php if ($isFormMode):
        $renderSectionForm($secKey, $secData);
    elseif ($isEditMode):
        $lnjSaved = json_decode($jsnLanjutan[$secKey] ?? '{}', true) ?? [];
        $renderSectionForm($secKey, $secData, $lnjSaved);
    elseif ($isDisplayMode):
        $renderSectionDisplay($secKey, $secData, $jsnLanjutan[$secKey]);
    else: ?>
      <span class="text-muted fst-italic" style="font-size:.82rem;">—</span>
    <?php endif ?>
  </div>
</div>

<?php endforeach ?>

<?php if ($isInputMode): ?>
  <!-- Detail Rujukan (tampil saat prognosis = Memerlukan rujukan lanjutan) -->
  <?php
  $editRujukanShow    = $isEditMode && ($hasil['ada_rujukan'] ?? 0);
  $editInstansiId     = $isEditMode ? ($hasil['instansi_rujukan_id'] ?? null) : null;
  $editInstansiManual = $isEditMode ? ($hasil['instansi_rujukan'] ?? '') : '';
  $editAlasan         = $isEditMode ? ($hasil['alasan_rujukan'] ?? '') : '';
  $showLainnyaInput   = $isEditMode && empty($editInstansiId) && $editInstansiManual !== '';
  ?>
  <input type="hidden" name="ada_rujukan" id="adaRujukanInput"
         value="<?= $editRujukanShow ? 1 : 0 ?>">
  <div id="rujukanDetail" class="card shadow-sm mb-3"
       style="display:<?= $editRujukanShow ? 'block' : 'none' ?>;border-top:3px solid #fd7e14;">
    <div class="card-header py-2 px-4 d-flex align-items-center gap-2" style="background:#fff8f30d;">
      <i class="ti tabler-arrow-forward" style="color:#fd7e14;"></i>
      <span class="fw-bold" style="font-size:.88rem;color:#c4620a;">Detail Rujukan</span>
      <span class="text-muted fw-normal ms-1" style="font-size:.78rem;">— aktif saat prognosis "Memerlukan rujukan lanjutan" dipilih</span>
    </div>
    <div class="card-body px-4 py-3">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:.82rem;">Instansi Rujukan</label>
          <select name="instansi_rujukan_id" id="selectInstansi" class="form-select form-select-sm">
            <option value="">— Pilih instansi rujukan —</option>
            <?php foreach ($instansiRujukan as $inst): ?>
              <option value="<?= esc($inst['id']) ?>"
                      <?= ($editInstansiId && $editInstansiId == $inst['id']) ? 'selected' : '' ?>>
                <?= esc($inst['singkatan']) ?> — <?= esc($inst['nama_instansi']) ?>
              </option>
            <?php endforeach ?>
            <option value="lainnya"
                    <?= $showLainnyaInput ? 'selected' : '' ?>>Lainnya (isi manual)</option>
          </select>
          <input type="text" name="instansi_rujukan" id="instansiLainnya"
                 class="form-control form-control-sm mt-2"
                 style="display:<?= $showLainnyaInput ? 'block' : 'none' ?>;"
                 placeholder="Tuliskan nama instansi rujukan..."
                 value="<?= esc($editInstansiManual) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:.82rem;">Alasan Rujukan</label>
          <textarea name="alasan_rujukan" class="form-control form-control-sm" rows="3"
                    placeholder="Jelaskan alasan perlu dirujuk..."><?= esc($editAlasan) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Jam Selesai — akhir form -->
  <div class="card shadow-sm mb-3" style="border-top:3px solid #fd7e14;">
    <div class="card-header py-2 px-4 d-flex align-items-center gap-2" style="background:#fd7e140d;">
      <i class="ti tabler-clock-stop" style="color:#fd7e14;font-size:1rem;"></i>
      <span class="fw-bold" style="font-size:.9rem;color:#c4620a;">Jam Selesai Sesi</span>
    </div>
    <div class="card-body px-4 py-3">
      <div class="col-sm-4 col-md-3">
        <input type="time" name="jam_selesai" class="form-control form-control-sm"
               value="<?= esc($hasil['jam_selesai'] ?? '') ?>">
        <div class="text-muted mt-1" style="font-size:.75rem;">Catat waktu sesi berakhir</div>
      </div>
    </div>
  </div>

  <!-- Submit -->
  <div class="d-flex gap-3 align-items-center mb-4">
    <button type="submit" class="btn btn-success px-5" style="font-size:.95rem;padding:.65rem 2rem;">
      <i class="ti tabler-circle-check me-1"></i>
      <?= $isEditMode ? 'Perbarui Hasil Konseling' : 'Simpan Hasil &amp; Selesaikan Sesi' ?>
    </button>
    <a href="<?= base_url($isEditMode ? 'konselor/janji/' . $janji['id'] : 'konselor/janji') ?>"
       class="btn btn-outline-secondary">Batal</a>
  </div>
</form>

<?php elseif ($isDisplayMode): ?>
  <!-- Rujukan tersimpan (jika ada) -->
  <?php if ($hasil && $hasil['ada_rujukan']): ?>
  <div class="card shadow-sm mb-3" style="border-top:3px solid #fd7e14;">
    <div class="card-header py-2 px-4 d-flex align-items-center justify-content-between" style="background:#fff8f30d;">
      <span class="fw-bold" style="font-size:.88rem;color:#c4620a;"><i class="ti tabler-arrow-forward me-1"></i>Detail Rujukan</span>
      <a href="<?= base_url('konselor/janji/' . $janji['id'] . '/surat-rujukan') ?>" target="_blank"
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
<?php endif ?>

<!-- Kembali -->
<a href="<?= base_url('konselor/janji') ?>" class="btn btn-outline-secondary btn-sm mb-4">
  <i class="ti tabler-arrow-left me-1"></i>Kembali ke Daftar
</a>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  /* ── Rujukan toggle ─────────────────────────────────── */
  function toggleRujukan(show) {
    const wrap = document.getElementById('rujukanDetail');
    const inp  = document.getElementById('adaRujukanInput');
    if (wrap) wrap.style.display = show ? 'block' : 'none';
    if (inp)  inp.value = show ? '1' : '0';
  }

  /* ── Catatan counter ────────────────────────────────── */
  function updateCatatanCounter(el) {
    const c = document.getElementById('catatanCounter');
    if (c) c.textContent = el.value.length + ' / 450';
  }

  /* ── Formulasi Ringkas: update display dari checklist── */
  function _escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function updateFormulasi() {
    ['stressor','kerentanan','protektif','koping'].forEach(function(sec) {
      const div = document.getElementById('formulasi_' + sec);
      if (! div) return;
      const inputs = Array.from(document.querySelectorAll(
        'input[type="checkbox"][name^="' + sec + '["], input[type="radio"][name^="' + sec + '["]'
      )).filter(el => el.checked);

      if (! inputs.length) {
        div.innerHTML = '<span class="text-muted fst-italic" style="font-size:.82rem;">belum ada pilihan</span>';
        return;
      }
      div.innerHTML = inputs.map(function(el) {
        var label = el.value;
        if (el.value === 'Lainnya') {
          var row = el.closest('.lainnya-row');
          var txt = row && row.querySelector('.lainnya-text');
          if (txt && txt.value) label = 'Lainnya: ' + txt.value;
        }
        return '<span class="badge bg-label-secondary me-1 mb-1 fw-normal" style="font-size:.78rem;">' + _escHtml(label) + '</span>';
      }).join('');
    });
  }

  /* ── Event delegation (checklist + prognosis + Lainnya)  */
  document.addEventListener('change', function(e) {
    var el = e.target;

    if (el.type === 'checkbox' && el.value === 'Lainnya') {
      var row  = el.closest('.lainnya-row');
      var text = row && row.querySelector('.lainnya-text');
      if (text) text.style.display = el.checked ? 'inline-block' : 'none';
      updateFormulasi();
    } else if (el.type === 'checkbox') {
      updateFormulasi();
    } else if (el.type === 'radio') {
      /* prognosis → rujukan detail */
      if (el.name === 'prognosis[hasil]') {
        toggleRujukan(el.value === 'Memerlukan rujukan lanjutan');
      }
      /* Lainnya text dalam radio group */
      document.querySelectorAll('input[type="radio"][name="' + CSS.escape(el.name) + '"][value="Lainnya"]')
        .forEach(function(r) {
          var row  = r.closest('.lainnya-row');
          var text = row && row.querySelector('.lainnya-text');
          if (text) text.style.display = r.checked ? 'inline-block' : 'none';
        });
      updateFormulasi();
    }
  });

  /* ── Input teks Lainnya → update formulasi juga ─────── */
  document.addEventListener('input', function(e) {
    if (e.target.classList.contains('lainnya-text')) updateFormulasi();
  });

  /* ── DOMContentLoaded ───────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function() {
    /* selectInstansi → lainnya text */
    var selI = document.getElementById('selectInstansi');
    var inpL = document.getElementById('instansiLainnya');
    if (selI) {
      selI.addEventListener('change', function() {
        inpL.style.display = this.value === 'lainnya' ? 'block' : 'none';
        inpL.required = this.value === 'lainnya';
      });
      /* Init for edit mode pre-fill */
      if (selI.value === 'lainnya' && inpL) {
        inpL.style.display = 'block';
        inpL.required = true;
      }
    }
    /* Init rujukan display when ada_rujukan is pre-set (edit mode) */
    var adaRujIn = document.getElementById('adaRujukanInput');
    if (adaRujIn && adaRujIn.value === '1') toggleRujukan(true);
    /* Init counter */
    var ta = document.getElementById('catatanSesiInput');
    if (ta) updateCatatanCounter(ta);
    /* Init formulasi (picks up pre-checked items in edit mode) */
    updateFormulasi();
  });
</script>
<?= $this->endSection() ?>
