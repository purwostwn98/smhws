<?php
use App\Libraries\DokumenTerbit;

$jenisLabel = ['univ' => 'Laporan Universitas', 'fakultas' => 'Laporan Fakultas', 'prodi' => 'Laporan Program Studi'];
?>
<!doctype html>
<html lang="id" class="layout-wide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="<?= base_url('assets/') ?>">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Verifikasi Dokumen | SMHWS - UMS</title>

  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/smhws.css') ?>" />

  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--smhws-bg); }
    .verif-wrap { max-width: 640px; margin: 0 auto; padding: 2.5rem 1rem; }
    .verif-logo img { height: 44px; }
    .verif-table th { width: 38%; color: #6b7c8f; font-weight: 500; font-size: .85rem; vertical-align: top; }
    .verif-table td { font-size: .9rem; color: var(--smhws-dark); word-break: break-word; }
    .verif-hash { font-family: ui-monospace, Menlo, monospace; font-size: .75rem !important; color: #555 !important; }
  </style>
</head>

<body>
  <div class="verif-wrap">

    <div class="verif-logo text-center mb-5">
      <img src="<?= base_url('myimg/logo_smhws_ums_transparent.png') ?>" alt="SMHWS UMS">
    </div>

    <?php if (! $dokumen): ?>

      <div class="card shadow-sm">
        <div class="card-body text-center p-6">
          <i class="ti tabler-file-alert text-danger" style="font-size:3rem;"></i>
          <h4 class="fw-bold mt-3 mb-2" style="color:var(--smhws-dark);">Dokumen Tidak Ditemukan</h4>
          <p class="text-muted mb-0">
            Kode verifikasi ini tidak terdaftar sebagai dokumen yang diterbitkan oleh SMHWS UMS.
            Dokumen yang memuat kode ini <strong>tidak dapat dipastikan keasliannya</strong>.
          </p>
        </div>
      </div>

    <?php else: ?>

      <div class="card shadow-sm">
        <div class="card-body p-5">

          <?php if ($utuh): ?>
            <div class="alert alert-success d-flex align-items-start gap-2 mb-5" role="alert">
              <i class="ti tabler-rosette-discount-check mt-1" style="font-size:1.4rem;"></i>
              <div>
                <div class="fw-semibold">Dokumen Asli dan Terverifikasi</div>
                <div style="font-size:.85rem;">Dokumen ini diterbitkan dan ditandatangani secara elektronik melalui sistem SMHWS UMS.</div>
              </div>
            </div>
          <?php else: ?>
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-5" role="alert">
              <i class="ti tabler-alert-triangle mt-1" style="font-size:1.4rem;"></i>
              <div>
                <div class="fw-semibold">Dokumen Terdaftar, Salinan Tidak Tersedia</div>
                <div style="font-size:.85rem;">Dokumen ini tercatat diterbitkan SMHWS UMS, tetapi salinan aslinya tidak lagi tersedia untuk dicocokkan. Hubungi SMHWS UMS untuk konfirmasi.</div>
              </div>
            </div>
          <?php endif ?>

          <h5 class="fw-bold mb-1" style="color:var(--smhws-dark);"><?= esc($dokumen['judul']) ?></h5>
          <span class="badge bg-label-primary mb-4"><?= esc($jenisLabel[$dokumen['jenis']] ?? 'Laporan') ?></span>

          <table class="table table-borderless verif-table mb-0">
            <tr><th>Cakupan</th><td><?= esc($dokumen['cakupan']) ?></td></tr>
            <tr><th>Periode</th><td><?= esc($dokumen['periode']) ?></td></tr>
            <tr><th>Ditandatangani oleh</th><td class="fw-semibold"><?= esc($dokumen['penandatangan_nama']) ?></td></tr>
            <tr><th>Jabatan</th><td><?= esc($dokumen['penandatangan_jabatan']) ?></td></tr>
            <tr><th>Waktu terbit</th><td><?= DokumenTerbit::tanggalIndonesia($dokumen['created_at']) ?>, pukul <?= date('H.i', strtotime($dokumen['created_at'])) ?> WIB</td></tr>
            <tr><th>ID Dokumen</th><td class="verif-hash"><?= esc($dokumen['token']) ?></td></tr>
            <tr><th>Sidik Jari (SHA-256)</th><td class="verif-hash"><?= esc($dokumen['file_hash']) ?></td></tr>
          </table>

          <?php if ($utuh): ?>
            <div class="d-grid mt-4">
              <a href="<?= base_url('dokumen/' . $dokumen['token'] . '/salinan') ?>" target="_blank" rel="noopener" class="btn btn-primary">
                <i class="ti tabler-file-type-pdf me-2"></i>Lihat Salinan Dokumen
              </a>
            </div>
            <p class="text-muted text-center mt-3 mb-0" style="font-size:.75rem;">
              Cocokkan isi dokumen yang Anda terima dengan salinan ini. Perbedaan isi menandakan dokumen telah diubah.
            </p>
          <?php endif ?>

        </div>
      </div>

    <?php endif ?>

    <p class="text-center text-muted mt-4 mb-0" style="font-size:.75rem;">
      Student Mental Health and Wellbeing Support &middot; Universitas Muhammadiyah Surakarta
    </p>
  </div>
</body>

</html>
