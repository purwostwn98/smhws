<?php
/*
 * Konten HTML untuk mPDF. mPDF mendukung CSS yang cukup lengkap:
 * margin, padding, border, font-size, color, background, dll.
 * Tidak perlu DOCTYPE / html / body — mPDF terima fragment HTML.
 */

/** @var array      $janji */
/** @var string|null $konselorNama */
/** @var string     $konselorNoStr */
/** @var array|null $dass */
/** @var array|null $safety */
/** @var array|null $hasil */
/** @var array      $checklistData */
/** @var array      $instansiRujukan */
$janji           = $janji           ?? [];
$konselorNama    = $konselorNama    ?? null;
$konselorNoStr   = $konselorNoStr   ?? '—';
$dass            = $dass            ?? null;
$safety          = $safety          ?? null;
$hasil           = $hasil           ?? null;
$checklistData   = $checklistData   ?? [];
$instansiRujukan = $instansiRujukan ?? [];

/* ── Helpers ─────────────────────────────────────────────────── */
$decodeChecklist = function (?string $json, string $sectionKey) use ($checklistData): array {
  if (! $json) return [];
  $data = json_decode($json, true);
  if (! $data) return [];
  $subs   = $checklistData[$sectionKey]['subsections'] ?? [];
  $result = [];
  foreach ($subs as $subKey => $subInfo) {
    if (! isset($data[$subKey])) continue;
    $values     = $data[$subKey];
    $subLabel   = $subInfo['subsection_label'] ?? $subKey;
    $lainnyaVal = $data[$subKey . '_lainnya'] ?? '';
    $vals       = is_array($values) ? $values : [$values];
    $items      = [];
    foreach ($vals as $v) {
      if (! $v) continue;
      $items[] = ($v === 'Lainnya' && $lainnyaVal) ? 'Lainnya: ' . $lainnyaVal : $v;
    }
    if ($items) $result[] = ['label' => $subLabel, 'items' => $items];
  }
  return $result;
};

$renderChecklist = function (?string $json, string $sectionKey) use ($decodeChecklist): string {
  $groups = $decodeChecklist($json, $sectionKey);
  if (! $groups) return '<p class="empty">— tidak ada data —</p>';
  $out = '';
  foreach ($groups as $g) {
    $out .= '<p class="sub-label">' . esc($g['label']) . '</p><ul class="checklist">';
    foreach ($g['items'] as $item) {
      $out .= '<li>' . esc($item) . '</li>';
    }
    $out .= '</ul>';
  }
  return $out;
};

$flatItems = function (?string $json, string $sectionKey) use ($decodeChecklist): string {
  $groups = $decodeChecklist($json, $sectionKey);
  if (! $groups) return '—';
  $all = [];
  foreach ($groups as $g) foreach ($g['items'] as $item) $all[] = $item;
  return implode('; ', $all);
};

/* ── Maps ────────────────────────────────────────────────────── */
$statusNikahMap = [
  'belum_menikah' => 'Belum Menikah',
  'menikah' => 'Menikah',
  'cerai'         => 'Cerai',
  'janda_duda' => 'Janda/Duda',
];
$statusNikah = $statusNikahMap[$janji['status_pernikahan'] ?? '']
  ?? ucwords(str_replace('_', ' ', $janji['status_pernikahan'] ?? '—'));

$metodeMap = [
  'online'   => 'Tatap Muka Daring (Online)',
  'offline'  => 'Tatap Muka Luring (Offline)',
  'keduanya' => 'Tatap Muka Daring & Luring',
];
$metodeLabel = $metodeMap[$janji['metode'] ?? ''] ?? ucfirst($janji['metode'] ?? '—');

$dassKatMap = [
  'normal' => 'Normal',
  'ringan' => 'Ringan',
  'sedang' => 'Sedang',
  'berat'  => 'Berat',
  'sangat_berat' => 'Sangat Berat',
];

/* ── Instansi rujukan ────────────────────────────────────────── */
$namaInstansi = '—';
if ($hasil && $hasil['ada_rujukan']) {
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
}

/* ── Waktu & durasi ──────────────────────────────────────────── */
$durMenit = null;
if ($hasil && ! empty($hasil['jam_mulai']) && ! empty($hasil['jam_selesai'])) {
  $dur = (strtotime($hasil['jam_selesai']) - strtotime($hasil['jam_mulai'])) / 60;
  if ($dur > 0) $durMenit = (int) $dur;
}

