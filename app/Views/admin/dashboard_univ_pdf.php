<?php
/** @var array  $stats        */
/** @var array  $filters      */
/** @var string $prodiNama    */
/** @var string $fakultasNama */
/** @var string $periodeLabel */

$stats        = $stats        ?? [];
$prodiNama    = $prodiNama    ?? 'Semua Program Studi';
$fakultasNama = $fakultasNama ?? 'Semua Fakultas';
$periodeLabel = $periodeLabel ?? 'Semua Periode';

$pct = static fn(int $n, int $total): string =>
    $total > 0 ? round($n / $total * 100, 1) . '%' : '0%';

$smtGroups  = $stats['semester_groups'] ?? ['1-2','3-4','5-6','7-8','9-10','11-12','13-14'];
$smtSesi    = $stats['semester']            ?? [];
$smtMhs     = $stats['semester_mahasiswa']  ?? [];
$totalSesi  = $stats['total']              ?? 0;
$totalMhs   = $stats['mahasiswa_unik']     ?? 0;

$jkMhs      = $stats['jk_mahasiswa'] ?? [];
$jkLaki     = (int)($jkMhs['laki-laki'] ?? 0);
$jkPerempuan= (int)($jkMhs['perempuan'] ?? 0);
$jkTotal    = $jkLaki + $jkPerempuan;

$masalahData  = $stats['masalah']            ?? [];
$stressorData = $stats['stressor']           ?? [];
$stressorLbl  = $stats['stressor_labels']    ?? [];
$statusKons   = $stats['status_kons']        ?? [];
$statusKonsLbl= $stats['status_kons_labels'] ?? [];
$hasilData    = $stats['hasil']              ?? [];
$totalHk      = $stats['total_hk']           ?? 0;

// Tanggal cetak Indonesia
$bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$bulan   = (int) date('n');
$tglCetak = date('j') . ' ' . $bulanId[$bulan] . ' ' . date('Y');
?>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: dejavusans, sans-serif;
    font-size: 10pt;
    color: #111;
    line-height: 1.45;
  }

  /* ── Judul laporan ── */
  .laporan-title {
    text-align: center;
    margin-bottom: 6pt;
  }
  .laporan-title h1 {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    margin: 0 0 0 0;
  }
  .laporan-title .periode {
    font-size: 10pt;
    color: #333;
    margin: 0;
  }
  .divider {
    border: none;
    border-top: 2pt solid #1a3a7a;
    margin: 4pt 0 2pt 0;
  }
  .divider-thin {
    border: none;
    border-top: 0.5pt solid #999;
    margin: 2pt 0 10pt 0;
  }

  /* ── Identitas ── */
  .identitas-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10pt;
    font-size: 10pt;
  }
  .identitas-table td {
    padding: 2pt 4pt;
    vertical-align: top;
  }
  .identitas-table td:first-child {
    width: 120pt;
    color: #444;
  }
  .identitas-table td:nth-child(2) {
    width: 8pt;
    color: #444;
  }

  /* ── Section header ── */
  .section-header {
    background: #1a3a7a;
    color: #fff;
    font-size: 10pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.4pt;
    padding: 4pt 8pt;
    margin-top: 10pt;
    margin-bottom: 0;
  }
  .sub-header {
    background: #e8eef7;
    color: #1a3a7a;
    font-size: 9.5pt;
    font-weight: bold;
    padding: 3pt 8pt;
    margin-top: 6pt;
    margin-bottom: 0;
  }

  /* ── Data tables ── */
  .data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9.5pt;
    margin-bottom: 4pt;
  }
  .data-table thead tr {
    background: #dde4f0;
  }
  .data-table thead th {
    padding: 3.5pt 7pt;
    border: 0.5pt solid #aab;
    font-weight: bold;
    color: #1a3a7a;
    text-align: left;
  }
  .data-table thead th.num {
    text-align: right;
    width: 55pt;
  }
  .data-table tbody td {
    padding: 3pt 7pt;
    border: 0.5pt solid #ccd;
  }
  .data-table tbody td.num {
    text-align: right;
    width: 55pt;
  }
  .data-table tbody tr:nth-child(even) {
    background: #f5f7fc;
  }
  .data-table tfoot td {
    padding: 3.5pt 7pt;
    border: 0.5pt solid #aab;
    font-weight: bold;
    background: #edf0f8;
  }
  .data-table tfoot td.num {
    text-align: right;
  }

  /* ── Row dua kolom ── */
  .two-col {
    width: 100%;
    border-collapse: collapse;
  }
  .two-col > tbody > tr > td {
    vertical-align: top;
    padding: 0;
  }
  .two-col > tbody > tr > td:first-child {
    width: 50%;
    padding-right: 8pt;
  }
  .two-col > tbody > tr > td:last-child {
    width: 50%;
    padding-left: 8pt;
  }

  /* ── Tanda tangan ── */
  .ttd-section {
    margin-top: 24pt;
    width: 100%;
    border-collapse: collapse;
  }
  .ttd-section td {
    vertical-align: top;
    padding: 0;
  }
  .ttd-right {
    text-align: center;
    width: 200pt;
    float: right;
  }
  .ttd-right .kota-tgl {
    font-size: 10pt;
    margin-bottom: 2pt;
  }
  .ttd-right .jabatan {
    font-size: 9.5pt;
    color: #444;
    margin-bottom: 40pt;
  }
  .ttd-right .nama {
    font-size: 10pt;
    font-weight: bold;
    border-top: 0.5pt solid #333;
    padding-top: 3pt;
    display: inline-block;
    min-width: 150pt;
  }
  .empty-row td {
    color: #888;
    font-style: italic;
    text-align: center;
  }
