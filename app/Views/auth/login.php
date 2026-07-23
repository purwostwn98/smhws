<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Masuk<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
  /* Panel kiri: foto sebagai background dengan overlay gelap */
  .auth-cover-bg {
    background-image:
      linear-gradient(145deg, rgba(13, 74, 97, .82) 0%, rgba(26, 95, 122, .75) 60%, rgba(87, 197, 182, .6) 100%),
      url('<?= base_url('myimg/bg_login.jpg') ?>') !important;
    background-size: cover !important;
    background-position: center !important;
    flex-direction: column;
    justify-content: center;
    padding: 3.5rem;
  }

  .smhws-cover-content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 440px;
    margin: auto;
  }

  .smhws-feature-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: rgba(255, 255, 255, .15);
    border: 1px solid rgba(255, 255, 255, .28);
    color: #fff;
    border-radius: 100px;
    padding: .3rem .85rem;
    font-size: .78rem;
    font-weight: 500;
    backdrop-filter: blur(4px);
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="authentication-wrapper authentication-cover">

  <!-- Logo brand (posisi absolute di atas) -->
  <a href="<?= base_url('/') ?>" class="app-brand auth-cover-brand d-flex align-items-center gap-2">
    <img src="<?= base_url('myimg/logo_with_text.png') ?>" alt="SMHWS UMS" style="height:40px;width:auto;">
  </a>

  <div class="authentication-inner row m-0">

    <!-- ── Panel kiri: branding ── -->
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        <div class="smhws-cover-content">

          <!-- Logo putih -->
          <img src="<?= base_url('myimg/logo_with_text.png') ?>" alt="SMHWS UMS"
            style="height:56px;width:auto;filter:brightness(0) invert(1);opacity:.95;margin-bottom:2rem;display:block;">

          <!-- Tagline -->
          <h1 class="fw-extrabold text-white mb-3" style="font-size:2rem;line-height:1.25;">
            Tumbuh Bermakna.<br>Sejahtera. Tangguh.
          </h1>
          <p class="mb-5" style="color:rgba(255,255,255,.8);font-size:.95rem;line-height:1.75;">
            Layanan kesehatan mental dan wellbeing untuk mahasiswa UMS —
            profesional, rahasia, dan sepenuhnya gratis.
          </p>

          <!-- Feature pills -->
          <div class="d-flex flex-wrap gap-2 mb-6">
            <span class="smhws-feature-pill"><i class="ti tabler-shield-check"></i> 100% Rahasia</span>
            <span class="smhws-feature-pill"><i class="ti tabler-currency-dollar-off"></i> Gratis</span>
            <span class="smhws-feature-pill"><i class="ti tabler-award"></i> Psikolog Bersertifikat</span>
            <span class="smhws-feature-pill"><i class="ti tabler-heart-handshake"></i> Pendekatan Holistik</span>
          </div>

          <p class="mb-0" style="color:rgba(255,255,255,.45);font-size:.75rem;letter-spacing:.06em;text-transform:uppercase;">
            Universitas Muhammadiyah Surakarta &mdash; sejak 2019
          </p>

        </div>
      </div>
    </div>
    <!-- /Panel kiri -->

    <!-- ── Panel kanan: form ── -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">

        <h4 class="mb-1 fw-bold" style="color:#1a2b40;">Selamat Datang! 👋</h4>
        <p class="mb-4 text-muted" style="font-size:.9rem;">
          <?php if (!empty($redirect) && $redirect === 'janji'): ?>
            Masuk untuk melanjutkan pendaftaran konseling.
          <?php else: ?>
            Masuk untuk mengakses layanan SMHWS UMS.
          <?php endif ?>
        </p>

        <!-- SSO UMS -->
        <a href="<?= base_url('cas') ?><?= !empty($redirect) ? '?redirect=' . esc($redirect) : '' ?>"
          class="btn d-flex align-items-center justify-content-center gap-2 w-100 mb-2 fw-semibold"
          style="background:#1a5f7a;color:#fff;border-radius:8px;padding:.65rem 1rem;">
          <img src="<?= base_url('myimg/logo_ums.png') ?>" alt="UMS"
            style="height:20px;width:auto;filter:brightness(0) invert(1);"
            onerror="this.style.display='none'">
          <span>Masuk dengan SSO UMS</span>
        </a>

        <div class="divider my-4">
          <div class="divider-text text-muted" style="font-size:.78rem;">atau masuk dengan email</div>
        </div>

        <!-- Flash messages -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="ti tabler-alert-circle flex-shrink-0"></i>
            <?= esc(session()->getFlashdata('error')) ?>
          </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="ti tabler-circle-check flex-shrink-0"></i>
            <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif ?>

        <!-- Form -->
        <?php $formErrors = session('errors') ?? []; ?>
        <form id="formLogin" action="<?= base_url('login') ?>" method="POST" class="mb-5" novalidate>
          <?= csrf_field() ?>
          <?php if (!empty($redirect)): ?>
            <input type="hidden" name="redirect" value="<?= esc($redirect) ?>" />
          <?php endif ?>

          <div class="mb-5">
            <label class="form-label fw-medium" for="email">Email</label>
            <input type="email" id="email" name="email"
              class="form-control <?= !empty($formErrors['email']) ? 'is-invalid' : '' ?>"
              placeholder="nama@student.ums.ac.id"
              value="<?= esc(old('email')) ?>" autofocus required />
            <?php if (!empty($formErrors['email'])): ?>
              <div class="invalid-feedback"><?= esc($formErrors['email']) ?></div>
            <?php endif ?>
          </div>

          <div class="mb-5 form-password-toggle">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label fw-medium mb-0" for="password">Password</label>
              <a href="<?= base_url('lupa-password') ?>" style="font-size:.83rem;color:#1a5f7a;">Lupa password?</a>
            </div>
            <div class="input-group input-group-merge">
              <input type="password" id="password" name="password"
                class="form-control <?= !empty($formErrors['password']) ? 'is-invalid' : '' ?>"
                placeholder="••••••••" required />
              <span class="input-group-text cursor-pointer" id="togglePassword">
                <i class="icon-base ti tabler-eye-off" id="eyeIcon"></i>
              </span>
            </div>
            <?php if (!empty($formErrors['password'])): ?>
              <div class="invalid-feedback d-block"><?= esc($formErrors['password']) ?></div>
            <?php endif ?>
          </div>

          <div class="my-6">
            <div class="form-check ms-2">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" />
              <label class="form-check-label" for="rememberMe">Ingat saya</label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary d-grid w-100">
            <span class="d-flex align-items-center justify-content-center gap-2">
              <i class="ti tabler-login"></i>Masuk
            </span>
          </button>
        </form>

        <p class="text-center">
          Belum punya akun?
          <a href="<?= base_url('daftar') ?>" class="fw-medium" style="color:#1a5f7a;">Daftar di sini</a>
        </p>

        <div class="divider my-5">
          <div class="divider-text">atau</div>
        </div>

        <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary d-grid w-100">
          <span class="d-flex align-items-center justify-content-center gap-2">
            <i class="ti tabler-arrow-left"></i>Kembali ke Beranda
          </span>
        </a>

        <!-- Darurat -->
        <div class="mt-5 rounded-3 p-3 d-flex align-items-start gap-2"
          style="background:rgba(240,165,0,.08);border:1px solid rgba(240,165,0,.25);">
          <i class="ti tabler-phone-call text-warning mt-1 flex-shrink-0"></i>
          <p class="mb-0" style="font-size:.78rem;color:#666;">
            <strong style="color:#1a2b40;">Butuh bantuan segera?</strong><br>
            Hotline 24 Jam: <strong>119 ext 8</strong> &nbsp;|&nbsp; Darurat: <strong>112</strong>
          </p>
        </div>

      </div>
    </div>
    <!-- /Panel kanan -->

  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('tabler-eye-off', 'tabler-eye');
    } else {
      input.type = 'password';
      icon.classList.replace('tabler-eye', 'tabler-eye-off');
    }
  });
</script>
<?= $this->endSection() ?>