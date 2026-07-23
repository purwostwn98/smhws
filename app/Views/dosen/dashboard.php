<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Dashboard Dosen<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">
      Selamat datang, <?= esc(session()->get('user_name')) ?>!
    </h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Anda masuk sebagai Dosen di sistem SMHWS UMS.
    </p>
  </div>
  <div class="avatar avatar-lg">
    <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.12);color:#1a5f7a;font-size:1.5rem;">
      <i class="ti tabler-school"></i>
    </div>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center py-5">
        <div class="mb-3" style="font-size:3rem;color:#1a5f7a;">
          <i class="ti tabler-heart-handshake"></i>
        </div>
        <h5 class="fw-bold mb-2" style="color:#1a2b40;">
          Student Mental Health &amp; Wellbeing Support
        </h5>
        <p class="text-muted mb-0" style="font-size:.9rem;">
          Universitas Muhammadiyah Surakarta
        </p>
        <hr class="my-4">
        <p class="text-muted" style="font-size:.875rem;">
          Jika ada mahasiswa yang membutuhkan bantuan kesehatan mental,
          silakan arahkan mereka untuk menggunakan layanan konseling SMHWS UMS.
        </p>
        <?php if (! empty($isKaprodi) || ! empty($isDekan)): ?>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
          <?php if (! empty($isKaprodi)): ?>
          <a href="<?= base_url('dosen/dashboard-prodi') ?>" class="btn btn-primary btn-sm">
            <i class="ti tabler-chart-bar me-1"></i>Dashboard Prodi
          </a>
          <?php endif ?>
          <?php if (! empty($isDekan)): ?>
          <a href="<?= base_url('dosen/dashboard-fakultas') ?>" class="btn btn-outline-primary btn-sm">
            <i class="ti tabler-building-community me-1"></i>Dashboard Fakultas
          </a>
          <?php endif ?>
        </div>
        <?php endif ?>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm mt-2">
          <i class="ti tabler-logout me-1"></i>Keluar
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