</style>

<!-- ── Judul ──────────────────────────────────────────────────────────── -->
<div class="laporan-title">
  <h1>Laporan Agregat Layanan Konseling SMHWS UMS</h1>
  <p class="periode"><?= esc($periodeLabel) ?></p>
</div>
<hr class="divider" />
<hr class="divider-thin" />

<!-- ── Identitas ────────────────────────────────────────────────────────── -->
<div class="section-header">Identitas</div>
<table class="identitas-table" style="margin-top:6pt;">
  <tr><td>Fakultas</td><td>:</td><td><?= esc($fakultasNama) ?></td></tr>
  <tr><td>Program Studi</td><td>:</td><td><?= esc($prodiNama) ?></td></tr>
  <tr><td>Periode</td><td>:</td><td><?= esc($periodeLabel) ?></td></tr>
</table>

<!-- ── Informasi Umum ────────────────────────────────────────────────────── -->
<div class="section-header">Informasi Umum</div>
<table class="identitas-table" style="margin-top:6pt;">
  <tr>
    <td>Jumlah mahasiswa yang melakukan konseling</td>
    <td>:</td>
    <td><strong><?= $totalMhs ?> orang</strong></td>
  </tr>
  <tr>
    <td>Total sesi konseling</td>
    <td>:</td>
    <td><strong><?= $totalSesi ?> sesi</strong></td>
  </tr>
  <tr>
    <td>Total laporan hasil konseling</td>
    <td>:</td>
    <td><strong><?= $totalHk ?> laporan</strong></td>
  </tr>
</table>

<!-- ── Karakteristik Mahasiswa ───────────────────────────────────────────── -->
<div class="section-header">Karakteristik Mahasiswa</div>

<table class="two-col" style="margin-top:6pt;">
<tr>
  <!-- Semester -->
  <td>
    <div class="sub-header">Berdasarkan Semester</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Semester</th>
          <th class="num">Mahasiswa</th>
          <th class="num">%</th>
          <th class="num">Sesi</th>
          <th class="num">%</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($smtGroups as $grp):
          $mhsN  = (int)($smtMhs[$grp] ?? 0);
          $sesiN = (int)($smtSesi[$grp] ?? 0);
        ?>
        <tr>
          <td><?= esc($grp) ?></td>
          <td class="num"><?= $mhsN ?></td>
          <td class="num"><?= $pct($mhsN, $totalMhs) ?></td>
          <td class="num"><?= $sesiN ?></td>
          <td class="num"><?= $pct($sesiN, $totalSesi) ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
      <tfoot>
        <tr>
          <td>Total</td>
          <td class="num"><?= $totalMhs ?></td>
          <td class="num">100%</td>
          <td class="num"><?= $totalSesi ?></td>
          <td class="num">100%</td>
        </tr>
      </tfoot>
    </table>
  </td>

  <!-- Jenis Kelamin -->
  <td>
    <div class="sub-header">Berdasarkan Jenis Kelamin</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Jenis Kelamin</th>
          <th class="num">Mahasiswa</th>
          <th class="num">%</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Laki-laki</td>
          <td class="num"><?= $jkLaki ?></td>
          <td class="num"><?= $pct($jkLaki, $jkTotal) ?></td>
        </tr>
        <tr>
          <td>Perempuan</td>
          <td class="num"><?= $jkPerempuan ?></td>
          <td class="num"><?= $pct($jkPerempuan, $jkTotal) ?></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td>Total</td>
          <td class="num"><?= $jkTotal ?></td>
          <td class="num">100%</td>
        </tr>
      </tfoot>
    </table>
  </td>
</tr>
</table>

