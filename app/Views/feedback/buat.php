<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Berikan Feedback<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$janji        = $janji ?? [];
$konselorNama = $konselorNama ?? null;
?>

<!-- Header -->
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('janji/' . $janji['id']) ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Detail Konseling
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;">Feedback Sesi</span>
</div>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<div class="row justify-content-center">
  <div class="col-lg-7 col-xl-6">

    <!-- Info Sesi -->
    <div class="card shadow-sm mb-4 border-success" style="border-left:4px solid #28c76f!important;">
      <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
          <i class="ti tabler-circle-check text-success" style="font-size:2rem;flex-shrink:0;"></i>
          <div>
            <div class="fw-bold">Sesi Konseling Selesai</div>
            <div class="text-muted" style="font-size:.82rem;">
              <?php if ($janji['tanggal_konseling']): ?>
                <?= date('l, d F Y', strtotime($janji['tanggal_konseling'])) ?>
                <?php if ($janji['jam_konseling']): ?>
                  · <?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB
                <?php endif ?>
              <?php endif ?>
            </div>
            <?php if ($konselorNama): ?>
              <div class="text-muted" style="font-size:.82rem;">Konselor: <?= esc($konselorNama) ?></div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Feedback -->
    <div class="card shadow-sm">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-star me-2 text-warning"></i>Berikan Penilaianmu</h6>
      </div>
      <div class="card-body">
        <form action="<?= base_url('feedback/simpan/' . $janji['id']) ?>" method="post">
          <?= csrf_field() ?>

          <!-- Star Rating -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.875rem;">Rating Sesi <span class="text-danger">*</span></label>
            <p class="text-muted mb-3" style="font-size:.82rem;">Seberapa puas kamu dengan sesi konseling ini?</p>

            <div class="d-flex gap-2 justify-content-center mb-2" id="starContainer">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <label for="star<?= $i ?>" class="star-label" style="cursor:pointer;">
                  <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="d-none" required>
                  <i class="ti tabler-star" id="starIcon<?= $i ?>"
                     style="font-size:2.5rem;color:#ccc;transition:color .15s;"></i>
                </label>
              <?php endfor ?>
            </div>
            <div class="text-center text-muted" id="ratingLabel" style="font-size:.82rem;min-height:1.2em;"></div>
          </div>

          <!-- Komentar -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.875rem;">Komentar <span class="text-muted fw-normal">(opsional)</span></label>
            <textarea name="komentar" class="form-control" rows="4"
                      placeholder="Ceritakan pengalamanmu, saran untuk konselor, atau hal yang bisa kami tingkatkan..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100" id="btnSubmit" disabled>
            <i class="ti tabler-send me-1"></i>Kirim Feedback
          </button>
        </form>
      </div>
    </div>

    <p class="text-center text-muted mt-3" style="font-size:.78rem;">
      <i class="ti tabler-lock me-1"></i>Feedback kamu bersifat rahasia dan hanya digunakan untuk evaluasi layanan.
    </p>

  </div>
</div>

<script>
const labels = ['', 'Sangat Tidak Puas', 'Tidak Puas', 'Cukup', 'Puas', 'Sangat Puas'];
let selected = 0;

function updateStars(hovered) {
  for (let i = 1; i <= 5; i++) {
    const icon = document.getElementById('starIcon' + i);
    if (i <= hovered) {
      icon.style.color = '#f0a500';
      icon.classList.add('ti-star-filled');
      icon.classList.remove('ti-star');
    } else {
      icon.style.color = selected >= i ? '#f0a500' : '#ccc';
      icon.classList.toggle('ti-star-filled', selected >= i);
      icon.classList.toggle('ti-star', selected < i);
    }
  }
}

document.querySelectorAll('.star-label').forEach((label, idx) => {
  const val = idx + 1;
  label.addEventListener('mouseenter', () => {
    updateStars(val);
    document.getElementById('ratingLabel').textContent = labels[val];
  });
  label.addEventListener('mouseleave', () => {
    updateStars(selected);
    document.getElementById('ratingLabel').textContent = selected ? labels[selected] : '';
  });
  label.addEventListener('click', () => {
    selected = val;
    updateStars(val);
    document.getElementById('ratingLabel').textContent = labels[val];
    document.getElementById('btnSubmit').disabled = false;
  });
});
</script>

<?= $this->endSection() ?>
