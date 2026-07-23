<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-4">

      <!-- Brand -->
      <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8 ms-0">
        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0 px-0 me-4" type="button"
          data-bs-toggle="collapse" data-bs-target="#navbarMain"
          aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ti tabler-menu-2 icon-lg align-middle text-heading fw-medium"></i>
        </button>

        <a href="<?= base_url('/') ?>" class="app-brand-link">
          <img src="<?= base_url('myimg/logo_with_text.png') ?>" alt="SMHWS UMS"
            style="height:47px;width:auto;object-fit:contain;">
        </a>
      </div>

      <!-- Nav links -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarMain">
        <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 p-2"
          type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
          aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base ti tabler-x icon-lg"></i>
        </button>
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#hero">Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#layanan">Layanan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#tentang">Tentang Kami</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#faq">FAQ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#kontak">Kontak</a>
          </li>
        </ul>
      </div>

      <div class="landing-menu-overlay d-lg-none"></div>

      <!-- Toolbar right -->
      <ul class="navbar-nav flex-row align-items-center ms-auto gap-2">
        <?php if (session()->get('is_logged_in')): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar avatar-sm">
                <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.15);color:#1a5f7a;font-weight:600;">
                  <?= strtoupper(substr(session()->get('user_name'), 0, 1)) ?>
                </div>
              </div>
              <span class="d-none d-md-block fw-medium text-heading"><?= esc(session()->get('user_name')) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <div class="dropdown-item-text py-2">
                  <small class="text-muted d-block" style="font-size:.72rem;">Masuk sebagai</small>
                  <strong class="text-heading" style="font-size:.85rem;"><?= esc(ucfirst(session()->get('user_role'))) ?></strong>
                </div>
              </li>
              <li>
                <hr class="dropdown-divider m-0">
              </li>
              <?php if (session()->get('is_superadmin') || session()->get('is_admin_fakultas') || session()->get('user_role') === 'konselor'): ?>
                <li><a class="dropdown-item" href="<?= base_url('dashboard') ?>">
                    <i class="icon-base ti tabler-layout-dashboard me-2"></i>Dashboard
                  </a></li>
              <?php endif ?>
              <li><a class="dropdown-item" href="<?= base_url('/#konsultasi') ?>">
                  <i class="icon-base ti tabler-calendar-event me-2"></i>Daftar Konseling
                </a></li>
              <li>
                <hr class="dropdown-divider m-0">
              </li>
              <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                  <i class="icon-base ti tabler-logout me-2"></i>Keluar
                </a></li>
            </ul>
          </li>
        <?php else: ?>
          <li>
            <a href="<?= base_url('login') ?>" class="btn"
               style="background:var(--smhws-accent-warm);color:#fff;">
              <span class="tf-icons icon-base ti tabler-login me-md-1"></span>
              <span class="d-none d-md-block">Login</span>
            </a>
          </li>
          <li>
            <a href="<?= base_url('login?redirect=janji') ?>" class="btn btn-primary">
              <span class="tf-icons icon-base ti tabler-calendar-event me-md-1"></span>
              <span class="d-none d-md-block">Daftar Konseling</span>
            </a>
          </li>
        <?php endif ?>
      </ul>
    </div>
  </div>
</nav>