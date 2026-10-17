<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Pertanyaan Saya<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('tanya') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Ask Our Psychologist</a>
  <span class="text-muted">/</span><span class="fw-semibold" style="font-size:.875rem;">Detail</span>
</div>

<?= $this->include('tanya/_flash') ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <?= view('tanya/_qa', ['row' => $row, 'tampilNama' => false]) ?>

    <?php if (in_array($row['status'], ['menunggu', 'ditugaskan'], true)): ?>
      <div class="alert alert-info d-flex gap-2">
        <i class="ti tabler-hourglass mt-1"></i>
        <div>Pertanyaanmu sedang <?= $row['status'] === 'menunggu' ? 'menunggu dibagikan ke psikolog' : 'dibaca oleh psikolog' ?>. Jawaban akan muncul di halaman ini dan di dashboard-mu.</div>
      </div>
    <?php endif ?>

    <?php if (! empty($row['learn_slug']) && $row['learn_status'] === 'terbit'): ?>
      <div class="alert alert-success d-flex gap-2">
        <i class="ti tabler-world mt-1"></i>
        <div>Tanya-jawab ini sudah ditampilkan secara anonim di SMHWS Learn. <a href="<?= base_url('learn/' . $row['learn_slug']) ?>" target="_blank" class="alert-link">Lihat</a></div>
      </div>
    <?php endif ?>

    <div class="text-center mt-4">
      <a href="<?= base_url('tanya/buat') ?>" class="btn btn-label-primary"><i class="ti tabler-message-plus me-1"></i>Ajukan Pertanyaan Lain</a>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
