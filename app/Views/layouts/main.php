<!doctype html>
<html lang="id" class="layout-navbar-fixed layout-wide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="<?= base_url('assets/') ?>" data-template="front-pages">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title><?= $this->renderSection('title') ?> | SMHWS - UMS</title>
  <meta name="description" content="<?= $this->renderSection('meta_description') ?>" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon/favicon.ico') ?>" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/fonts/iconify-icons.css') ?>" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/node-waves/node-waves.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/core.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/demo.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/pages/front-page.css') ?>" />

  <!-- Swiper -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/libs/swiper/swiper.css') ?>" />

  <!-- Landing page CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/css/pages/front-page-landing.css') ?>" />

  <!-- SMHWS Custom Colors -->
  <link rel="stylesheet" href="<?= base_url('assets/css/smhws.css') ?>" />

  <?= $this->renderSection('extra_css') ?>

  <!-- Helpers & Config -->
  <script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/template-customizer.js') ?>"></script>
  <script src="<?= base_url('assets/js/front-config.js') ?>"></script>
</head>

<body>
  <script src="<?= base_url('assets/vendor/js/dropdown-hover.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/mega-dropdown.js') ?>"></script>

  <?= $this->include('partials/_navbar') ?>

  <div data-bs-spy="scroll" class="scrollspy-example">
    <?= $this->renderSection('content') ?>
  </div>

  <?= $this->include('partials/_footer') ?>

  <!-- Core JS -->
  <script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/libs/node-waves/node-waves.js') ?>"></script>

  <!-- Swiper JS -->
  <script src="<?= base_url('assets/vendor/libs/swiper/swiper.js') ?>"></script>

  <!-- Front Page JS -->
  <script src="<?= base_url('assets/js/front-main.js') ?>"></script>

  <?= $this->renderSection('extra_js') ?>
</body>

</html>