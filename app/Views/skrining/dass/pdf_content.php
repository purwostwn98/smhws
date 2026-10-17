<?php
$labelKat = fn(string $k) => \App\Models\DassAssessmentModel::labelKategori($k);
$warnaKat = [
    'normal' => '#2d9b6e', 'ringan' => '#0dcaf0', 'sedang' => '#d48f00',
    'berat' => '#dc3545', 'sangat_berat' => '#a71d2a',
];
// 'tanggal' sudah dalam Asia/Jakarta (diisi saat submit); string tanpa zona tidak digeser
$tgl = ! empty($hasil['tanggal'])
    ? (new DateTime($hasil['tanggal'], new DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i') . ' WIB'
    : (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i') . ' WIB';
?>
<style>
  body { font-size: 10pt; color: #222; }
  td, th { vertical-align: top; }
  .box { border: 0.5pt solid #ccc; padding: 8pt; }
</style>

<p style="font-size:9pt;color:#555;">Tanggal pengisian: <?= $tgl ?></p>

<table style="width:100%;border-collapse:separate;border-spacing:6pt 0;">
  <tr>
    <?php foreach ($desk['subskala'] as $s): ?>
      <td class="box" style="width:33%;border-top:3pt solid <?= $s['color'] ?>;">
        <div style="font-weight:bold;font-size:10.5pt;"><?= $s['label'] ?></div>
        <div style="font-size:22pt;font-weight:bold;color:<?= $s['color'] ?>;"><?= (int) $s['skor'] ?></div>
        <div style="font-size:8pt;color:#666;">skor</div>
        <div style="font-weight:bold;color:<?= $warnaKat[$s['kategori']] ?? '#333' ?>;margin:3pt 0;"><?= $labelKat($s['kategori']) ?></div>
        <div style="font-size:8.5pt;line-height:1.5;"><?= esc($s['interpretasi']) ?></div>
      </td>
    <?php endforeach ?>
  </tr>
</table>

<div class="box" style="margin-top:12pt;border-left:3pt solid <?= $desk['meta']['border'] ?>;background-color:#f7f7f7;">
  <div style="font-weight:bold;color:<?= $desk['meta']['color'] ?>;margin-bottom:3pt;">Rekomendasi</div>
  <div style="line-height:1.6;"><?= esc($desk['rekomendasi']) ?></div>
</div>

<p style="font-size:8.5pt;color:#555;margin-top:14pt;">
  Hasil ini adalah skrining awal, <b>bukan diagnosis</b>. Untuk penilaian yang akurat, konsultasikan dengan tenaga profesional
  (psikolog/layanan kesehatan mental).
</p>
