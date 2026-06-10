<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Masuk<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">

    <!-- Left: Illustration -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center" style="background: linear-gradient(135deg, #eaf4f8 0%, #d0eaf4 100%);">
        <img src="<?= base_url('assets/img/illustrations/auth-login-illustration-light.png') ?>"
          alt="Login" class="img-fluid" style="max-width: 380px;" />

        <!-- Floating brand card -->
        <div class="d-none d-xl-flex flex-column gap-2 auth-cover-brand">
          <div class="d-flex align-items-center gap-3 bg-white rounded-3 shadow-sm px-4 py-3">
            <span class="text-primary">
              <svg width="36" height="36" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 28s-12-7.2-12-14.4C4 9.16 7.16 6 11.2 6c2.16 0 4.12 1.04 5.4 2.68A6.8 6.8 0 0 1 22 6c3.76 0 6 2.96 6 7.6C28 20.8 16 28 16 28z" fill="currentColor" fill-opacity=".25"/>
                <path d="M16 26s-11-6.8-11-13.4C5 8.72 7.92 6 11.6 6c2.08 0 3.92 1.04 5.12 2.64A6.44 6.44 0 0 1 21.76 6C25.2 6 27 8.72 27 12.6 27 19.2 16 26 16 26z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="16" cy="14" r="3" fill="currentColor"/>
              </svg>
            </span>
            <div>
              <div class="fw-bold text-heading" style="font-size:.95rem;color:#1a5f7a;">SMHWS</div>
              <div class="text-muted" style="font-size:.75rem;">Student Mental Health &amp; Wellbeing Support</div>
            </div>
          </div>
          <p class="text-muted text-center mt-2" style="font-size:.82rem;">
            Universitas Muhammadiyah Surakarta
          </p>
        </div>
      </div>
    </div>

    <!-- Right: Form -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">

        <!-- Mobile logo -->
        <div class="d-lg-none d-flex align-items-center gap-2 mb-4">
          <span class="text-primary">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M16 28s-12-7.2-12-14.4C4 9.16 7.16 6 11.2 6c2.16 0 4.12 1.04 5.4 2.68A6.8 6.8 0 0 1 22 6c3.76 0 6 2.96 6 7.6C28 20.8 16 28 16 28z" fill="currentColor" fill-opacity=".25"/>
              <path d="M16 26s-11-6.8-11-13.4C5 8.72 7.92 6 11.6 6c2.08 0 3.92 1.04 5.12 2.64A6.44 6.44 0 0 1 21.76 6C25.2 6 27 8.72 27 12.6 27 19.2 16 26 16 26z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="16" cy="14" r="3" fill="currentColor"/>
            </svg>
          </span>
          <span class="fw-bold text-heading" style="color:#1a5f7a;">SMHWS – UMS</span>
        </div>

        <h3 class="mb-1 fw-bold" style="color:#1a2b40;">Selamat Datang! 👋</h3>
        <p class="mb-4 text-muted">Masuk untuk membuat janji konseling atau mengakses layanan SMHWS.</p>

        <!-- Flash messages -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="icon-base ti tabler-alert-circle"></i>
            <?= esc(session()->getFlashdata('error')) ?>
          </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="icon-base ti tabler-circle-check"></i>
            <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif ?>

        <!-- Login Form -->
        <?php $formErrors = session('errors') ?? []; ?>
        <form action="<?= base_url('login') ?>" method="POST" id="formLogin" novalidate>
          <?= csrf_field() ?>
          <?php if (!empty($redirect)): ?>
            <input type="hidden" name="redirect" value="<?= esc($redirect) ?>" />
          <?php endif ?>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label fw-medium" for="email">Email</label>
            <input type="email" id="email" name="email"
              class="form-control <?= !empty($formErrors['email']) ? 'is-invalid' : '' ?>"
              placeholder="nama@student.ums.ac.id"
              value="<?= esc(old('email')) ?>" autofocus required />
            <?php if (!empty($formErrors['email'])): ?>
              <div class="invalid-feedback"><?= esc($formErrors['email']) ?></div>
            <?php endif ?>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label fw-medium" for="password">Password</label>
              <a href="<?= base_url('lupa-password') ?>" class="float-end mb-1" style="font-size:.85rem;color:#1a5f7a;">
                Lupa password?
              </a>
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

          <!-- Remember me -->
          <div class="mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" />
              <label class="form-check-label" for="rememberMe">Ingat saya</label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary d-grid w-100">
            <span class="d-flex align-items-center justify-content-center gap-2">
              <i class="icon-base ti tabler-login"></i>
              Masuk
            </span>
          </button>
        </form>

        <p class="text-center mt-4 mb-0">
          Belum punya akun?
          <a href="<?= base_url('daftar') ?>" style="color:#1a5f7a;" class="fw-medium">Daftar di sini</a>
        </p>

        <!-- Divider -->
        <div class="d-flex align-items-center gap-3 my-4">
          <hr class="flex-grow-1 m-0" />
          <small class="text-muted">atau</small>
          <hr class="flex-grow-1 m-0" />
        </div>

        <!-- Back to home -->
        <a href="<?= base_url('/') ?>" class="btn btn-label-primary d-grid w-100">
          <span class="d-flex align-items-center justify-content-center gap-2">
            <i class="icon-base ti tabler-arrow-left"></i>
            Kembali ke Beranda
          </span>
        </a>

        <!-- Emergency notice -->
        <div class="mt-4 p-3 rounded-3 smhws-emergency">
          <div class="d-flex align-items-start gap-2">
            <i class="icon-base ti tabler-phone-call text-warning mt-1"></i>
            <div style="font-size:.8rem;">
              <strong>Butuh bantuan segera?</strong><br />
              Hubungi Hotline 24 Jam: <strong>119 ext 8</strong>
            </div>
          </div>
        </div>

      </div>
    </div>
    <!-- /Form -->

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  // Toggle password visibility
  document.getElementById('togglePassword').addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
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
