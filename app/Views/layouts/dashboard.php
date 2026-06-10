<!doctype html>
<html lang="id" class="layout-navbar-fixed layout-menu-fixed" dir="ltr"
  data-skin="default" data-bs-theme="light"
  data-assets-path="<?= base_url('assets/') ?>"
  data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title><?= $this->renderSection('title') ?> | SMHWS Dashboard</title>

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

  <!-- SMHWS Colors -->
  <link rel="stylesheet" href="<?= base_url('assets/css/smhws.css') ?>" />

  <?= $this->renderSection('extra_css') ?>

  <script src="<?= base_url('assets/vendor/js/helpers.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/template-customizer.js') ?>"></script>
  <script src="<?= base_url('assets/js/config.js') ?>"></script>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- ===================== SIDEBAR ===================== -->
      <?= $this->include('partials/_dashboard_sidebar') ?>
      <!-- /Sidebar -->

      <div class="layout-page">

        <!-- =================== NAVBAR =================== -->
        <?= $this->include('partials/_dashboard_navbar') ?>
        <!-- /Navbar -->

        <!-- ================== CONTENT ================== -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <?= $this->renderSection('content') ?>
          </div>

          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <strong>SMHWS – Universitas Muhammadiyah Surakarta</strong>
              </div>
              <div>
                <a href="<?= base_url('/') ?>" class="footer-link me-4">Beranda</a>
                <a href="<?= base_url('/#kontak') ?>" class="footer-link">Kontak</a>
              </div>
            </div>
          </footer>
        </div>
        <!-- /Content wrapper -->

      </div>
      <!-- /Layout page -->
    </div>

    <!-- Overlay (mobile sidebar) -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>

  <!-- Core JS -->
  <script src="<?= base_url('assets/vendor/libs/popper/popper.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/bootstrap.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/libs/node-waves/node-waves.js') ?>"></script>
  <script src="<?= base_url('assets/vendor/js/menu.js') ?>"></script>
  <script src="<?= base_url('assets/js/main.js') ?>"></script>

  <?= $this->renderSection('extra_js') ?>
</body>

</html>
