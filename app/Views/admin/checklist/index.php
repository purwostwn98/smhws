<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Kelola Checklist Konseling<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Checklist Konseling</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Kelola daftar pilihan yang muncul pada formulir hasil konseling.</p>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<div class="row g-4">
  <?php foreach ($sections as $sec): ?>
  <div class="col-md-6">
    <div class="card h-100 shadow-sm" style="border-top:4px solid <?= esc($sec['color']) ?>;">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3">
          <div class="rounded-3 p-2 flex-shrink-0"
               style="background:<?= esc($sec['color']) ?>18;">
            <i class="ti <?= esc($sec['icon']) ?>"
               style="font-size:1.4rem;color:<?= esc($sec['color']) ?>;display:block;"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-bold mb-1" style="font-size:.95rem;color:#1a2b40;">
              <?= esc($sec['huruf']) ?>. <?= esc($sec['label']) ?>
            </div>
            <div class="text-muted mb-3" style="font-size:.8rem;">
              <span class="badge bg-label-success me-1"><?= $sec['active_items'] ?> aktif</span>
              <span class="badge bg-label-secondary"><?= $sec['total_items'] ?> total</span>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="<?= base_url('admin/checklist/' . $sec['section_key']) ?>"
                 class="btn btn-sm btn-primary">
                <i class="ti tabler-list me-1"></i>Kelola Item
              </a>
              <a href="<?= base_url('admin/checklist/item/buat?section=' . $sec['section_key']) ?>"
                 class="btn btn-sm btn-outline-primary">
                <i class="ti tabler-plus me-1"></i>Tambah Item
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach ?>
</div>

<?= $this->endSection() ?>