/* ── Tanggal Indonesia ───────────────────────────────────────── */
$bulanId = [
  '',
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember'
];
$tglUnix        = $janji['tanggal_konseling'] ? strtotime($janji['tanggal_konseling']) : time();
$tanggalPanjang = date('j', $tglUnix) . ' ' . $bulanId[(int) date('n', $tglUnix)] . ' ' . date('Y', $tglUnix);

/* ── Baris waktu konseling ───────────────────────────────────── */
$waktuJadwal = $janji['jam_konseling'] ? date('H:i', strtotime($janji['jam_konseling'])) . ' WIB' : '—';
$waktuSesi   = '';
if ($hasil && (! empty($hasil['jam_mulai']) || ! empty($hasil['jam_selesai']))) {
  $jM = ! empty($hasil['jam_mulai'])   ? date('H:i', strtotime($hasil['jam_mulai']))   : '?';
  $jS = ! empty($hasil['jam_selesai']) ? date('H:i', strtotime($hasil['jam_selesai'])) : '?';
  $waktuSesi = $jM . ' – ' . $jS . ' WIB' . ($durMenit ? ' (' . $durMenit . ' menit)' : '');
}
?>
<style>
  /* ── Base ──────────────────────────────────────────── */
  body {
    font-family: dejavuserif;
    font-size: 11pt;
    color: #111;
    line-height: 1.55;
  }

  p {
    margin: 0 0 4pt 0;
    padding: 0;
  }

  ul,
  ol {
    margin: 2pt 0 6pt 0;
    padding-left: 18pt;
  }

  li {
    margin-bottom: 2pt;
    font-size: 11pt;
  }

  b,
  strong {
    font-weight: bold;
  }

  i,
  em {
    font-style: italic;
  }

  /* ── Section title ─────────────────────────────────── */
  .sec {
    font-size: 11pt;
    font-weight: bold;
    text-transform: uppercase;
    border-bottom: 1pt solid #222;
    padding-bottom: 2pt;
    margin-top: 12pt;
    margin-bottom: 6pt;
    letter-spacing: 0.3pt;
  }

  /* ── Data table (label : value) ────────────────────── */
  .dt {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4pt;
  }

  .dt td {
    padding: 2pt 4pt;
    vertical-align: top;
    font-size: 11pt;
  }

  .dt .lbl {
    width: 44%;
    color: #222;
  }

  .dt .col {
    width: 8pt;
    text-align: center;
  }

  .dt .val {}

  /* ── DASS table ────────────────────────────────────── */
  .dass {
    width: 100%;
    border-collapse: collapse;
    margin: 4pt 0 8pt;
    font-size: 10.5pt;
  }

  .dass th {
    background: #e8e8e8;
    border: 0.5pt solid #999;
    padding: 4pt 6pt;
    text-align: left;
    font-weight: bold;
  }

  .dass td {
    border: 0.5pt solid #999;
    padding: 4pt 6pt;
  }

  .dass .num {
    text-align: center;
    width: 50pt;
  }

  .dass .kat {
    text-align: center;
    width: 80pt;
  }

  /* ── Safety table ──────────────────────────────────── */
  .safety {
    width: 100%;
    border-collapse: collapse;
    margin: 4pt 0 8pt;
    font-size: 10.5pt;
  }

  .safety td {
    padding: 3pt 4pt;
    border-bottom: 0.5pt dotted #ccc;
  }

  .safety .ans {
    text-align: right;
    width: 60pt;
    font-weight: bold;
  }

  .danger {
    color: #b91c1c;
  }

  .safe {
    color: #15803d;
  }

  /* ── Checklist ─────────────────────────────────────── */
  .sub-label {
    font-size: 9.5pt;
    font-style: italic;
    color: #555;
    margin: 4pt 0 1pt 0;
  }

  .checklist {
    margin: 1pt 0 5pt 14pt;
    padding: 0;
  }

  .checklist li {
    font-size: 11pt;
    margin-bottom: 1pt;
  }

  .empty {
    font-style: italic;
    color: #888;
    font-size: 10pt;
    margin: 2pt 0;
  }

  /* ── Keluhan box ───────────────────────────────────── */
  .keluhan-box {
    border: 0.5pt solid #bbb;
    padding: 6pt 8pt;
    background: #fafafa;
    font-size: 11pt;
    line-height: 1.6;
  }

  /* ── Formulasi ─────────────────────────────────────── */
  .formulasi {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4pt;
  }

  .formulasi td {
    padding: 3pt 4pt;
    vertical-align: top;
    font-size: 11pt;
  }

  .formulasi .fl {
    width: 42%;
    font-style: italic;
    color: #444;
    font-size: 10pt;
  }

  .kesimpulan-box {
    border: 0.5pt solid #ccc;
    padding: 5pt 7pt;
    background: #fafafa;
    font-size: 11pt;
    line-height: 1.6;
    margin-top: 2pt;
  }

  /* ── Tanda tangan ──────────────────────────────────── */
  .ttd {
    width: 100%;
    margin-top: 20pt;
  }

  .ttd td {
    font-size: 11pt;
  }

  .ttd-right {
    text-align: center;
    width: 44%;
  }

  .ttd-spacer {
    height: 40pt;
  }

  .ttd-name {
    font-weight: bold;
    border-top: 0.5pt solid #000;
    display: inline-block;
    padding-top: 3pt;
    min-width: 150pt;
  }

  .ttd-nosipp {
    font-size: 9.5pt;
    color: #444;
  }
