<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Data Mahasiswa<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>
  #mahasiswaTable thead th { color: #fff !important; }
  #mahasiswaTable_wrapper .dataTables_length,
  #mahasiswaTable_wrapper .dataTables_filter  { padding: 12px 16px 4px; }
  #mahasiswaTable_wrapper .dataTables_info,
  #mahasiswaTable_wrapper .dataTables_paginate { padding: 8px 16px 12px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$mahasiswaList = $mahasiswaList ?? [];
$stats         = $stats ?? ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'bulan_ini' => 0];
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Data Mahasiswa</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Daftar seluruh mahasiswa terdaftar di SMHWS UMS</p>
  </div>
  <span class="badge bg-primary fs-6"><?= $stats['total'] ?> mahasiswa</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-sm-3">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.12);color:#1a5f7a;font-size:1.3rem;">
            <i class="ti tabler-users"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['total'] ?></div>
          <div class="text-muted" style="font-size:.78rem;">Total</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(45,155,110,.12);color:#2d9b6e;font-size:1.3rem;">
            <i class="ti tabler-user-check"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['aktif'] ?></div>
          <div class="text-muted" style="font-size:.78rem;">Aktif</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(220,53,69,.1);color:#dc3545;font-size:1.3rem;">
            <i class="ti tabler-user-off"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['nonaktif'] ?></div>
          <div class="text-muted" style="font-size:.78rem;">Nonaktif</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-3">
    <div class="card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded-circle" style="background:rgba(87,197,182,.12);color:#57c5b6;font-size:1.3rem;">
            <i class="ti tabler-calendar-plus"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold fs-4" style="color:#1a2b40;"><?= $stats['bulan_ini'] ?></div>
          <div class="text-muted" style="font-size:.78rem;">Daftar Bulan Ini</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="mahasiswaTable" class="table table-hover align-middle mb-0 w-100">
        <thead style="background:#1a2b40;">
          <tr style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">
            <th class="px-3 py-3 text-white text-center" style="width:48px;">No</th>
            <th class="py-3 text-white">Mahasiswa</th>
            <th class="py-3 text-white">NIM</th>
            <th class="py-3 text-white">Fakultas</th>
            <th class="py-3 text-white">Program Studi</th>
            <th class="py-3 text-white">No HP</th>
            <th class="py-3 text-white text-center">Status</th>
            <th class="py-3 text-white px-3">Tanggal Daftar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mahasiswaList as $i => $m): ?>
            <?php $inisial = strtoupper(substr($m['name'] ?? 'M', 0, 1)); ?>
            <tr>
              <!-- No -->
              <td class="px-3 text-center text-muted" style="font-size:.82rem;"><?= $i + 1 ?></td>

              <!-- Nama + Email -->
              <td class="py-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-initial rounded-circle fw-bold flex-shrink-0"
                    style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(26,95,122,.15);color:#1a5f7a;font-size:.85rem;">
                    <?= $inisial ?>
                  </div>
                  <div>
                    <div class="fw-semibold" style="color:#1a2b40;font-size:.875rem;"><?= esc($m['name']) ?></div>
                    <div class="text-muted" style="font-size:.78rem;"><?= esc($m['email']) ?></div>
                  </div>
                </div>
              </td>

              <!-- NIM -->
              <td class="py-3">
                <span class="font-monospace" style="font-size:.82rem;color:#444;"><?= esc($m['uniid'] ?? '-') ?></span>
              </td>

              <!-- Fakultas -->
              <td class="py-3" style="max-width:180px;">
                <span style="font-size:.82rem;color:#555;"><?= esc($m['fakultas'] ?? '-') ?></span>
              </td>

              <!-- Program Studi -->
              <td class="py-3" style="max-width:200px;">
                <span style="font-size:.82rem;color:#555;"><?= esc($m['prodi'] ?? '-') ?></span>
              </td>

              <!-- No HP -->
              <td class="py-3">
                <?php if (! empty($m['phone'])): ?>
                  <span style="font-size:.82rem;color:#555;"><?= esc($m['phone']) ?></span>
                <?php else: ?>
                  <span class="text-muted" style="font-size:.82rem;">-</span>
                <?php endif ?>
              </td>

              <!-- Status -->
              <td class="py-3 text-center">
                <?php if ((int)$m['is_active'] === 1): ?>
                  <span class="badge bg-label-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-label-secondary">Nonaktif</span>
                <?php endif ?>
              </td>

              <!-- Tanggal Daftar -->
              <td class="py-3 px-3">
                <?php if (! empty($m['created_at'])): ?>
                  <span style="font-size:.82rem;color:#555;">
                    <?= date('d M Y', strtotime($m['created_at'])) ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted" style="font-size:.82rem;">-</span>
                <?php endif ?>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="<?= base_url('assets/vendor/libs/jquery/jquery.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
$(function () {
  $('#mahasiswaTable').DataTable({
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
    scrollX: true,
    order: [[1, 'asc']],
    columnDefs: [{ orderable: false, targets: 0 }],
    language: {
      search: 'Cari:',
      searchPlaceholder: 'Cari nama, NIM, prodi…',
      lengthMenu: 'Tampilkan _MENU_ baris',
      info: 'Menampilkan _START_–_END_ dari _TOTAL_ mahasiswa',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(difilter dari _MAX_ mahasiswa)',
      zeroRecords: 'Tidak ada mahasiswa yang cocok',
      emptyTable: 'Belum ada data mahasiswa',
      paginate: { first: '&laquo;', last: '&raquo;', next: '&rsaquo;', previous: '&lsaquo;' },
    },
  });
});
</script>
<?= $this->endSection() ?>
