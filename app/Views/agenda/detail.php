<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($agenda['nama_kegiatan']) ?><?= $this->endSection() ?>

<?= $this->section('meta_description') ?><?= esc(mb_strimwidth(strip_tags((string) $agenda['deskripsi']), 0, 150, '…')) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
$efektif = \App\Models\AgendaKegiatanModel::statusEfektif($agenda);
$selesai = \App\Models\AgendaKegiatanModel::sudahSelesai($agenda);
$wa      = wa_link($agenda['kontak']);
$narasumber = array_filter(array_map('trim', preg_split('/\R/', (string) $agenda['narasumber'])));
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <a href="<?= base_url('psikoedukasi') ?>" class="text-muted text-decoration-none small"><i class="ti tabler-arrow-left me-1"></i>Semua kegiatan</a>

      <div class="card shadow-sm mt-3 overflow-hidden">
        <?php if ($agenda['poster']): ?>
          <div class="text-center pt-4 px-4" style="background:#f8f9fa;">
            <img src="<?= base_url($agenda['poster']) ?>" alt="<?= esc($agenda['nama_kegiatan']) ?>"
                 class="w-100 rounded-top" style="aspect-ratio:4/5;object-fit:cover;max-width:480px;">
          </div>
        <?php endif ?>
        <div class="card-body p-4 p-md-5">
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <span class="badge bg-label-primary"><?= esc(\App\Models\AgendaKegiatanModel::JENIS[$agenda['jenis']] ?? $agenda['jenis']) ?></span>
            <span class="badge <?= $selesai ? 'bg-label-secondary' : ($efektif === 'dibuka' ? 'bg-label-success' : 'bg-label-warning') ?>">
              <?= $selesai ? 'Telah selesai' : ($efektif === 'dibuka' ? 'Pendaftaran dibuka' : 'Pendaftaran ditutup') ?>
            </span>
          </div>
          <h2 class="fw-bold mb-1" style="color:var(--smhws-dark);"><?= esc($agenda['nama_kegiatan']) ?></h2>
          <?php if ($agenda['tema']): ?><p class="text-muted fs-5 mb-4">Tema: <?= esc($agenda['tema']) ?></p><?php endif ?>

          <?php if ($agenda['deskripsi']): ?>
            <div class="mb-4" style="line-height:1.7;"><?= nl2br(esc($agenda['deskripsi'])) ?></div>
          <?php endif ?>

          <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="fw-semibold small text-muted">Waktu</div><i class="ti tabler-calendar me-1"></i><?= esc(rentang_tanggal_id($agenda['tanggal_mulai'], $agenda['tanggal_selesai'])) ?></div>
            <div class="col-md-6"><div class="fw-semibold small text-muted">Lokasi</div><i class="ti tabler-map-pin me-1"></i><?= esc($agenda['lokasi']) ?></div>
            <div class="col-md-6"><div class="fw-semibold small text-muted">Biaya</div><i class="ti tabler-ticket me-1"></i><?= esc(biaya_label($agenda)) ?></div>
            <?php if ($agenda['batas_pendaftaran']): ?>
              <div class="col-md-6"><div class="fw-semibold small text-muted">Batas Pendaftaran</div><i class="ti tabler-clock me-1"></i><?= esc(tanggal_id($agenda['batas_pendaftaran'], true)) ?></div>
            <?php endif ?>
            <?php if ($narasumber): ?>
              <div class="col-12"><div class="fw-semibold small text-muted">Narasumber</div>
                <ul class="mb-0 ps-3"><?php foreach ($narasumber as $n): ?><li><?= esc($n) ?></li><?php endforeach ?></ul>
              </div>
            <?php endif ?>
            <?php if ($agenda['kontak']): ?>
              <div class="col-12"><div class="fw-semibold small text-muted">Kontak</div>
                <i class="ti tabler-phone me-1"></i><?= esc($agenda['kontak']) ?>
                <?php if ($wa): ?><a href="<?= esc($wa) ?>" target="_blank" rel="noopener" class="ms-2 small">Chat WhatsApp</a><?php endif ?>
              </div>
            <?php endif ?>
          </div>

          <?php if ($agenda['link_pendaftaran'] && $efektif === 'dibuka'): ?>
            <a href="<?= esc($agenda['link_pendaftaran']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
              <i class="ti tabler-external-link me-1"></i>Daftar Sekarang
            </a>
          <?php else: ?>
            <button type="button" class="btn btn-secondary" disabled><?= $selesai ? 'Kegiatan telah selesai' : 'Pendaftaran ditutup' ?></button>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
