<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Ask Our Psychologist<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
use App\Models\PertanyaanPsikologModel as P;
$th   = 'px-4 py-3 fw-semibold text-muted';
$thSt = 'font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;';
?>
<div class="mb-4">
  <h4 class="fw-bold mb-1" style="color:#1a2b40;">Ask Our Psychologist</h4>
  <p class="text-muted mb-0" style="font-size:.875rem;">Bagikan pertanyaan mahasiswa ke psikolog dan terbitkan tanya-jawab anonim ke SMHWS Learn.</p>
</div>

<?= $this->include('tanya/_flash') ?>

<ul class="nav nav-pills mb-4 flex-wrap gap-1">
  <?php foreach (P::STATUS as $kode => [$label]): ?>
    <li class="nav-item">
      <a class="nav-link <?= $status === $kode ? 'active' : '' ?>" href="<?= base_url('admin/tanya?status=' . $kode) ?>">
        <?= $label ?> <span class="badge <?= $status === $kode ? 'bg-white text-primary' : 'bg-label-primary' ?> ms-1"><?= $jumlah[$kode] ?></span>
      </a>
    </li>
  <?php endforeach ?>
</ul>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($list)): ?>
      <div class="text-center py-5 text-muted">Tidak ada pertanyaan dengan status ini.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem;">
          <thead style="background:#f8f9fa;border-bottom:2px solid #e9ecef;">
            <tr>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Pertanyaan</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Penanya</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Psikolog</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Dikirim</th>
              <th class="<?= $th ?> text-end" style="<?= $thSt ?>"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $q): ?>
              <tr>
                <td class="px-4 py-3" style="max-width:340px;">
                  <div class="fw-semibold"><?= esc($q['judul']) ?></div>
                  <div class="d-flex gap-1 mt-1 flex-wrap">
                    <?php if ($q['topik_nama']): ?><span class="badge bg-label-secondary"><?= esc($q['topik_nama']) ?></span><?php endif ?>
                    <?php if ($q['izin_publikasi']): ?><span class="badge bg-label-info"><i class="ti tabler-world"></i> Boleh publikasi</span><?php endif ?>
                    <?php if ($q['learn_konten_id']): ?><span class="badge bg-label-success">Di Learn</span><?php endif ?>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div><?= esc($q['nama']) ?></div>
                  <div class="text-muted small">Smt <?= (int) $q['semester'] ?> · <?= esc($q['prodi']) ?></div>
                </td>
                <td class="px-4 py-3"><?= $q['konselor_id'] ? esc(P::namaPsikolog($q)) : '<span class="text-muted">—</span>' ?></td>
                <td class="px-4 py-3 text-nowrap"><?= esc(tanggal_id($q['created_at'], true)) ?></td>
                <td class="px-4 py-3 text-end">
                  <a href="<?= base_url('admin/tanya/' . $q['id']) ?>" class="btn btn-sm btn-outline-primary"><?= $q['status'] === 'menunggu' ? 'Tugaskan' : 'Detail' ?></a>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <div class="p-3"><?= $pager->links() ?></div>
    <?php endif ?>
  </div>
</div>
<?= $this->endSection() ?>
