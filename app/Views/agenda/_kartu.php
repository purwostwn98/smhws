<?php
/** Kartu satu kegiatan. Variabel: $row */
$efektif = \App\Models\AgendaKegiatanModel::statusEfektif($row);
$selesai = \App\Models\AgendaKegiatanModel::sudahSelesai($row);
?>
<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
  <div class="card h-100 shadow-sm overflow-hidden">
    <?php if (! empty($row['poster'])): ?>
      <a href="<?= base_url('psikoedukasi/' . $row['id']) ?>">
        <img src="<?= base_url($row['poster']) ?>" alt="<?= esc($row['nama_kegiatan']) ?>" class="w-100" style="aspect-ratio:4/5;object-fit:cover;">
      </a>
    <?php endif ?>
    <div class="card-body d-flex flex-column">
      <div class="d-flex gap-2 mb-2 flex-wrap">
        <span class="badge bg-label-primary"><?= esc(\App\Models\AgendaKegiatanModel::JENIS[$row['jenis']] ?? $row['jenis']) ?></span>
        <span class="badge <?= $selesai ? 'bg-label-secondary' : ($efektif === 'dibuka' ? 'bg-label-success' : 'bg-label-warning') ?>">
          <?= $selesai ? 'Telah selesai' : ($efektif === 'dibuka' ? 'Pendaftaran dibuka' : 'Pendaftaran ditutup') ?>
        </span>
      </div>
      <h6 class="fw-bold mb-1"><?= esc($row['nama_kegiatan']) ?></h6>
      <?php if ($row['tema']): ?><div class="text-muted small mb-2">Tema: <?= esc($row['tema']) ?></div><?php endif ?>
      <ul class="list-unstyled small text-muted mb-3 flex-grow-1">
        <li class="mb-1"><i class="ti tabler-calendar me-1"></i><?= esc(rentang_tanggal_id($row['tanggal_mulai'], $row['tanggal_selesai'])) ?></li>
        <li class="mb-1"><i class="ti tabler-map-pin me-1"></i><?= esc($row['lokasi']) ?></li>
        <li><i class="ti tabler-ticket me-1"></i><?= esc(biaya_label($row)) ?></li>
      </ul>
      <a href="<?= base_url('psikoedukasi/' . $row['id']) ?>" class="btn btn-label-primary btn-sm">Lihat Detail</a>
    </div>
  </div>
</div>
