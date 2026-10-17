<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Hasil Mental Health Check<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$hasil = $hasil ?? session()->getFlashdata('hasil');
$desk = \App\Libraries\DassDeskripsi::susun($hasil);
$dassSubscales = $desk['subskala'];
$rm = $desk['meta'];
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <div class="text-center mb-5">
        <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Hasil Mental Health Check</h2>
        <p class="text-muted mb-0">Terima kasih sudah mengisi skrining DASS-21.</p>
      </div>

      <div class="card mb-4 shadow-sm">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="ti tabler-brain me-2" style="color:#f0a500;"></i>Hasil Asesmen</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php foreach ($dassSubscales as $s): $interp = $s['interpretasi']; ?>
              <div class="col-md-4">
                <div class="h-100 rounded-3 p-3" style="background:#f8f9fa;border-top:3px solid <?= $s['color'] ?>;">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ti <?= $s['icon'] ?>" style="font-size:1.1rem;color:<?= $s['color'] ?>;"></i>
                    <span class="fw-semibold" style="font-size:.88rem;"><?= $s['label'] ?></span>
                    <span class="badge ms-auto <?= \App\Models\DassAssessmentModel::badgeKelas($s['kategori']) ?>"><?= \App\Models\DassAssessmentModel::labelKategori($s['kategori']) ?></span>
                  </div>
                  <div class="d-flex align-items-end gap-1 mb-2">
                    <span class="fw-bold" style="font-size:2rem;color:<?= $s['color'] ?>;line-height:1;"><?= $s['skor'] ?></span>
                    <span class="text-muted mb-1" style="font-size:.73rem;">skor</span>
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

          <div class="mt-3 p-3 rounded-3 d-flex gap-3 align-items-start"
            style="background:<?= $rm['bg'] ?>;border-left:3px solid <?= $rm['border'] ?>;">
            <i class="ti <?= $rm['icon'] ?> flex-shrink-0 mt-1" style="font-size:1.3rem;color:<?= $rm['color'] ?>;"></i>
            <div>
              <div class="fw-semibold mb-1" style="font-size:.83rem;color:<?= $rm['color'] ?>;">Rekomendasi</div>
              <div style="font-size:.82rem;line-height:1.6;color:#444;"><?= $desk['rekomendasi'] ?></div>
            </div>
          </div>
        </div>
      </div>

      <p class="text-muted small">
        Hasil ini adalah skrining awal, <strong>bukan diagnosis</strong>. Untuk penilaian yang akurat, konsultasikan dengan tenaga profesional.
      </p>

      <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
        <a href="<?= base_url('skrining/dass/hasil/pdf') ?>" class="btn btn-outline-primary"><i class="ti tabler-download me-1"></i> Unduh PDF</a>
        <a href="<?= base_url('login') ?>" class="btn btn-primary"><i class="ti tabler-calendar-plus me-1"></i> Buat Janji Konseling</a>
        <a href="<?= base_url('skrining') ?>" class="btn btn-label-secondary">Pilih Assessment Lain</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>