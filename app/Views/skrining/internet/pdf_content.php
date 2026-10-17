<?php
use App\Libraries\PolaInternet;

// 'tanggal' sudah dalam Asia/Jakarta (diisi saat submit)
$tgl = ! empty($hasil['tanggal'])
    ? (new DateTime($hasil['tanggal'], new DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i') . ' WIB'
    : (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('d-m-Y H:i') . ' WIB';
$warna = PolaInternet::warna($hasil['kategori']);
?>
<style>
  body { font-size: 10pt; color: #222; }
  .box { border: 0.5pt solid #ccc; padding: 10pt; }
</style>

<p style="font-size:9pt;color:#555;">Tanggal pengisian: <?= $tgl ?></p>

<div class="box" style="text-align:center;border-top:3pt solid <?= $warna ?>;">
  <div style="font-size:9pt;color:#666;">Skor Total</div>
  <div style="font-size:28pt;font-weight:bold;color:<?= $warna ?>;"><?= (int) $hasil['skor_total'] ?> <span style="font-size:12pt;color:#888;font-weight:normal;">/ <?= PolaInternet::SKOR_MAKS ?></span></div>
</div>

<div class="box" style="margin-top:12pt;border-left:3pt solid <?= $warna ?>;background-color:#f7f7f7;">
  <div style="font-weight:bold;color:<?= $warna ?>;margin-bottom:3pt;">Kesimpulan &amp; Rekomendasi</div>
  <div style="line-height:1.6;"><?= esc(PolaInternet::rekomendasi($hasil['kategori'])) ?></div>
</div>

<p style="font-size:8.5pt;color:#555;margin-top:14pt;line-height:1.5;">
  <b>Catatan:</b> <?= esc(PolaInternet::CATATAN) ?>
</p>
