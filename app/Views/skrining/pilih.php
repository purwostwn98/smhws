<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Mental Health Check<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Pilih jenis assessment kesehatan mental yang ingin kamu isi secara online tanpa login.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="text-center mb-5">
    <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Mental Health Check</h2>
    <p class="text-muted mb-0">Pilih jenis assessment yang ingin kamu isi. Tanpa login, jawabanmu bersifat anonim.</p>
  </div>

  <div class="row g-4 justify-content-center">
    <?php foreach ($daftarJenis as $kode => $j): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm" style="border-top:3px solid <?= esc($j['color']) ?>;">
          <div class="card-body p-5 d-flex flex-column">
            <div class="mb-3" style="color:<?= esc($j['color']) ?>;">
              <i class="ti <?= esc($j['icon']) ?>" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-1"><?= esc($j['singkatan']) ?></h5>
            <div class="text-muted small mb-3"><?= esc($j['nama']) ?></div>
            <p class="text-muted flex-grow-1"><?= esc($j['deskripsi']) ?></p>
            <div class="d-flex gap-3 text-muted small mb-4">
              <span><i class="ti tabler-list-check me-1"></i><?= esc($j['jumlah_item']) ?></span>
              <span><i class="ti tabler-clock me-1"></i><?= esc($j['durasi']) ?></span>
            </div>
            <?php if ($j['aktif']): ?>
              <a href="<?= base_url($j['url']) ?>" class="btn btn-primary btn-sm">Mulai Assessment</a>
            <?php else: ?>
              <button type="button" class="btn btn-label-secondary btn-sm" disabled>Segera Hadir</button>
            <?php endif ?>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div class="text-center mt-5">
    <a href="<?= base_url() ?>" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Kembali ke Beranda</a>
  </div>
</div>
<?= $this->endSection() ?>
