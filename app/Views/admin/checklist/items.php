<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Checklist: <?= esc($section['huruf']) ?>. <?= esc($section['label']) ?><?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
  <a href="<?= base_url('admin/checklist') ?>" class="btn btn-icon btn-label-secondary">
    <i class="ti tabler-arrow-left"></i>
  </a>
  <div class="flex-grow-1">
    <h4 class="fw-bold mb-0" style="color:#1a2b40;">
      <?= esc($section['huruf']) ?>. <?= esc($section['label']) ?>
    </h4>
    <p class="text-muted mb-0" style="font-size:.85rem;">Kelola item dan subsection pada bagian ini.</p>
  </div>
  <a href="<?= base_url('admin/checklist/item/buat?section=' . $section['section_key']) ?>"
     class="btn btn-primary">
    <i class="ti tabler-plus me-1"></i>Tambah Item
  </a>
</div>

<?php foreach (['success','error'] as $fl): ?>
  <?php if ($msg = session()->getFlashdata($fl)): ?>
    <div class="alert alert-<?= $fl === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-4">
      <?= esc($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif ?>
<?php endforeach ?>

<?php if (empty($groups)): ?>
  <div class="alert alert-info">Belum ada item. Klik "Tambah Item" untuk mulai.</div>
<?php else: ?>
  <?php foreach ($groups as $grp): ?>
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3"
         style="background:<?= esc($section['color']) ?>0f;border-left:4px solid <?= esc($section['color']) ?>;">
      <div>
        <span class="fw-semibold" style="font-size:.9rem;color:<?= esc($section['color']) ?>;">
          <?= esc($grp['subsection_label']) ?>
        </span>
        <span class="badge bg-label-secondary ms-2" style="font-size:.68rem;">
          <?= $grp['input_type'] === 'radio' ? 'Pilih satu (radio)' : 'Multi-pilih (checkbox)' ?>
        </span>
      </div>
      <span class="text-muted" style="font-size:.75rem;"><?= count($grp['items']) ?> item</span>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0 align-middle">
        <tbody>
          <?php foreach ($grp['items'] as $item): ?>
          <tr>
            <td class="ps-3 py-2" style="font-size:.82rem;width:60%;">
              <?= esc($item['item_label']) ?>
            </td>
            <td class="text-center" style="width:10%;">
              <?php if ($item['is_active']): ?>
                <span class="badge bg-label-success" style="font-size:.7rem;">Aktif</span>
              <?php else: ?>
                <span class="badge bg-label-secondary" style="font-size:.7rem;">Nonaktif</span>
              <?php endif ?>
            </td>
            <td class="text-end pe-3" style="white-space:nowrap;">
              <a href="<?= base_url('admin/checklist/item/edit/' . $item['id']) ?>"
                 class="btn btn-xs btn-icon btn-label-primary me-1"
                 title="Edit">
                <i class="ti tabler-pencil"></i>
              </a>
              <form action="<?= base_url('admin/checklist/item/toggle/' . $item['id']) ?>"
                    method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-xs btn-icon btn-label-secondary me-1"
                        title="<?= $item['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                  <i class="ti <?= $item['is_active'] ? 'tabler-eye-off' : 'tabler-eye' ?>"></i>
                </button>
              </form>
              <form action="<?= base_url('admin/checklist/item/hapus/' . $item['id']) ?>"
                    method="post" class="d-inline"
                    onsubmit="return confirm('Hapus item ini?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-xs btn-icon btn-label-danger" title="Hapus">
                  <i class="ti tabler-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach ?>
<?php endif ?>

<?= $this->endSection() ?>
