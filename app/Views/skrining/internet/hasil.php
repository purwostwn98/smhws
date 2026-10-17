<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Hasil Pola Penggunaan Internet<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Libraries\PolaInternet;

// Nama kategori sengaja tidak ditampilkan ke mahasiswa (hanya skor & rekomendasi)
$skor   = (int) $hasil['skor_total'];
$warna  = PolaInternet::warna($hasil['kategori']);
$persen = round($skor / PolaInternet::SKOR_MAKS * 100);
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="text-center mb-5">
        <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Hasil Pola Penggunaan Internet</h2>
        <p class="text-muted mb-0">Terima kasih sudah mengisi skrining ini.</p>
      </div>

      <div class="card mb-4 shadow-sm">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <div class="text-muted small mb-1">Skor Total</div>
            <div class="fw-bold" style="font-size:3rem;line-height:1;color:<?= $warna ?>;">
              <?= $skor ?><span class="text-muted fw-normal" style="font-size:1.2rem;"> / <?= PolaInternet::SKOR_MAKS ?></span>
            </div>
            <div class="progress mt-3 mx-auto" style="height:.6rem;max-width:420px;">
              <div class="progress-bar" role="progressbar" style="width:<?= $persen ?>%;background:<?= $warna ?>;"
                   aria-valuenow="<?= $skor ?>" aria-valuemin="0" aria-valuemax="<?= PolaInternet::SKOR_MAKS ?>"></div>
            </div>
          </div>

          <div class="p-3 rounded-3 d-flex gap-3 align-items-start" style="background:#f6fafb;border-left:3px solid <?= $warna ?>;">
            <i class="ti tabler-bulb flex-shrink-0 mt-1" style="font-size:1.3rem;color:<?= $warna ?>;"></i>
            <div>
              <div class="fw-semibold mb-1" style="font-size:.85rem;color:<?= $warna ?>;">Kesimpulan &amp; Rekomendasi</div>
              <div style="font-size:.88rem;line-height:1.65;color:#444;"><?= esc(PolaInternet::rekomendasi($hasil['kategori'])) ?></div>
            </div>
          </div>
        </div>
      </div>

      <p class="text-muted small" style="line-height:1.6;">
        <strong>Catatan:</strong> <?= esc(PolaInternet::CATATAN) ?>
      </p>

      <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
        <a href="<?= base_url('skrining/internet/hasil/pdf') ?>" class="btn btn-outline-primary"><i class="ti tabler-download me-1"></i> Unduh PDF</a>
        <a href="<?= base_url('login') ?>" class="btn btn-primary"><i class="ti tabler-calendar-plus me-1"></i> Buat Janji Konseling</a>
        <a href="<?= base_url('skrining') ?>" class="btn btn-label-secondary">Pilih Assessment Lain</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
