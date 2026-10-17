<!doctype html>
<html lang="id" class="layout-wide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="<?= base_url('assets/') ?>">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Dalam Pengembangan | SMHWS - UMS</title>
  <meta name="description" content="SIM SMHWS UMS saat ini sedang dalam proses pengembangan." />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/pages/page-misc.css') ?>" />

  <!-- SMHWS Custom Colors -->
  <link rel="stylesheet" href="<?= base_url('assets/css/smhws.css') ?>" />

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--smhws-bg);
    }

    .misc-wrapper {
      padding: 2rem 1.5rem;
    }

    .misc-logo img {
      height: 44px;
    }

    .misc-illustration {
      max-inline-size: 320px;
      inline-size: 100%;
      margin-block: 2rem;
    }

    .misc-contact-pill {
      background: #fff;
      border: 1px solid rgba(var(--smhws-primary-rgb), .18);
      border-radius: .75rem;
      padding: .9rem 1.5rem;
    }
  </style>
</head>

<body>
  <div class="misc-wrapper">

    <div class="misc-logo mb-6">
      <img src="<?= base_url('myimg/logo_smhws_ums_transparent.png') ?>" alt="SMHWS UMS">
    </div>

    <span class="badge bg-label-warning px-3 py-2 mb-4">
      <i class="ti tabler-tools me-1"></i>Dalam Proses Pengembangan
    </span>

    <h2 class="fw-extrabold mb-3" style="color:var(--smhws-dark);">
      Mohon Maaf, SIM SMHWS Belum Dapat Diakses
    </h2>

    <p class="mb-2 mx-auto" style="max-width:560px;color:#4a6378;line-height:1.75;">
      Saat ini Sistem Informasi Manajemen SMHWS (<strong>smhws@ums.ac.id</strong>) masih dalam proses
      pengembangan sehingga belum dapat diakses. Kami mohon maaf atas ketidaknyamanan ini.
    </p>
    <p class="mb-0 mx-auto" style="max-width:560px;color:#4a6378;line-height:1.75;">
      Silakan kembali lagi di lain waktu. Kami akan segera menginformasikan begitu layanan sudah siap digunakan.
    </p>

    <img src="<?= base_url('assets/img/illustrations/page-misc-under-maintenance.png') ?>"
      alt="Sedang dalam pengembangan" class="misc-illustration">

    <div class="misc-contact-pill d-inline-flex align-items-center gap-2">
      <i class="ti tabler-mail text-primary fs-5"></i>
      <span style="color:#555;">Pertanyaan? Hubungi kami di</span>
      <a href="mailto:smhws@ums.ac.id" class="fw-semibold text-primary">smhws@ums.ac.id</a>
    </div>

  </div>
</body>

</html>
