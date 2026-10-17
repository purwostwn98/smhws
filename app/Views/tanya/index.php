<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Ask Our Psychologist<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
use App\Models\PertanyaanPsikologModel as P;
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Ask Our Psychologist</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Pertanyaan yang pernah kamu ajukan kepada psikolog SMHWS.</p>
  </div>
  <a href="<?= base_url('tanya/buat') ?>" class="btn btn-primary d-flex align-items-center gap-2">
    <i class="ti tabler-message-plus"></i>Ajukan Pertanyaan
  </a>
</div>

<?= $this->include('tanya/_flash') ?>

<?php if (empty($list)): ?>
  <div class="card shadow-sm">
    <div class="card-body text-center py-5 text-muted">
      <i class="ti tabler-message-question" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
      Kamu belum pernah bertanya. Nggak perlu menunggu semuanya terasa berat untuk mulai bertanya.
      <div><a href="<?= base_url('tanya/buat') ?>" class="btn btn-sm btn-primary mt-3">Ajukan Pertanyaan</a></div>
    </div>
  </div>
<?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($list as $q): $baru = $q['status'] === 'dijawab' && empty($q['dibaca_mahasiswa_at']); ?>
      <a href="<?= base_url('tanya/' . $q['id']) ?>" class="card shadow-sm text-body text-decoration-none">
        <div class="card-body d-flex gap-3 align-items-start">
          <div class="flex-grow-1 min-width-0">
            <div class="d-flex gap-2 align-items-center flex-wrap mb-1">
              <?= P::badge($q['status']) ?>
              <?php if ($baru): ?><span class="badge bg-success">Jawaban baru</span><?php endif ?>
              <?php if ($q['topik_nama']): ?><span class="badge bg-label-secondary"><?= esc($q['topik_nama']) ?></span><?php endif ?>
            </div>
            <div class="fw-semibold"><?= esc($q['judul']) ?></div>
            <div class="text-muted small">
              Dikirim <?= esc(tanggal_id($q['created_at'], true)) ?>
              <?php if ($q['status'] === 'dijawab'): ?> · Dijawab oleh <?= esc(P::namaPsikolog($q)) ?><?php endif ?>
            </div>
          </div>
          <i class="ti tabler-chevron-right text-muted mt-2"></i>
        </div>
      </a>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
