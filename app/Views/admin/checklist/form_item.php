<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?><?= $item ? 'Edit Item' : 'Tambah Item' ?> Checklist<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php $backUrl = $section ? base_url('admin/checklist/' . $section['section_key']) : base_url('admin/checklist'); ?>

<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $backUrl ?>" class="btn btn-icon btn-label-secondary">
    <i class="ti tabler-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0" style="color:#1a2b40;">
    <?= $item ? 'Edit Item Checklist' : 'Tambah Item Checklist' ?>
  </h4>
</div>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger mb-4"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif ?>

<div class="card shadow-sm" style="max-width:720px;">
  <div class="card-body">
    <form action="<?= $item ? base_url('admin/checklist/item/update/' . $item['id']) : base_url('admin/checklist/item/simpan') ?>" method="post">
      <?= csrf_field() ?>

      <!-- Section -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Section <span class="text-danger">*</span></label>
        <select name="section_id" id="sectionSelect" class="form-select" required
                <?= $item ? 'disabled' : '' ?> onchange="loadSubsections(this.value)">
          <option value="">-- Pilih Section --</option>
          <?php foreach ($sections as $sec): ?>
            <option value="<?= $sec['id'] ?>"
              <?= ($section && $section['id'] == $sec['id']) ? 'selected' : '' ?>>
              <?= esc($sec['huruf']) ?>. <?= esc($sec['label']) ?>
            </option>
          <?php endforeach ?>
        </select>
        <?php if ($item): ?>
          <input type="hidden" name="section_id" value="<?= $item['section_id'] ?>">
        <?php endif ?>
      </div>

      <!-- Subsection -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Subsection <span class="text-danger">*</span></label>
        <select name="subsection_key" id="subsectionSelect" class="form-select"
                onchange="handleSubSelect(this.value)">
          <option value="">-- Pilih atau buat subsection --</option>
          <?php foreach ($subsections as $sub): ?>
            <option value="<?= esc($sub['subsection_key']) ?>"
              data-label="<?= esc($sub['subsection_label']) ?>"
              <?= ($item && $item['subsection_key'] === $sub['subsection_key']) ? 'selected' : '' ?>>
              <?= esc($sub['subsection_label']) ?>
            </option>
          <?php endforeach ?>
          <option value="__new__">+ Buat subsection baru</option>
        </select>
      </div>

      <!-- Subsection label (readonly jika pilih existing, editable jika edit/buat baru) -->
      <div class="mb-3" id="subLabelWrap">
        <label class="form-label fw-semibold">Label Subsection</label>
        <input type="text" name="subsection_label" id="subLabelInput" class="form-control"
               value="<?= esc($item['subsection_label'] ?? '') ?>"
               placeholder="Contoh: 1. Akademik">
      </div>

      <!-- New subsection key (muncul jika pilih buat baru) -->
      <div class="mb-3" id="newSubKeyWrap" style="display:none;">
        <label class="form-label fw-semibold">Key Subsection Baru <span class="text-danger">*</span></label>
        <input type="text" name="new_subsection_key" id="newSubKeyInput" class="form-control"
               placeholder="Contoh: minat_bakat (huruf kecil, tanpa spasi)">
        <div class="form-text">Digunakan sebagai nama field di formulir konseling. Gunakan huruf kecil dan underscore.</div>
      </div>
      <div class="mb-3" id="newSubLabelWrap" style="display:none;">
        <label class="form-label fw-semibold">Label Subsection Baru <span class="text-danger">*</span></label>
        <input type="text" name="new_subsection_label" id="newSubLabelInput" class="form-control"
               placeholder="Contoh: 9. Minat dan Bakat">
      </div>

      <!-- Item label -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Teks Item <span class="text-danger">*</span></label>
        <textarea name="item_label" class="form-control" rows="3" required
                  placeholder="Tulis teks pilihan checklist..."><?= esc($item['item_label'] ?? '') ?></textarea>
      </div>

      <!-- Input type -->
      <div class="mb-3">
        <label class="form-label fw-semibold">Tipe Input</label>
        <div class="d-flex gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="input_type" id="typeCheckbox"
                   value="checkbox" <?= (! $item || $item['input_type'] === 'checkbox') ? 'checked' : '' ?>>
            <label class="form-check-label" for="typeCheckbox">Checkbox (multi-pilih)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="input_type" id="typeRadio"
                   value="radio" <?= ($item && $item['input_type'] === 'radio') ? 'checked' : '' ?>>
            <label class="form-check-label" for="typeRadio">Radio (pilih satu)</label>
          </div>
        </div>
      </div>

      <?php if ($item): ?>
      <!-- Status aktif (hanya pada edit) -->
      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                 <?= $item['is_active'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="isActive">Item aktif (tampil di formulir)</label>
        </div>
      </div>
      <?php endif ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="ti <?= $item ? 'tabler-device-floppy' : 'tabler-plus' ?> me-1"></i>
          <?= $item ? 'Simpan Perubahan' : 'Tambah Item' ?>
        </button>
        <a href="<?= $backUrl ?>" class="btn btn-label-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  const SUBSECTIONS_API = '<?= base_url('admin/checklist/subsections') ?>';

  function handleSubSelect(val) {
    const isNew       = val === '__new__';
    const subLabelIn  = document.getElementById('subLabelInput');
    const subLabelWr  = document.getElementById('subLabelWrap');
    const newKeyWr    = document.getElementById('newSubKeyWrap');
    const newLabelWr  = document.getElementById('newSubLabelWrap');
    const sel         = document.getElementById('subsectionSelect');

    if (isNew) {
      subLabelWr.style.display  = 'none';
      newKeyWr.style.display    = '';
      newLabelWr.style.display  = '';
    } else {
      newKeyWr.style.display    = 'none';
      newLabelWr.style.display  = 'none';
      subLabelWr.style.display  = '';
      // auto-fill label from data attribute
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.dataset.label) subLabelIn.value = opt.dataset.label;
    }
  }

  async function loadSubsections(sectionId) {
    if (! sectionId) return;
    const res  = await fetch(SUBSECTIONS_API + '?section_id=' + sectionId);
    const data = await res.json();
    const sel  = document.getElementById('subsectionSelect');
    sel.innerHTML = '<option value="">-- Pilih atau buat subsection --</option>';
    data.forEach(sub => {
      const opt = document.createElement('option');
      opt.value = sub.subsection_key;
      opt.dataset.label = sub.subsection_label;
      opt.textContent = sub.subsection_label;
      sel.appendChild(opt);
    });
    const newOpt = document.createElement('option');
    newOpt.value = '__new__';
    newOpt.textContent = '+ Buat subsection baru';
    sel.appendChild(newOpt);
  }

  // Init: jika ada subsection terpilih, set label
  document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('subsectionSelect');
    if (sel.value && sel.value !== '__new__') {
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.dataset.label) {
        document.getElementById('subLabelInput').value = opt.dataset.label;
      }
    }
  });
</script>
<?= $this->endSection() ?>
