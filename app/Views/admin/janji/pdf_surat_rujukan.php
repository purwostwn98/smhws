<?php

/**
 * PDF view — Surat Rujukan
 * Dirender melalui mPDF::WriteHTML(). <style> didukung di sini.
 * Header (kop) diset via SetHTMLHeader() di controller.
 */

$janji = $janji ?? [];

$bulanId = [
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

// ── Psikolog ────────────────────────────────────────────────────────────
$namaKonselor = '____________________';
$noSIPP       = '____________________';
if (! empty($konselor)) {
  $namaKonselor = trim(
    (! empty($konselor['gelar_depan'])   ? $konselor['gelar_depan'] . ' '  : '')
      . ($konselor['name'] ?? '')
      . (! empty($konselor['gelar_belakang']) ? ', ' . $konselor['gelar_belakang'] : '')
  ) ?: '____________________';
  $noSIPP = ! empty($konselor['no_str']) ? $konselor['no_str'] : '____________________';
}

// ── Data klien ──────────────────────────────────────────────────────────
$jkMap = ['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan'];
$jenisKelamin = $jkMap[$janji['jenis_kelamin'] ?? ''] ?? ucfirst($janji['jenis_kelamin'] ?? '—');

// ── Intervensi: decode JSON → plain text ────────────────────────────────
$intervensiItems = [];
if (! empty($hasil['intervensi'])) {
  $data = json_decode($hasil['intervensi'], true);
  if (is_array($data)) {
    foreach ($data as $subKey => $values) {
      if (str_ends_with($subKey, '_lainnya')) continue;
      $lainnya = $data[$subKey . '_lainnya'] ?? '';
      $vals    = is_array($values) ? $values : [$values];
      foreach ($vals as $v) {
        if ($v === 'Lainnya' && $lainnya) {
          $intervensiItems[] = $lainnya;
        } elseif ($v) {
          $intervensiItems[] = $v;
        }
      }
    }
  }
}
$intervensiText = $intervensiItems ? implode('; ', $intervensiItems) : '—';

// ── Tanggal surat ───────────────────────────────────────────────────────
$tglRef   = $janji['tanggal_konseling'] ? strtotime($janji['tanggal_konseling']) : time();
$tglSurat = date('d', $tglRef) . ' ' . $bulanId[date('n', $tglRef) - 1] . ' ' . date('Y', $tglRef);

// ── Instansi tujuan ─────────────────────────────────────────────────────
$tujuan      = ! empty($namaInstansi) && $namaInstansi !== '—' ? $namaInstansi : '____________________';
$kotaTujuan  = ! empty($alamatInstansi) ? $alamatInstansi : '____________________';
?>
<style>
  body {
    font-family: dejavuserif, serif;
    font-size: 11pt;
    line-height: 1.4;
    color: #000;
  }

  p {
    margin: 0 0 6pt 0;
  }

  .bold {
    font-weight: bold;
  }

  .center {
    text-align: center;
  }

  .kv {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 8pt 0;
  }

  .kv td {
    padding: 1.5pt 0;
    vertical-align: top;
    font-size: 11pt;
  }

  .kv .lbl {
    width: 48%;
  }

  .kv .col {
    width: 8pt;
  }

  .kv .val {}

  .section {
    margin-bottom: 10pt;
  }

  .ttd-row {
    width: 100%;
    border-collapse: collapse;
    margin-top: 28pt;
  }

  .ttd-row td {
    vertical-align: top;
    font-size: 11pt;
  }

  .line {
    border-top: 1pt solid #000;
    margin: 3pt 0 2pt 0;
  }
</style>

<!-- Kepada Yth. -->
<div class="section">
  <p>Kepada Yth. <br>
    <span class="bold"><?= htmlspecialchars($tujuan) ?></span> <br>
    di <?= htmlspecialchars($kotaTujuan) ?>
  </p>
</div>

<p><i>Assalamu'alaikum Warohmatullahi Wabarokatuh,</i></p>

<p>Saya yang bertandatangan di bawah ini:</p>

<table class="kv">
  <tr>
    <td class="lbl">Nama</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($namaKonselor) ?></td>
  </tr>
  <tr>
    <td class="lbl">Jabatan</td>
    <td class="col">:</td>
    <td class="val">Psikolog</td>
  </tr>
  <tr>
    <td class="lbl">No. SIPP / SILP</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($noSIPP) ?></td>
  </tr>
</table>

<p>
  Dengan ini merujuk klien yang sedang melakukan konseling di
  <span class="bold">Student Mental Health and Wellbeing Support (SMHWS) UMS</span>,
  dengan data sebagai berikut:
</p>

<table class="kv">
  <tr>
    <td class="lbl">Nama</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($janji['name'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Jenis Kelamin</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($jenisKelamin) ?></td>
  </tr>
  <tr>
    <td class="lbl">Usia</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($janji['usia'] ?? '—') ?> tahun</td>
  </tr>
  <tr>
    <td class="lbl">Fakultas</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($janji['fakultas'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Program Studi</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($janji['prodi'] ?? '—') ?></td>
  </tr>
</table>

<table class="kv">
  <tr>
    <td class="lbl">Permasalahan</td>
    <td class="col">:</td>
    <td class="val"><?= nl2br(htmlspecialchars($janji['keluhan_utama'] ?? '—')) ?></td>
  </tr>
  <tr>
    <td class="lbl">Intervensi yang telah dilakukan</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($intervensiText) ?></td>
  </tr>
  <tr>
    <td class="lbl">Alasan rujukan</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($hasil['alasan_rujukan'] ?? '—') ?></td>
  </tr>
  <tr>
    <td class="lbl">Tujuan rujukan</td>
    <td class="col">:</td>
    <td class="val"><?= htmlspecialchars($tujuan) ?></td>
  </tr>
</table>

<p>
  Atas perhatian dan kerja sama yang diberikan, kami menyampaikan terima kasih.
</p>

<p><em>Wassalamu'alaikum Warohmatullahi Wabarokatuh.</em></p>

<!-- Tanda Tangan -->
<table class="ttd-row">
  <tr>
    <td style="width:55%;"></td>
    <td style="width:45%;">
      <p>Surakarta, <?= $tglSurat ?></p>
      <p>Psikolog,</p>
      <br><br><br>
      <div class="line"></div>
      <p class="bold"><?= htmlspecialchars($namaKonselor) ?></p>
      <p>SIPP / SILP: <?= htmlspecialchars($noSIPP) ?></p>
    </td>
  </tr>
</table>