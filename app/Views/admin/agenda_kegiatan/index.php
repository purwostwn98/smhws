<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Agenda Kegiatan<?= $this->endSection() ?>
<?php
helper('tanggal');
$list = $list ?? [];
$th   = 'px-4 py-3 fw-semibold text-muted';
$thSt = 'font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;';
?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Agenda Kegiatan</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Kelola agenda Psychoeducation, Webinar &amp; Workshop yang tampil di halaman publik.
    </p>
  </div>
  <a href="<?= base_url('admin/agenda-kegiatan/buat') ?>" class="btn btn-primary d-flex align-items-center gap-2">
    <i class="ti tabler-plus"></i>Tambah Kegiatan
  </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($list)): ?>
      <div class="text-center py-5 text-muted">
        <i class="ti tabler-calendar-event" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
        <div style="font-size:.9rem;">Belum ada agenda kegiatan.</div>
        <a href="<?= base_url('admin/agenda-kegiatan/buat') ?>" class="btn btn-sm btn-primary mt-3">
          <i class="ti tabler-plus me-1"></i>Tambah Sekarang
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem;">
          <thead style="background:#f8f9fa;border-bottom:2px solid #e9ecef;">
            <tr>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Kegiatan</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Jenis</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Waktu</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Biaya</th>
              <th class="<?= $th ?> text-center" style="<?= $thSt ?>">Pendaftaran</th>
              <th class="<?= $th ?> text-end" style="<?= $thSt ?>">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $row):
              $efektif = \App\Models\AgendaKegiatanModel::statusEfektif($row);
              $selesai = \App\Models\AgendaKegiatanModel::sudahSelesai($row);
            ?>
              <tr>
                <td class="px-4 py-3" style="max-width:280px;">
                  <div class="fw-semibold"><?= esc($row['nama_kegiatan']) ?></div>
                  <div class="text-muted small"><i class="ti tabler-map-pin me-1"></i><?= esc($row['lokasi']) ?></div>
                </td>
                <td class="px-4 py-3"><span class="badge bg-label-primary"><?= esc(\App\Models\AgendaKegiatanModel::JENIS[$row['jenis']] ?? $row['jenis']) ?></span></td>
                <td class="px-4 py-3">
                  <?= esc(rentang_tanggal_id($row['tanggal_mulai'], $row['tanggal_selesai'])) ?>
                  <?php if ($selesai): ?><span class="badge bg-label-secondary ms-1">Selesai</span><?php endif ?>
                </td>
                <td class="px-4 py-3"><?= esc(biaya_label($row)) ?></td>
                <td class="px-4 py-3 text-center">
                  <span class="badge <?= $efektif === 'dibuka' ? 'bg-label-success' : 'bg-label-secondary' ?>"><?= $efektif === 'dibuka' ? 'Dibuka' : 'Ditutup' ?></span>
                  <?php if ($row['status'] === 'dibuka' && $efektif === 'ditutup'): ?>
                    <div class="text-muted" style="font-size:.7rem;">otomatis</div>
                  <?php endif ?>
                </td>
                <td class="px-4 py-3 text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <form action="<?= base_url('admin/agenda-kegiatan/toggle/' . $row['id']) ?>" method="post">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-<?= $row['status'] === 'dibuka' ? 'warning' : 'success' ?>"
                              title="<?= $row['status'] === 'dibuka' ? 'Tutup pendaftaran' : 'Buka pendaftaran' ?>">
                        <i class="ti tabler-toggle-<?= $row['status'] === 'dibuka' ? 'right' : 'left' ?>"></i>
                      </button>
                    </form>
                    <a href="<?= base_url('admin/agenda-kegiatan/edit/' . $row['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Edit">
                      <i class="ti tabler-pencil"></i>
                    </a>
                    <form action="<?= base_url('admin/agenda-kegiatan/hapus/' . $row['id']) ?>" method="post"
                          onsubmit="return confirm('Hapus kegiatan &quot;<?= esc($row['nama_kegiatan'], 'js') ?>&quot;?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="ti tabler-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php endif ?>
  </div>
</div>

<?= $this->endSection() ?>
