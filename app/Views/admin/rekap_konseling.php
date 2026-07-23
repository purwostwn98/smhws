<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Rekap Konseling<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>">
<style>#rekapTable thead th { color: #fff !important; }</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$filters         = $filters         ?? [];
$rows            = $rows            ?? [];
$allProdi        = $allProdi        ?? [];
$konselorList    = $konselorList    ?? [];
$tahunAkdOptions = $tahunAkdOptions ?? [];

$validStatus = ['dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
$statusLabel = [
    'dikonfirmasi' => 'Dikonfirmasi',
    'terjadwal'    => 'Terjadwal',
    'berlangsung'  => 'Berlangsung',
    'selesai'      => 'Selesai',
    'dibatalkan'   => 'Dibatalkan',
];
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Rekap Konseling</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Rekap seluruh sesi konseling mahasiswa SMHWS UMS
    </p>
  </div>
  <span class="badge bg-primary fs-6"><?= count($rows) ?> sesi</span>
</div>

<!-- Filter -->
<div class="card mb-4 shadow-sm border-0">
  <div class="card-body pb-2">
    <form id="filterForm" method="GET" action="<?= base_url('admin/rekap-konseling') ?>">

      <div class="row g-2 align-items-end">

        <div class="col-sm-6 col-md-3 col-xl-2">
          <label class="form-label form-label-sm mb-1">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <?php foreach ($validStatus as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
              <?= $statusLabel[$s] ?>
            </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-3">
          <label class="form-label form-label-sm mb-1">Program Studi</label>
          <select name="prodi" class="form-select form-select-sm">
            <option value="">Semua Prodi</option>
            <?php foreach ($allProdi as $p): ?>
            <option value="<?= esc($p['id_lembaga']) ?>"
              <?= ($filters['prodi'] ?? '') === $p['id_lembaga'] ? 'selected' : '' ?>>
              <?= esc($p['nama_prodi']) ?>
            </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-3">
          <label class="form-label form-label-sm mb-1">Psikolog</label>
          <select name="konselor_id" class="form-select form-select-sm">
            <option value="">Semua Psikolog</option>
            <?php foreach ($konselorList as $k): ?>
            <?php
              $gd    = ! empty($k['gelar_depan'])    ? $k['gelar_depan'] . ' '     : '';
              $gb    = ! empty($k['gelar_belakang']) ? ', ' . $k['gelar_belakang'] : '';
              $kNama = $gd . $k['name'] . $gb;
            ?>
            <option value="<?= $k['id'] ?>"
              <?= (string) ($filters['konselor_id'] ?? '') === (string) $k['id'] ? 'selected' : '' ?>>
              <?= esc($kNama) ?>
            </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-2 col-xl-2">
          <label class="form-label form-label-sm mb-1">Tahun Akademik</label>
          <select name="tahun_akd" class="form-select form-select-sm">
            <option value="">Semua Tahun</option>
            <?php foreach ($tahunAkdOptions as $opt): ?>
            <option value="<?= $opt ?>" <?= ($filters['tahun_akd'] ?? '') === $opt ? 'selected' : '' ?>>
              <?= $opt ?>
            </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-sm-6 col-md-2 col-xl-2">
          <label class="form-label form-label-sm mb-1">Semester</label>
          <select name="smt_akd" class="form-select form-select-sm">
            <option value="">Semua</option>
            <option value="ganjil" <?= ($filters['smt_akd'] ?? '') === 'ganjil' ? 'selected' : '' ?>>Ganjil (Jul–Des)</option>
            <option value="genap"  <?= ($filters['smt_akd'] ?? '') === 'genap'  ? 'selected' : '' ?>>Genap (Jan–Jun)</option>
          </select>
        </div>

      </div><!-- row 1 -->

      <div class="row g-2 align-items-end mt-1">

        <div class="col-sm-6 col-md-3 col-xl-2">
          <label class="form-label form-label-sm mb-1">Dari Tanggal</label>
          <input type="date" name="tgl_mulai" class="form-control form-control-sm"
            value="<?= esc($filters['tgl_mulai'] ?? '') ?>">
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
          <label class="form-label form-label-sm mb-1">Sampai Tanggal</label>
          <input type="date" name="tgl_selesai" class="form-control form-control-sm"
            value="<?= esc($filters['tgl_selesai'] ?? '') ?>">
        </div>

        <div class="col-sm-4 col-md-2 col-xl-1">
          <label class="form-label form-label-sm mb-1">JK</label>
          <select name="jk" class="form-select form-select-sm">
            <option value="">L/P</option>
            <option value="laki-laki" <?= ($filters['jk'] ?? '') === 'laki-laki' ? 'selected' : '' ?>>L</option>
            <option value="perempuan" <?= ($filters['jk'] ?? '') === 'perempuan' ? 'selected' : '' ?>>P</option>
          </select>
        </div>

      </div><!-- row 2 -->

      <div class="d-flex gap-2 justify-content-end mt-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="ti tabler-filter me-1"></i>Filter
        </button>
        <a href="<?= base_url('admin/rekap-konseling') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="ti tabler-refresh me-1"></i>Reset
        </a>
        <a id="btnExport" href="#" class="btn btn-success btn-sm">
          <i class="ti tabler-file-spreadsheet me-1"></i>Ekspor Excel
        </a>
      </div>

    </form>
  </div>
</div>

<!-- Table -->
<div class="card shadow-sm border-0">
  <div class="card-header d-flex align-items-center justify-content-between pt-3">
    <h6 class="mb-0 fw-semibold" style="color:#1a2b40;">
      <i class="ti tabler-table me-1 text-primary"></i>Data Rekap Konseling
    </h6>
    <span class="text-muted" style="font-size:.8rem;"><?= count($rows) ?> sesi ditemukan</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="rekapTable" class="table table-sm table-bordered table-hover align-middle mb-0 w-100"
             style="font-size:.78rem;">
        <thead style="background:#1a2b40;color:#fff;">
          <tr class="align-middle">
            <th class="text-center px-2 py-2 text-white" style="min-width:38px;">No</th>
            <th class="px-2 py-2 text-white" style="min-width:160px;">Psikolog</th>
            <th class="px-2 py-2 text-white" style="min-width:110px;">NIM</th>
            <th class="px-2 py-2 text-white" style="min-width:150px;">Nama Mahasiswa</th>
            <th class="text-center px-2 py-2 text-white" style="min-width:100px;">Kehadiran</th>
            <th class="text-center px-2 py-2 text-white" style="min-width:70px;"
                title="Bobot sesi ini: >75 menit = 2, lainnya = 1">Jml Sesi</th>
            <th class="text-center px-2 py-2 text-white" style="min-width:46px;">JK</th>
            <th class="px-2 py-2 text-white" style="min-width:170px;">Program Studi</th>
            <th class="text-center px-2 py-2 text-white" style="min-width:80px;">Media</th>
            <th class="px-2 py-2 text-white" style="min-width:180px;">Masalah</th>
            <th class="px-2 py-2 text-white" style="min-width:200px;">Diagnosis Problem Normal Bermasalah</th>
            <th class="px-2 py-2 text-white" style="min-width:200px;">Fokus Intervensi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
          <tr>
            <td colspan="12" class="text-center py-5 text-muted">
              <i class="ti tabler-database-off d-block mb-2" style="font-size:2.2rem;"></i>
              Tidak ada data untuk filter yang dipilih
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($rows as $i => $row): ?>
          <?php
            // Psikolog name
            $konselorNama = '—';
            if (! empty($row['konselor_name'])) {
                $gd = ! empty($row['gelar_depan'])    ? $row['gelar_depan'] . ' '     : '';
                $gb = ! empty($row['gelar_belakang']) ? ', ' . $row['gelar_belakang'] : '';
                $konselorNama = esc($gd . $row['konselor_name'] . $gb);
            }

            // Kehadiran
            [$khadirLabel, $khadirClass] = match($row['status']) {
                'selesai'     => ['Hadir',       'bg-success'],
                'berlangsung' => ['Berlangsung', 'bg-primary'],
                'dibatalkan'  => ['Tidak Hadir', 'bg-danger'],
                'terjadwal'   => ['Terjadwal',   'bg-info'],
                default       => [ucfirst($row['status']), 'bg-secondary'],
            };

            // JK
            $isLaki   = $row['jenis_kelamin'] === 'laki-laki';
            $jkLabel  = $isLaki ? 'L' : 'P';
            $jkClass  = $isLaki ? 'bg-primary' : 'bg-danger';

            // Metode
            $metodeClass = match($row['metode'] ?? '') {
                'online' => 'bg-info',
                'hybrid' => 'bg-warning',
                default  => 'bg-secondary',
            };

            // Diagnosis DSM5 & Fokus Intervensi
            $dsm5Items  = $row['diagnosis_dsm5']   ?? [];
            $fokusItems = $row['intervensi_fokus'] ?? [];

            // Tanggal sesi
            $tglSesi = ! empty($row['tanggal_konseling'])
                ? date('d/m/Y', strtotime($row['tanggal_konseling'])) : '—';

            $bobot = $row['bobot_sesi'] ?? 1;
          ?>
          <tr>
            <td class="text-center text-muted"><?= $i + 1 ?></td>
            <td><?= $konselorNama ?></td>
            <td><code style="font-size:.75rem;"><?= esc($row['uniid'] ?? '—') ?></code></td>
            <td><?= esc($row['mahasiswa_nama'] ?? '—') ?></td>
            <td class="text-center">
              <span class="badge <?= $khadirClass ?>"><?= $khadirLabel ?></span>
              <?php if (! empty($row['tanggal_konseling'])): ?>
              <div class="text-muted mt-1" style="font-size:.7rem;"><?= $tglSesi ?></div>
              <?php endif ?>
            </td>
            <td class="text-center fw-semibold">
              <?= $bobot ?>
              <?php if ($bobot === 2): ?>
              <div class="text-warning" style="font-size:.65rem;" title="Durasi >75 menit">&#9654; 2&times;</div>
              <?php endif ?>
            </td>
            <td class="text-center">
              <span class="badge <?= $jkClass ?>"><?= $jkLabel ?></span>
            </td>
            <td style="white-space:normal;word-break:break-word;"><?= esc($row['mahasiswa_prodi'] ?? '—') ?></td>
            <td class="text-center">
              <span class="badge <?= $metodeClass ?>"><?= ucfirst($row['metode'] ?? '—') ?></span>
            </td>
            <td style="white-space:normal;word-break:break-word;">
              <?php if (! empty($row['keluhan_utama'])): ?>
              <?php $ku = $row['keluhan_utama']; ?>
              <?php if (mb_strlen($ku) > 100): ?>
              <span title="<?= esc($ku) ?>" data-bs-toggle="tooltip" data-bs-placement="top">
                <?= esc(mb_strimwidth($ku, 0, 100, '…')) ?>
              </span>
              <?php else: ?>
              <?= esc($ku) ?>
              <?php endif ?>
              <?php else: ?>—<?php endif ?>
            </td>
            <td style="white-space:normal;word-break:break-word;">
              <?php if (! empty($dsm5Items)): ?>
              <ul class="mb-0 ps-3" style="list-style:disc;">
                <?php foreach ($dsm5Items as $item): ?><li><?= esc($item) ?></li><?php endforeach ?>
              </ul>
              <?php else: ?><span class="text-muted">—</span><?php endif ?>
            </td>
            <td style="white-space:normal;word-break:break-word;">
              <?php if (! empty($fokusItems)): ?>
              <ul class="mb-0 ps-3" style="list-style:disc;">
                <?php foreach ($fokusItems as $item): ?><li><?= esc($item) ?></li><?php endforeach ?>
              </ul>
              <?php else: ?><span class="text-muted">—</span><?php endif ?>
            </td>
          </tr>
          <?php endforeach ?>
          <?php endif ?>
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
  // Export URL — keep in sync with filter form
  var $form   = $('#filterForm');
  var $btnExp = $('#btnExport');
  function syncExportUrl() {
    $btnExp.attr('href', '<?= base_url('admin/rekap-konseling/export') ?>?' + $form.serialize());
  }
  syncExportUrl();
  $form.on('change input', syncExportUrl);

  // Tooltip init
  $('[data-bs-toggle="tooltip"]').each(function () {
    new bootstrap.Tooltip(this, { trigger: 'hover' });
  });

  // DataTables
  $('#rekapTable').DataTable({
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
    scrollX: true,
    order: [],
    columnDefs: [{ orderable: false, targets: 0 }],
    language: {
      search:          'Cari:',
      searchPlaceholder: 'Cari nama, NIM, masalah…',
      lengthMenu:      'Tampilkan _MENU_ baris',
      info:            'Menampilkan _START_–_END_ dari _TOTAL_ sesi',
      infoEmpty:       'Tidak ada data',
      infoFiltered:    '(difilter dari _MAX_ sesi)',
      zeroRecords:     'Tidak ada data yang cocok dengan pencarian',
      emptyTable:      'Tidak ada data untuk filter yang dipilih',
      paginate: {
        first:    '&laquo;',
        last:     '&raquo;',
        next:     '&rsaquo;',
        previous: '&lsaquo;',
      },
    },
  });
});
</script>
<?= $this->endSection() ?>
