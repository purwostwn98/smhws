<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Kelola Psikolog<?= $this->endSection() ?>
<?php
$konselorList = $konselorList ?? [];
$stats        = $stats ?? ['total' => 0, 'tersedia' => 0, 'total_sesi' => 0];
?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Kelola Psikolog</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Manajemen data psikolog dan ketersediaan sesi.</p>
  </div>
  <a href="<?= base_url('admin/konselor/buat') ?>" class="btn btn-primary d-flex align-items-center gap-2">
    <i class="ti tabler-user-plus"></i>Tambah Psikolog
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.12);color:#1a5f7a;font-size:1.3rem;">
            <i class="ti tabler-users"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['total'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Total Psikolog</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(45,155,110,.12);color:#2d9b6e;font-size:1.3rem;">
            <i class="ti tabler-user-check"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['tersedia'] ?></div>
          <div class="text-muted" style="font-size:.8rem;">Psikolog Tersedia</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(87,197,182,.12);color:#57c5b6;font-size:1.3rem;">
            <i class="ti tabler-history"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= number_format($stats['total_sesi']) ?></div>
          <div class="text-muted" style="font-size:.8rem;">Total Sesi Terlaksana</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabel Psikolog -->
<?php if (empty($konselorList)): ?>
  <div class="card">
    <div class="card-body text-center py-5">
      <div class="avatar avatar-xl mx-auto mb-3">
        <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.1);color:#1a5f7a;font-size:1.75rem;">
          <i class="ti tabler-user-off"></i>
        </div>
      </div>
      <h6 class="text-muted">Belum ada data psikolog</h6>
      <a href="<?= base_url('admin/konselor/buat') ?>" class="btn btn-primary mt-3">
        <i class="ti tabler-user-plus me-1"></i>Tambah Psikolog Pertama
      </a>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr style="background:#f8f9fa;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;color:#6c757d;">
              <th class="px-4 py-3">Psikolog</th>
              <th class="py-3">NIP</th>
              <th class="py-3">Spesialisasi</th>
              <th class="py-3 text-center">Rating</th>
              <th class="py-3 text-center">Total Sesi</th>
              <th class="py-3 text-center">Kapasitas/Hari</th>
              <th class="py-3 text-center">Status</th>
              <th class="py-3 text-center px-4">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($konselorList as $k): ?>
              <?php
              $inisial = strtoupper(substr($k['name'] ?? 'K', 0, 1));
              $namaLengkap = \App\Models\KonselorModel::namaLengkap($k);
              $spesialisasi = is_array($k['spesialisasi']) ? $k['spesialisasi'] : [];
              ?>
              <tr>
                <!-- Nama + Email -->
                <td class="px-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0">
                      <?php if (! empty($k['foto'])): ?>
                        <img src="<?= base_url($k['foto']) ?>" alt="<?= esc($k['name']) ?>"
                             style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:1px solid #e0e0e0;">
                      <?php else: ?>
                        <div class="avatar-initial rounded-circle fw-bold"
                          style="background:rgba(26,95,122,.15);color:#1a5f7a;font-size:.9rem;">
                          <?= $inisial ?>
                        </div>
                      <?php endif ?>
                    </div>
                    <div>
                      <div class="fw-semibold" style="color:#1a2b40;font-size:.875rem;"><?= esc($namaLengkap) ?></div>
                      <div class="text-muted" style="font-size:.78rem;"><?= esc($k['email']) ?></div>
                      <?php if (! empty($k['phone'])): ?>
                        <div class="text-muted" style="font-size:.75rem;">
                          <i class="ti tabler-phone me-1"></i><?= esc($k['phone']) ?>
                        </div>
                      <?php endif ?>
                    </div>
                  </div>
                </td>

                <!-- NIP -->
                <td class="py-3">
                  <span style="font-size:.82rem;color:#555;"><?= esc($k['nip'] ?? '-') ?></span>
                </td>

                <!-- Spesialisasi -->
                <td class="py-3" style="max-width:220px;">
                  <div class="d-flex flex-wrap gap-1">
                    <?php if (empty($spesialisasi)): ?>
                      <span class="text-muted" style="font-size:.78rem;">-</span>
                    <?php else: ?>
                      <?php foreach (array_slice($spesialisasi, 0, 3) as $sp): ?>
                        <span class="badge bg-label-primary" style="font-size:.72rem;"><?= esc($sp) ?></span>
                      <?php endforeach ?>
                      <?php if (count($spesialisasi) > 3): ?>
                        <span class="badge bg-label-secondary" style="font-size:.72rem;">+<?= count($spesialisasi) - 3 ?></span>
                      <?php endif ?>
                    <?php endif ?>
                  </div>
                </td>

                <!-- Rating -->
                <td class="py-3 text-center">
                  <div class="d-flex align-items-center justify-content-center gap-1">
                    <i class="ti tabler-star-filled" style="color:#f0a500;font-size:.85rem;"></i>
                    <span class="fw-semibold" style="font-size:.85rem;"><?= number_format((float)$k['rating'], 2) ?></span>
                  </div>
                </td>

                <!-- Total Sesi -->
                <td class="py-3 text-center">
                  <span class="fw-semibold" style="font-size:.875rem;"><?= number_format($k['total_sesi']) ?></span>
                </td>

                <!-- Kapasitas -->
                <td class="py-3 text-center">
                  <span style="font-size:.875rem;"><?= (int)$k['max_pasien_per_hari'] ?> pasien</span>
                </td>

                <!-- Status -->
                <td class="py-3 text-center">
                  <?php if ($k['is_available']): ?>
                    <span class="badge bg-label-success">Tersedia</span>
                  <?php else: ?>
                    <span class="badge bg-label-secondary">Tidak Tersedia</span>
                  <?php endif ?>
                </td>

                <!-- Aksi -->
                <td class="py-3 text-center px-4">
                  <div class="d-flex align-items-center justify-content-center gap-1">
                    <!-- Toggle ketersediaan -->
                    <form method="post" action="<?= base_url('admin/konselor/toggle/' . $k['id']) ?>">
                      <?= csrf_field() ?>
                      <button type="submit"
                        class="btn btn-sm <?= $k['is_available'] ? 'btn-label-warning' : 'btn-label-success' ?>"
                        title="<?= $k['is_available'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                        <i class="ti <?= $k['is_available'] ? 'tabler-toggle-right' : 'tabler-toggle-left' ?>"></i>
                      </button>
                    </form>

                    <!-- Edit -->
                    <a href="<?= base_url('admin/konselor/edit/' . $k['id']) ?>"
                      class="btn btn-sm btn-label-primary" title="Edit">
                      <i class="ti tabler-pencil"></i>
                    </a>

                    <!-- Hapus -->
                    <button type="button"
                      class="btn btn-sm btn-label-danger btn-hapus"
                      data-id="<?= $k['id'] ?>"
                      data-nama="<?= esc($namaLengkap) ?>"
                      title="Hapus">
                      <i class="ti tabler-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif ?>

<!-- Modal Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="avatar avatar-lg mx-auto mb-3">
          <div class="avatar-initial rounded-circle" style="background:rgba(220,53,69,.1);color:#dc3545;font-size:1.5rem;">
            <i class="ti tabler-trash"></i>
          </div>
        </div>
        <h5 class="fw-bold mb-1" style="color:#1a2b40;">Hapus Psikolog?</h5>
        <p class="text-muted mb-4" style="font-size:.875rem;">
          <strong id="hapusNama"></strong> akan dihapus. Akun login psikolog juga akan dinonaktifkan.
        </p>
        <form id="formHapus" method="post" action="">
          <?= csrf_field() ?>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">
              <i class="ti tabler-trash me-1"></i>Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.btn-hapus').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('hapusNama').textContent = this.dataset.nama;
      document.getElementById('formHapus').action = '<?= base_url('admin/konselor/hapus/') ?>' + this.dataset.id;
      new bootstrap.Modal(document.getElementById('modalHapus')).show();
    });
  });
</script>

<?= $this->endSection() ?>
