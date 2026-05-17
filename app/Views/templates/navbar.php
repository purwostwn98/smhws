<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo ">
        <a href="<?= base_url() ?>" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                            fill="currentColor" />
                        <path
                            opacity="0.06"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                            fill="#161616" />
                        <path
                            opacity="0.06"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                            fill="#161616" />
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                            fill="currentColor" />
                    </svg>
                </span>
            </span> 
            <span class="app-brand-text demo menu-text fw-bold ms-3">SMHWS</span> </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto"> <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i> <i class="icon-base ti tabler-x d-block d-xl-none"></i> </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-item <?= ($title == 'Dashboard') ? 'active' : '' ?>">
            <a href="<?php if (session()->get('userdata')['role'] == 'admin') {
                echo route_to('admin.dashboard');
            } else if (session()->get('userdata')['role'] == 'leader') {
                echo route_to('leader.dashboard');
            } else if (session()->get('userdata')['role'] == 'counselor') {
                echo route_to('counselor.dashboard');
            } else if (session()->get('userdata')['role'] == 'student') {
                echo route_to('student.dashboard');
            } ?>" class="menu-link"> <i class="menu-icon icon-base ti tabler-smart-home"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <?php if (session()->get('userdata')['role'] == 'admin') : ?>
            <li class="menu-item <?= ($title == 'Validasi Konseling') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-circle-check"></i>
                    <div data-i18n="Validasi Konseling">Validasi Konseling</div>
                </a>
            </li>
            <li class="menu-item <?= ($title == 'Manajemen Jadwal') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-calendar-event"></i>
                    <div data-i18n="Manajemen Jadwal">Manajemen Jadwal</div>
                </a>
            </li>
            <li class="menu-item <?= ($title == 'Manajemen Pengguna') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-users"></i>
                    <div data-i18n="Manajemen Pengguna">Manajemen Pengguna</div>
                </a>
            </li>
        <?php endif; ?>

        <?php if (session()->get('userdata')['role'] == 'leader') : ?>
            <li class="menu-item <?= ($title == 'Riwayat Konseling') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-history"></i>
                    <div data-i18n="Riwayat Konseling">Riwayat Konseling</div>
                </a>
            </li>
        <?php endif; ?>

        <?php if (session()->get('userdata')['role'] == 'counselor') : ?>
            <li class="menu-item <?= ($title == 'Jadwal Konseling') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-calendar-event"></i>
                    <div data-i18n="Jadwal Konseling">Jadwal Konseling</div>
                </a>
            </li>
        <?php endif; ?>

        <?php if (session()->get('userdata')['role'] == 'student') : ?>
            <li class="menu-item <?= ($title == 'Riwayat Konseling') ? 'active' : '' ?>">
                <a href="#" class="menu-link"> <i class="menu-icon icon-base ti tabler-history"></i>
                    <div data-i18n="Riwayat Konseling">Riwayat Konseling</div>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>