<!-- ── Masalah Utama ──────────────────────────────────────────────────────── -->
<div class="section-header">Masalah Utama yang Dialami Klien</div>
<?php $masalahTotal = array_sum($masalahData); ?>
<table class="data-table" style="margin-top:6pt;">
  <thead>
    <tr>
      <th>Jenis Masalah</th>
      <th class="num">Jumlah</th>
      <th class="num">%</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($masalahData) || $masalahTotal === 0): ?>
    <tr class="empty-row"><td colspan="3">— Belum ada data —</td></tr>
    <?php else: ?>
    <?php foreach ($masalahData as $label => $n): if ($n === 0) continue; ?>
    <tr>
      <td><?= esc($label) ?></td>
      <td class="num"><?= $n ?></td>
      <td class="num"><?= $pct((int)$n, $masalahTotal) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </tbody>
  <?php if ($masalahTotal > 0): ?>
  <tfoot>
    <tr>
      <td>Total</td>
      <td class="num"><?= $masalahTotal ?></td>
      <td class="num">—</td>
    </tr>
  </tfoot>
  <?php endif ?>
</table>

<!-- ── Stressor Utama ────────────────────────────────────────────────────── -->
<div class="section-header">Stressor Utama Pemicu Permasalahan Mahasiswa</div>
<?php $stressorTotal = array_sum($stressorData); ?>
<table class="data-table" style="margin-top:6pt;">
  <thead>
    <tr>
      <th>Stressor</th>
      <th class="num">Jumlah Sesi</th>
      <th class="num">%</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($stressorData) || $stressorTotal === 0): ?>
    <tr class="empty-row"><td colspan="3">— Belum ada data —</td></tr>
    <?php else: ?>
    <?php foreach ($stressorData as $key => $n): ?>
    <tr>
      <td><?= esc($stressorLbl[$key] ?? ucfirst($key)) ?></td>
      <td class="num"><?= $n ?></td>
      <td class="num"><?= $pct((int)$n, $totalHk) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </tbody>
</table>

<!-- ── Status Konseling ──────────────────────────────────────────────────── -->
<div class="section-header">Status Konseling</div>
<?php $statusTotal = array_sum($statusKons); ?>
<table class="data-table" style="margin-top:6pt;">
  <thead>
    <tr>
      <th>Status</th>
      <th class="num">Jumlah</th>
      <th class="num">%</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($statusKons) || $statusTotal === 0): ?>
    <tr class="empty-row"><td colspan="3">— Belum ada data —</td></tr>
    <?php else: ?>
    <?php foreach ($statusKons as $key => $n): if ($n === 0) continue; ?>
    <tr>
      <td><?= esc($statusKonsLbl[$key] ?? $key) ?></td>
      <td class="num"><?= $n ?></td>
      <td class="num"><?= $pct((int)$n, $statusTotal) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </tbody>
  <?php if ($statusTotal > 0): ?>
  <tfoot>
    <tr>
      <td>Total</td>
      <td class="num"><?= $statusTotal ?></td>
      <td class="num">100%</td>
    </tr>
  </tfoot>
  <?php endif ?>
</table>

<!-- ── Hasil Konseling ───────────────────────────────────────────────────── -->
<div class="section-header">Hasil Konseling</div>
<?php $hasilTotal = array_sum($hasilData); ?>
<table class="data-table" style="margin-top:6pt;">
  <thead>
    <tr>
      <th>Penilaian dari Psikolog yang Menangani</th>
      <th class="num">Jumlah</th>
      <th class="num">%</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($hasilData) || $hasilTotal === 0): ?>
    <tr class="empty-row"><td colspan="3">— Belum ada data —</td></tr>
    <?php else: ?>
    <?php foreach ($hasilData as $label => $n): if ($n === 0) continue; ?>
    <tr>
      <td><?= esc($label) ?></td>
      <td class="num"><?= $n ?></td>
      <td class="num"><?= $pct((int)$n, $hasilTotal) ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </tbody>
  <?php if ($hasilTotal > 0): ?>
  <tfoot>
    <tr>
      <td>Total</td>
      <td class="num"><?= $hasilTotal ?></td>
      <td class="num">100%</td>
    </tr>
  </tfoot>
  <?php endif ?>
</table>

<!-- ── Tanda Tangan ──────────────────────────────────────────────────────── -->
<div style="margin-top:28pt; text-align:right;">
  <div style="display:inline-block; text-align:center; min-width:180pt;">
    <div style="font-size:10pt; margin-bottom:2pt;">Surakarta, <?= $tglCetak ?></div>
    <div style="font-size:9.5pt; color:#444; margin-bottom:48pt;">Ketua SMHWS UMS,</div>
    <div style="font-size:10pt; font-weight:bold; border-top:0.5pt solid #333; padding-top:3pt; display:inline-block; min-width:160pt;">Dr. Usmi Karyani, M.Si., Psikolog</div>
  </div>
</div>
