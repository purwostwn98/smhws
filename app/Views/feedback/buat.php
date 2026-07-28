<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Berikan Feedback<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$janji        = $janji ?? [];
$konselorNama = $konselorNama ?? null;

$kepuasanOptions = [
    'Sikap empati',
    'Jaminan kerahasiaan & rasa aman',
    'Solutif',
    'Keterampilan komunikasi',
    'Kemudahan akses dan penjadwalan',
    'Fasilitas yang disediakan',
];
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
              <div class="text-muted" style="font-size:.82rem;">Psikolog: <?= esc($konselorNama) ?></div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Feedback -->
    <div class="card shadow-sm">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-star me-2 text-warning"></i>Review Layanan SMHWS</h6>
      </div>
      <div class="card-body">
        <form action="<?= base_url('feedback/simpan/' . $janji['id']) ?>" method="post" id="feedbackForm">
          <?= csrf_field() ?>

          <!-- Q1: Rating keseluruhan -->
          <div class="mb-5">
            <label class="form-label fw-semibold mb-1" style="font-size:.9rem;">
              Secara keseluruhan, seberapa puas kamu terhadap layanan SMHWS?
              <span class="text-danger">*</span>
            </label>

            <div class="d-flex gap-3 justify-content-center mt-3 mb-2" id="starContainer">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <label for="star<?= $i ?>" class="star-label d-flex flex-column align-items-center gap-1" style="cursor:pointer;">
                  <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="d-none" required>
                  <i class="ti tabler-star" id="starIcon<?= $i ?>"
                     style="font-size:2.8rem;color:#ddd;transition:color .15s,transform .1s;"></i>
                  <span class="star-num" style="font-size:.72rem;color:#aaa;"><?= $i ?></span>
                </label>
              <?php endfor ?>
            </div>
            <div class="text-center fw-semibold mt-1" id="ratingLabel"
                 style="font-size:.85rem;min-height:1.4em;color:#1a2b40;"></div>
          </div>

          <!-- Q2: Faktor kepuasan -->
          <div class="mb-4">
            <label class="form-label fw-semibold mb-3" style="font-size:.9rem;">
              Hal apa yang membuat kamu merasa puas dan suka terhadap layanan konseling SMHWS?
              <span class="text-muted fw-normal" style="font-size:.8rem;">(pilih semua yang sesuai)</span>
            </label>

            <div class="d-flex flex-column gap-2">
              <?php foreach ($kepuasanOptions as $opt): ?>
                <label class="kepuasan-item d-flex align-items-center gap-3 px-3 py-2 rounded-2"
                       style="cursor:pointer;border:1.5px solid #e0e0e0;transition:border-color .15s,background .15s;">
                  <input type="checkbox" name="kepuasan[]" value="<?= esc($opt) ?>"
                         class="kepuasan-check form-check-input mt-0 flex-shrink-0" style="width:1.1em;height:1.1em;">
                  <span style="font-size:.875rem;"><?= esc($opt) ?></span>
                </label>
              <?php endforeach ?>

              <!-- Lainnya -->
              <label class="kepuasan-item d-flex align-items-start gap-3 px-3 py-2 rounded-2"
                     style="cursor:pointer;border:1.5px solid #e0e0e0;transition:border-color .15s,background .15s;">
                <input type="checkbox" name="kepuasan[]" value="Lainnya"
                       id="checkLainnya" class="kepuasan-check form-check-input mt-1 flex-shrink-0"
                       style="width:1.1em;height:1.1em;">
                <div class="flex-grow-1">
                  <span style="font-size:.875rem;">Lainnya</span>
                  <input type="text" name="kepuasan_lainnya" id="inputLainnya"
                         class="form-control form-control-sm mt-2 d-none"
                         placeholder="Tuliskan di sini..."
                         style="font-size:.82rem;">
                </div>
              </label>
            </div>
          </div>

          <!-- Komentar tambahan -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.875rem;">
              Komentar atau saran tambahan
              <span class="text-muted fw-normal">(opsional)</span>
            </label>
            <textarea name="komentar" class="form-control" rows="3"
                      placeholder="Ceritakan pengalamanmu atau hal yang bisa kami tingkatkan..."></textarea>
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
    const active = i <= (hovered || selected);
    icon.style.color  = active ? '#f0a500' : '#ddd';
    icon.style.transform = (i === hovered) ? 'scale(1.2)' : 'scale(1)';
    icon.classList.toggle('ti-star-filled', active);
    icon.classList.toggle('ti-star', !active);
  }
}

document.querySelectorAll('.star-label').forEach((label, idx) => {
  const val = idx + 1;
  label.addEventListener('mouseenter', () => {
    updateStars(val);
    document.getElementById('ratingLabel').textContent = labels[val];
  });
  label.addEventListener('mouseleave', () => {
    updateStars(0);
    document.getElementById('ratingLabel').textContent = selected ? labels[selected] : '';
  });
  label.addEventListener('click', () => {
    selected = val;
    updateStars(0);
    document.getElementById('ratingLabel').textContent = labels[val];
    document.getElementById('btnSubmit').disabled = false;
  });
});

// Checkbox highlight
document.querySelectorAll('.kepuasan-check').forEach(cb => {
  cb.addEventListener('change', function() {
    const item = this.closest('.kepuasan-item');
    if (this.checked) {
      item.style.borderColor = '#696cff';
      item.style.background  = 'rgba(105,108,255,.06)';
    } else {
      item.style.borderColor = '#e0e0e0';
      item.style.background  = '';
    }
  });
});

// Lainnya toggle
document.getElementById('checkLainnya').addEventListener('change', function() {
  const inp = document.getElementById('inputLainnya');
  inp.classList.toggle('d-none', !this.checked);
  if (this.checked) inp.focus();
});
</script>

<?= $this->endSection() ?>
