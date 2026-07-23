<?php
$isAdmin    = session()->get('is_superadmin') || session()->get('is_admin_fakultas');
$isKonselor = session()->get('user_role') === 'konselor';
$isDosen    = session()->get('user_role') === 'dosen';
$isKaprodi  = (bool) session()->get('is_kaprodi'); // berlaku untuk dosen maupun psikolog
$isDekan    = (bool) session()->get('is_dekan');   // berlaku untuk dosen maupun psikolog
$uri        = uri_string();
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- Brand -->
  <div class="app-brand demo">
    <a href="<?= base_url('/') ?>" class="app-brand-link">
      <img src="<?= base_url('myimg/logo_with_text.png') ?>" alt="SMHWS UMS" style="height:70px;width:auto;">
    </a>

    <a href="javascript:void(0)" class="layout-menu-toggle menu-link text-auto ms-auto d-block d-xl-none">
      <i class="icon-base ti tabler-x icon-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    <?php if ($isAdmin): ?>

      <!-- ===== ADMIN MENU ===== -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Manajemen</span>
      </li>

      <li class="menu-item <?= $uri === 'admin/dashboard' ? 'active' : '' ?>">
        <a href="<?= base_url('admin/dashboard') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-layout-dashboard"></i>
          <div>Dashboard</div>
        </a>
      </li>

      <li class="menu-item <?= $uri === 'admin/dashboard-univ' ? 'active' : '' ?>">
        <a href="<?= base_url('admin/dashboard-univ') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-building-community"></i>
          <div>Dashboard Universitas</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/janji') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/janji') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-stats"></i>
          <div>Kelola Konseling</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/kalender') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/kalender') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-month"></i>
          <div>Kalender Konseling</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/konselor') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/konselor') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-user-heart"></i>
          <div>Kelola Psikolog</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/mahasiswa') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/mahasiswa') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-users"></i>
          <div>Data Mahasiswa</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/instansi-rujukan') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/instansi-rujukan') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-building-hospital"></i>
          <div>Instansi Rujukan</div>
        </a>
      </li>

      <li class="menu-item <?= $uri === 'admin/rekap-konseling' ? 'active' : '' ?>">
        <a href="<?= base_url('admin/rekap-konseling') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-table"></i>
          <div>Rekap Konseling</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Akun</span>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('logout') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-logout text-danger"></i>
          <div class="text-danger">Keluar</div>
        </a>
      </li>

    <?php elseif ($isDosen): ?>

      <!-- ===== DOSEN MENU ===== -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Dosen</span>
      </li>

      <li class="menu-item <?= $uri === 'dosen/dashboard' ? 'active' : '' ?>">
        <a href="<?= base_url('dosen/dashboard') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-layout-dashboard"></i>
          <div>Dashboard</div>
        </a>
      </li>

      <?php if ($isKaprodi): ?>
        <li class="menu-item <?= $uri === 'dosen/dashboard-prodi' ? 'active' : '' ?>">
          <a href="<?= base_url('dosen/dashboard-prodi') ?>" class="menu-link">
            <i class="menu-icon icon-base ti tabler-chart-bar"></i>
            <div>Dashboard Prodi</div>
          </a>
        </li>
      <?php endif ?>

      <?php if ($isDekan): ?>
        <li class="menu-item <?= $uri === 'dosen/dashboard-fakultas' ? 'active' : '' ?>">
          <a href="<?= base_url('dosen/dashboard-fakultas') ?>" class="menu-link">
            <i class="menu-icon icon-base ti tabler-building-community"></i>
            <div>Dashboard Fakultas</div>
          </a>
        </li>
      <?php endif ?>

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Akun</span>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('logout') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-logout text-danger"></i>
          <div class="text-danger">Keluar</div>
        </a>
      </li>

    <?php elseif ($isKonselor): ?>

      <!-- ===== KONSELOR MENU ===== -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Psikolog</span>
      </li>

      <li class="menu-item <?= $uri === 'konselor/dashboard' ? 'active' : '' ?>">
        <a href="<?= base_url('konselor/dashboard') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-layout-dashboard"></i>
          <div>Dashboard</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'konselor/janji') ? 'active' : '' ?>">
        <a href="<?= base_url('konselor/janji') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-event"></i>
          <div>Sesi Saya</div>
        </a>
      </li>

      <?php if ($isKaprodi): ?>
        <li class="menu-item <?= $uri === 'dosen/dashboard-prodi' ? 'active' : '' ?>">
          <a href="<?= base_url('dosen/dashboard-prodi') ?>" class="menu-link">
            <i class="menu-icon icon-base ti tabler-chart-bar"></i>
            <div>Dashboard Prodi</div>
          </a>
        </li>
      <?php endif ?>

      <?php if ($isDekan): ?>
        <li class="menu-item <?= $uri === 'dosen/dashboard-fakultas' ? 'active' : '' ?>">
          <a href="<?= base_url('dosen/dashboard-fakultas') ?>" class="menu-link">
            <i class="menu-icon icon-base ti tabler-building-community"></i>
            <div>Dashboard Fakultas</div>
          </a>
        </li>
      <?php endif ?>

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Akun</span>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'konselor/profil') ? 'active' : '' ?>">
        <a href="<?= base_url('konselor/profil') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-user-edit"></i>
          <div>Profil &amp; Jadwal</div>
        </a>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('logout') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-logout text-danger"></i>
          <div class="text-danger">Keluar</div>
        </a>
      </li>

    <?php else: ?>

      <!-- ===== MAHASISWA MENU ===== -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Mahasiswa</span>
      </li>

      <li class="menu-item <?= $uri === 'dashboard' ? 'active' : '' ?>">
        <a href="<?= base_url('dashboard') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-smart-home"></i>
          <div>Dashboard</div>
        </a>
      </li>

      <li class="menu-item <?= $uri === 'janji/buat' ? 'active' : '' ?>">
        <a href="<?= base_url('janji/buat') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-plus"></i>
          <div>Daftar Konseling</div>
        </a>
      </li>

      <li class="menu-item <?= ($uri === 'janji' || preg_match('#^janji/\d+$#', $uri)) ? 'active' : '' ?>">
        <a href="<?= base_url('janji') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-event"></i>
          <div>Konseling Saya</div>
        </a>
      </li>

      <li class="menu-item <?= $uri === 'riwayat' ? 'active' : '' ?>">
        <a href="<?= base_url('riwayat') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-history"></i>
          <div>Riwayat Sesi</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Akun</span>
      </li>

      <li class="menu-item <?= $uri === 'profil' ? 'active' : '' ?>">
        <a href="<?= base_url('profil') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-user-circle"></i>
          <div>Profil Saya</div>
        </a>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('logout') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-logout text-danger"></i>
          <div class="text-danger">Keluar</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Informasi</span>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('/#layanan') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-heart-handshake"></i>
          <div>Layanan SMHWS</div>
        </a>
      </li>

      <li class="menu-item">
        <a href="<?= base_url('/#faq') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-help-circle"></i>
          <div>FAQ</div>
        </a>
      </li>

    <?php endif ?>

  </ul>

  <?php if (! $isAdmin && ! $isKonselor && ! $isDosen): ?>
    <!-- Emergency Banner (hanya untuk mahasiswa) -->
    <div class="px-3 py-3 mt-auto">
      <div class="p-3 rounded-3" style="background:rgba(240,165,0,.12);border-left:3px solid #f0a500;">
        <div class="d-flex gap-2 align-items-start">
          <i class="ti tabler-phone-call text-warning mt-1" style="font-size:1rem;"></i>
          <div>
            <div class="fw-semibold" style="font-size:.75rem;color:#1a2b40;">Darurat Mental</div>
            <div style="font-size:.7rem;color:#666;">Hotline: <strong>119 ext 8</strong></div>
          </div>
        </div>
      </div>
    </div>
  <?php endif ?>

</aside>