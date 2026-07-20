<?php
$isAdmin    = session()->get('is_superadmin') || session()->get('is_admin_fakultas');
$isKonselor = session()->get('user_role') === 'konselor';
$uri        = uri_string();
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- Brand -->
  <div class="app-brand demo">
    <a href="<?= base_url('/') ?>" class="app-brand-link">
      <span class="app-brand-logo demo text-primary">
        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M16 28s-12-7.2-12-14.4C4 9.16 7.16 6 11.2 6c2.16 0 4.12 1.04 5.4 2.68A6.8 6.8 0 0 1 22 6c3.76 0 6 2.96 6 7.6C28 20.8 16 28 16 28z" fill="currentColor" fill-opacity=".25"/>
          <path d="M16 26s-11-6.8-11-13.4C5 8.72 7.92 6 11.6 6c2.08 0 3.92 1.04 5.12 2.64A6.44 6.44 0 0 1 21.76 6C25.2 6 27 8.72 27 12.6 27 19.2 16 26 16 26z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="16" cy="14" r="3" fill="currentColor"/>
        </svg>
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2" style="font-size:.85rem;line-height:1.2;">
        SMHWS<br><small class="fw-normal text-muted" style="font-size:.62rem;">UMS</small>
      </span>
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

      <li class="menu-item <?= str_starts_with($uri, 'admin/janji') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/janji') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-calendar-stats"></i>
          <div>Kelola Konseling</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/konselor') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/konselor') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-user-heart"></i>
          <div>Kelola Konselor</div>
        </a>
      </li>

      <li class="menu-item <?= str_starts_with($uri, 'admin/mahasiswa') ? 'active' : '' ?>">
        <a href="<?= base_url('admin/mahasiswa') ?>" class="menu-link">
          <i class="menu-icon icon-base ti tabler-users"></i>
          <div>Data Mahasiswa</div>
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

    <?php elseif ($isKonselor): ?>

      <!-- ===== KONSELOR MENU ===== -->
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Konselor</span>
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

      <li class="menu-header small text-uppercase mt-1">
        <span class="menu-header-text">Akun</span>
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

  <?php if (! $isAdmin && ! $isKonselor): ?>
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