</style>

<!-- ═══ I. INFORMASI UMUM ═══ -->
<div class="sec">I. Informasi Umum</div>
<table class="dt">
  <tr>
    <td class="lbl">Nomor Klien (ID Konseling)</td>
    <td class="col">:</td>
    <td class="val"><b>#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></b></td>
  </tr>
  <tr>
    <td class="lbl">Nama Psikolog</td>
    <td class="col">:</td>
    <td class="val"><?= esc($konselorNama ?: '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Tanggal Konseling</td>
    <td class="col">:</td>
    <td class="val"><?= $tanggalPanjang ?></td>
  </tr>
  <tr>
    <td class="lbl">Waktu Jadwal</td>
    <td class="col">:</td>
    <td class="val"><?= esc($waktuJadwal) ?></td>
  </tr>
  <?php if ($waktuSesi): ?>
    <tr>
      <td class="lbl">Waktu Sesi Aktual</td>
      <td class="col">:</td>
      <td class="val"><?= esc($waktuSesi) ?></td>
    </tr>
  <?php endif ?>
  <tr>
    <td class="lbl">Media Konseling</td>
    <td class="col">:</td>
    <td class="val"><?= esc($metodeLabel) ?></td>
  </tr>
  <?php if (! empty($janji['lokasi_link'])): ?>
    <tr>
      <td class="lbl">Lokasi / Link</td>
      <td class="col">:</td>
      <td class="val"><?= esc($janji['lokasi_link']) ?></td>
    </tr>
  <?php endif ?>
</table>

<!-- ═══ II. IDENTITAS KLIEN ═══ -->
<div class="sec">II. Identitas Klien</div>
<table class="dt">
  <tr>
    <td class="lbl">Nama</td>
    <td class="col">:</td>
    <td class="val"><b><?= esc($janji['name'] ?? '—') ?></b></td>
  </tr>
  <tr>
    <td class="lbl">NIM / NIP</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['uniid'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Jenis Kelamin</td>
    <td class="col">:</td>
    <td class="val"><?= esc(ucwords(str_replace('-', ' ', $janji['jenis_kelamin'] ?? '—'))) ?></td>
  </tr>
  <tr>
    <td class="lbl">Usia</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['usia'] ?? '—') ?> tahun</td>
  </tr>
  <tr>
    <td class="lbl">Status Pernikahan</td>
    <td class="col">:</td>
    <td class="val"><?= esc($statusNikah) ?></td>
  </tr>
  <tr>
    <td class="lbl">Semester</td>
    <td class="col">:</td>
    <td class="val">Semester <?= esc($janji['semester'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Fakultas</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['fakultas'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Program Studi</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['prodi'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Agama</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['agama'] ?? '—') ?></td>
  </tr>
  <?php if (! empty($janji['pekerjaan'])): ?>
    <tr>
      <td class="lbl">Pekerjaan</td>
      <td class="col">:</td>
      <td class="val"><?= esc($janji['pekerjaan']) ?></td>
    </tr>
  <?php endif ?>
  <tr>
    <td class="lbl">No. HP</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['phone'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Email</td>
    <td class="col">:</td>
    <td class="val"><?= esc($janji['email'] ?? '—') ?></td>
  </tr>
</table>

<!-- ═══ III. KELUHAN UTAMA ═══ -->
<div class="sec">III. Keluhan Utama</div>
<?php if (! empty($janji['tema_konseling'])): ?>
  <p style="margin-bottom:4pt;">
    Tema: <b><?= esc(ucwords(str_replace('_', ' ', $janji['tema_konseling']))) ?></b>
    <?php if (! empty($janji['urgensi'])): ?>
      &nbsp;—&nbsp;Urgensi: <b><?= esc(ucwords(str_replace('_', ' ', $janji['urgensi']))) ?></b>
    <?php endif ?>
  </p>
<?php endif ?>
<div class="keluhan-box"><?= nl2br(esc($janji['keluhan_utama'] ?? '—')) ?></div>
<?php if (! empty($janji['mulai_keluhan']) || ! empty($janji['upaya_dilakukan'])): ?>
  <table class="dt" style="margin-top:5pt;">
    <?php if (! empty($janji['mulai_keluhan'])): ?>
      <tr>
        <td class="lbl">Sejak kapan</td>
        <td class="col">:</td>
        <td class="val"><?= esc($janji['mulai_keluhan']) ?></td>
      </tr>
    <?php endif ?>
    <?php if (! empty($janji['upaya_dilakukan'])): ?>
      <tr>
        <td class="lbl">Upaya yang sudah dilakukan</td>
        <td class="col">:</td>
        <td class="val"><?= esc($janji['upaya_dilakukan']) ?></td>
      </tr>
    <?php endif ?>
  </table>
<?php endif ?>

<!-- ═══ IV. HASIL ASESMEN ═══ -->
<div class="sec">IV. Hasil Asesmen</div>

<?php if ($dass): ?>
  <p style="font-weight:bold;margin-bottom:3pt;">Hasil Asesmen Awal (DASS-21)</p>
  <table class="dass">
    <tr>
      <th>Aspek</th>
      <th class="num">Skor</th>
      <th class="kat">Kategori</th>
    </tr>
    <?php foreach (
      [
        ['Depresi',  $dass['skor_depresi'] ?? 0, $dassKatMap[$dass['kategori_depresi'] ?? 'normal'] ?? '—'],
        ['Ansietas', $dass['skor_anxiety'] ?? 0,  $dassKatMap[$dass['kategori_anxiety'] ?? 'normal'] ?? '—'],
        ['Stres',    $dass['skor_stress']  ?? 0,  $dassKatMap[$dass['kategori_stress']  ?? 'normal'] ?? '—'],
      ] as [$lbl, $skor, $kat]
    ): ?>
      <tr>
        <td><?= $lbl ?></td>
        <td class="num"><?= $skor ?></td>
        <td class="kat"><?= $kat ?></td>
      </tr>
    <?php endforeach ?>
  </table>
<?php endif ?>

<?php if ($safety): ?>
  <p style="font-weight:bold;margin-bottom:3pt;">Safety Screening</p>
  <table class="safety">
    <?php foreach (
      [
        ['Pernah self-harm',         'pernah_selfharm',          'ya'],
        ['Pikiran mengakhiri hidup',  'pikiran_mengakhiri_hidup', 'ya'],
        ['Pikiran yang mengganggu',   'pikiran_mengganggu',       'ya'],
        ['Merasa aman saat ini',      'merasa_aman',              'tidak'],
      ] as [$lbl, $key, $danger]
    ):
      $val      = $safety[$key] ?? '—';
      $isDanger = ($val === $danger);
    ?>
      <tr>
        <td><?= $lbl ?></td>
        <td class="ans <?= $isDanger ? 'danger' : 'safe' ?>"><?= ucfirst($val) ?></td>
      </tr>
    <?php endforeach ?>
  </table>
  <?php if (! empty($safety['riwayat_selfharm_keterangan'])): ?>
    <p style="font-size:10pt;padding:3pt 6pt;border:0.5pt solid #f0a0a0;background:#fff5f5;margin-bottom:6pt;">
      <b>Keterangan:</b> <?= esc($safety['riwayat_selfharm_keterangan']) ?>
    </p>
  <?php endif ?>
<?php endif ?>

<p style="font-weight:bold;margin:6pt 0 2pt;">Stressor saat ini (Current Stressor)</p>
<?= $renderChecklist($hasil['stressor'] ?? null, 'stressor') ?>

<p style="font-weight:bold;margin:6pt 0 2pt;">Faktor Kerentanan (Diathesis/Vulnerability)</p>
<?= $renderChecklist($hasil['faktor_kerentanan'] ?? null, 'kerentanan') ?>

<p style="font-weight:bold;margin:6pt 0 2pt;">Faktor Protektif</p>
<?= $renderChecklist($hasil['faktor_protektif'] ?? null, 'protektif') ?>

<p style="font-weight:bold;margin:6pt 0 2pt;">Strategi Koping yang Digunakan Klien</p>
<?= $renderChecklist($hasil['strategi_koping'] ?? null, 'koping') ?>

<!-- ═══ V. FORMULASI RINGKAS ═══ -->
<div class="sec">V. Formulasi Ringkas Permasalahan Klien</div>
<table class="formulasi">
  <tr>
    <td class="fl">Faktor Kerentanan Utama (Diathesis):</td>
    <td><?= esc($flatItems($hasil['faktor_kerentanan'] ?? null, 'kerentanan')) ?></td>
  </tr>
  <tr>
    <td class="fl">Stressor atau Pemicu Utama:</td>
    <td><?= esc($flatItems($hasil['stressor'] ?? null, 'stressor')) ?></td>
  </tr>
  <tr>
    <td class="fl">Manifestasi atau Respons Psikologis yang Muncul:</td>
    <td><?= nl2br(esc($janji['keluhan_utama'] ?? '—')) ?></td>
  </tr>
  <tr>
    <td class="fl">Faktor Protektif yang Dimiliki Klien:</td>
    <td><?= esc($flatItems($hasil['faktor_protektif'] ?? null, 'protektif')) ?></td>
  </tr>
  <tr>
    <td class="fl">Strategi Coping Dominan yang Digunakan:</td>
    <td><?= esc($flatItems($hasil['strategi_koping'] ?? null, 'koping')) ?></td>
  </tr>
</table>
<p style="font-size:10pt;font-style:italic;color:#444;margin:4pt 0 2pt;">Kesimpulan Psikolog:</p>
<div class="kesimpulan-box"><?= nl2br(esc($hasil['catatan_sesi'] ?? '—')) ?></div>

<!-- ═══ VI. DIAGNOSIS ═══ -->
<div class="sec">VI. Diagnosis Problem Normal Bermasalah (DSM-5-TR)</div>
<?= $renderChecklist($hasil['diagnosis'] ?? null, 'diagnosis') ?>

<!-- ═══ VII. INTERVENSI ═══ -->
<div class="sec">VII. Intervensi yang Diberikan</div>
<?= $renderChecklist($hasil['intervensi'] ?? null, 'intervensi') ?>

<!-- ═══ VIII. REKOMENDASI ═══ -->
<div class="sec">VIII. Rekomendasi</div>
<?= $renderChecklist($hasil['rekomendasi'] ?? null, 'rekomendasi') ?>
<?php if ($hasil): ?>
  <table class="dt" style="margin-top:4pt;">
    <tr>
      <td class="lbl">Sesi Lanjutan</td>
      <td class="col">:</td>
      <td class="val"><?= $hasil['sesi_lanjutan'] ? 'Diperlukan' : 'Tidak diperlukan' ?></td>
    </tr>
    <tr>
      <td class="lbl">Rujukan</td>
      <td class="col">:</td>
      <td class="val">
        <?php if ($hasil['ada_rujukan']): ?>
          Ya — <?= esc($namaInstansi) ?>
          <?php if (! empty($hasil['alasan_rujukan'])): ?>
            <br><i style="font-size:10pt;"><?= esc($hasil['alasan_rujukan']) ?></i>
          <?php endif ?>
        <?php else: ?>
          Tidak
        <?php endif ?>
      </td>
    </tr>
  </table>
<?php endif ?>

<!-- ═══ IX. PROGNOSIS ═══ -->
<div class="sec">IX. Prognosis / Kemungkinan Perkembangan Permasalahan Klien</div>
<?= $renderChecklist($hasil['prognosis'] ?? null, 'prognosis') ?>

<!-- ═══ TANDA TANGAN ═══ -->
<table class="ttd">
  <tr>
    <td></td>
    <td class="ttd-right">
      <p>Surakarta, <?= $tanggalPanjang ?></p>
      <p>Psikolog</p>
      <p class="ttd-spacer"></p>
      <br>
      <br>
      <p><span class="ttd-name"><?= esc($konselorNama ?: '____________________________') ?></span></p>
      <p class="ttd-nosipp">No. SIPP / STR: <?= esc($konselorNoStr) ?></p>
    </td>
  </tr>
</table>