<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Psychoeducation, Webinar &amp; Workshop<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Agenda kegiatan psychoeducation, webinar, dan workshop kesehatan mental untuk mahasiswa.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
$chip = fn(?string $kode, string $label) => '<a href="' . base_url('psikoedukasi' . ($kode ? '?jenis=' . $kode : '')) . '" class="btn btn-sm '
    . (($jenisAktif ?? null) === $kode ? 'btn-primary' : 'btn-label-primary') . '">' . esc($label) . '</a>';
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="text-center mb-4">
    <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Psychoeducation, Webinar &amp; Workshop</h2>
    <p class="text-muted mb-0">Agenda kegiatan pengembangan diri dan kesehatan mental &amp; wellbeing.</p>
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
    <?= $chip(null, 'Semua') ?>
    <?php foreach ($jenisList as $kode => $label): ?><?= $chip($kode, $label) ?><?php endforeach ?>
  </div>

  <ul class="nav nav-tabs justify-content-center mb-4" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabMendatang" type="button" role="tab">Akan Datang (<?= count($akanDatang) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSelesai" type="button" role="tab">Telah Selesai (<?= count($telahSelesai) ?>)</button></li>
  </ul>

  <div class="tab-content p-0 shadow-none bg-transparent">
    <div class="tab-pane fade show active" id="tabMendatang" role="tabpanel">
      <?php if ($akanDatang): ?>
        <div class="row g-4"><?php foreach ($akanDatang as $row): ?><?= view('agenda/_kartu', ['row' => $row]) ?><?php endforeach ?></div>
      <?php else: ?>
        <div class="text-center text-muted py-5"><i class="ti tabler-calendar-off" style="font-size:2.5rem;display:block;opacity:.4;"></i>Belum ada kegiatan yang akan datang.</div>
      <?php endif ?>
    </div>
    <div class="tab-pane fade" id="tabSelesai" role="tabpanel">
      <?php if ($telahSelesai): ?>
        <div class="row g-4"><?php foreach ($telahSelesai as $row): ?><?= view('agenda/_kartu', ['row' => $row]) ?><?php endforeach ?></div>
      <?php else: ?>
        <div class="text-center text-muted py-5">Belum ada kegiatan yang telah selesai.</div>
      <?php endif ?>
    </div>
  </div>

  <div class="text-center mt-5">
    <a href="<?= base_url() ?>" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Kembali ke Beranda</a>
  </div>
</div>
<?= $this->endSection() ?>
