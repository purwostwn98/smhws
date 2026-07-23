<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Instansi Rujukan<?= $this->endSection() ?>
<?php
$list   = $list   ?? [];
$counts = $counts ?? [];
?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Instansi Rujukan</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Daftar instansi/lembaga tujuan rujukan konseling.
    </p>
  </div>
  <a href="<?= base_url('admin/instansi-rujukan/buat') ?>"
     class="btn btn-primary d-flex align-items-center gap-2">
    <i class="ti tabler-plus"></i>Tambah Instansi
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
        <i class="ti tabler-building-hospital" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
        <div style="font-size:.9rem;">Belum ada data instansi rujukan.</div>
        <a href="<?= base_url('admin/instansi-rujukan/buat') ?>" class="btn btn-sm btn-primary mt-3">
          <i class="ti tabler-plus me-1"></i>Tambah Sekarang
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem;">
          <thead style="background:#f8f9fa;border-bottom:2px solid #e9ecef;">
            <tr>
              <th class="px-4 py-3 fw-semibold text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;width:3rem;">#</th>
              <th class="px-4 py-3 fw-semibold text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Instansi</th>
              <th class="px-4 py-3 fw-semibold text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Singkatan</th>
              <th class="px-4 py-3 fw-semibold text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Alamat</th>
              <th class="px-4 py-3 fw-semibold text-muted text-center" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Digunakan</th>
              <th class="px-4 py-3 fw-semibold text-muted text-end" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $i => $row):
              $used = $counts[$row['id']] ?? 0;
            ?>
            <tr>
              <td class="px-4 py-3 text-muted"><?= $i + 1 ?></td>
              <td class="px-4 py-3 fw-semibold"><?= esc($row['nama_instansi']) ?></td>
              <td class="px-4 py-3">
                <?php if ($row['singkatan']): ?>
                  <span class="badge bg-label-primary px-2 py-1" style="font-size:.78rem;"><?= esc($row['singkatan']) ?></span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif ?>
              </td>
              <td class="px-4 py-3" style="color:#555;max-width:260px;">
                <?= $row['alamat'] ? esc($row['alamat']) : '<span class="text-muted">—</span>' ?>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if ($used > 0): ?>
                  <span class="badge bg-label-info" style="font-size:.78rem;"><?= $used ?> sesi</span>
                <?php else: ?>
                  <span class="text-muted" style="font-size:.8rem;">—</span>
                <?php endif ?>
              </td>
              <td class="px-4 py-3 text-end">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="<?= base_url('admin/instansi-rujukan/edit/' . $row['id']) ?>"
                     class="btn btn-sm btn-icon btn-outline-primary"
                     title="Edit">
                    <i class="ti tabler-pencil"></i>
                  </a>
                  <form action="<?= base_url('admin/instansi-rujukan/hapus/' . $row['id']) ?>"
                        method="post"
                        onsubmit="return confirm('Hapus instansi \"<?= esc($row['nama_instansi'], 'js') ?>\"?<?= $used > 0 ? ' \\n\\n⚠️ Instansi ini digunakan pada ' . $used . ' sesi konseling. Data rujukan pada sesi tersebut akan dikosongkan.' : '' ?>')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                      <i class="ti tabler-trash"></i>
                    </button>
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
