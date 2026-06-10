<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">

  <!-- Mobile sidebar toggle -->
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="icon-base ti tabler-menu-2 icon-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

    <!-- Search (decorative) -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i>Student Mental Health and Wellbeing Support</i>
      </div>
    </div>

    <ul class="navbar-nav flex-row align-items-center ms-auto gap-2">

      <!-- Notifications -->
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <i class="icon-base ti tabler-bell icon-sm"></i>
          <span class="badge bg-danger rounded-pill badge-notifications">2</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto fw-semibold">Notifikasi</h6>
              <span class="badge bg-label-primary">2 Baru</span>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
              <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar">
                      <div class="avatar-initial rounded-circle bg-label-primary">
                        <i class="ti tabler-calendar-check"></i>
                      </div>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0 fw-semibold">Pengingat Janji</h6>
                    <small class="mb-1 d-block text-body">Sesi konseling besok jam 09.00</small>
                    <small class="text-muted">1 jam lalu</small>
                  </div>
                </div>
              </li>
              <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar">
                      <div class="avatar-initial rounded-circle bg-label-success">
                        <i class="ti tabler-check"></i>
                      </div>
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0 fw-semibold">Janji Dikonfirmasi</h6>
                    <small class="mb-1 d-block text-body">Janji konseling Anda telah dikonfirmasi</small>
                    <small class="text-muted">2 jam lalu</small>
                  </div>
                </div>
              </li>
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top p-2">
            <a href="javascript:void(0)" class="btn btn-primary w-100">Lihat semua</a>
          </li>
        </ul>
      </li>

      <!-- User Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center gap-2" href="javascript:void(0)" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.15);color:#1a5f7a;font-weight:700;">
              <?= strtoupper(substr(session()->get('user_name'), 0, 1)) ?>
            </div>
          </div>
          <div class="d-none d-sm-block">
            <span class="fw-medium d-block lh-1" style="font-size:.85rem;"><?= esc(session()->get('user_name')) ?></span>
            <small class="text-muted"><?= esc(ucfirst(session()->get('user_role'))) ?></small>
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
          <li>
            <a class="dropdown-item" href="<?= base_url('profil') ?>">
              <div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-sm flex-shrink-0">
                  <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.15);color:#1a5f7a;">
                    <?= strtoupper(substr(session()->get('user_name'), 0, 1)) ?>
                  </div>
                </div>
                <div>
                  <h6 class="mb-0" style="font-size:.85rem;"><?= esc(session()->get('user_name')) ?></h6>
                  <small class="text-muted"><?= esc(session()->get('user_email')) ?></small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider my-1">
          </li>
          <li>
            <a class="dropdown-item" href="<?= base_url('profil') ?>">
              <i class="ti tabler-user-circle me-2 ti-sm"></i>Profil Saya
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="<?= base_url('janji/buat') ?>">
              <i class="ti tabler-calendar-plus me-2 ti-sm"></i>Buat Janji
            </a>
          </li>
          <li>
            <hr class="dropdown-divider my-1">
          </li>
          <li>
            <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
              <i class="ti tabler-logout me-2 ti-sm"></i>Keluar
            </a>
          </li>
        </ul>
      </li>

    </ul>
  </div>
</nav>