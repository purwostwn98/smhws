<?= $this->extend('layouts/dashboard') ?>
<?php $instansi = $instansi ?? null; ?>
<?= $this->section('title') ?><?= $instansi ? 'Edit' : 'Tambah' ?> Instansi Rujukan<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/instansi-rujukan') ?>"
     class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Instansi Rujukan
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;"><?= $instansi ? 'Edit' : 'Tambah' ?></span>
</div>

<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-header py-3"
           style="border-left:4px solid #696cff;background:#696cff0a;">
        <h6 class="fw-bold mb-0" style="color:#696cff;">
          <i class="ti tabler-building-hospital me-2"></i>
          <?= $instansi ? 'Edit Instansi Rujukan' : 'Tambah Instansi Rujukan Baru' ?>
        </h6>
      </div>
      <div class="card-body py-4 px-4">
        <form action="<?= $instansi
              ? base_url('admin/instansi-rujukan/update/' . $instansi['id'])
              : base_url('admin/instansi-rujukan/simpan') ?>"
              method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Nama Instansi <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama_instansi" class="form-control"
                   placeholder="cth: Rumah Sakit Jiwa Dr. Radjiman Wediodiningrat"
                   value="<?= esc(old('nama_instansi', $instansi['nama_instansi'] ?? '')) ?>"
                   required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Singkatan</label>
            <input type="text" name="singkatan" class="form-control"
                   placeholder="cth: RSJ Lawang"
                   value="<?= esc(old('singkatan', $instansi['singkatan'] ?? '')) ?>">
            <div class="form-text">Opsional — nama pendek yang tampil di tabel rujukan.</div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"
                      placeholder="Alamat lengkap instansi..."><?= esc(old('alamat', $instansi['alamat'] ?? '')) ?></textarea>
            <div class="form-text">Opsional — ditampilkan pada surat rujukan.</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="ti <?= $instansi ? 'tabler-device-floppy' : 'tabler-plus' ?> me-1"></i>
              <?= $instansi ? 'Simpan Perubahan' : 'Tambah Instansi' ?>
            </button>
            <a href="<?= base_url('admin/instansi-rujukan') ?>"
               class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
