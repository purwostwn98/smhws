<?= $this->extend('layouts/dashboard') ?>
<?php
$agenda = $agenda ?? null;
$v = fn(string $k, $default = '') => old($k, $agenda[$k] ?? $default);
$dt = fn(string $k) => old($k, ! empty($agenda[$k]) ? date('Y-m-d\TH:i', strtotime($agenda[$k])) : '');
$biayaTipe = $v('biaya_tipe', 'gratis');
?>
<?= $this->section('title') ?><?= $agenda ? 'Edit' : 'Tambah' ?> Agenda Kegiatan<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/agenda-kegiatan') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Agenda Kegiatan
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;"><?= $agenda ? 'Edit' : 'Tambah' ?></span>
</div>

<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card shadow-sm">
      <div class="card-header py-3" style="border-left:4px solid #696cff;background:#696cff0a;">
        <h6 class="fw-bold mb-0" style="color:#696cff;">
          <i class="ti tabler-calendar-event me-2"></i><?= $agenda ? 'Edit Agenda Kegiatan' : 'Tambah Agenda Kegiatan Baru' ?>
        </h6>
      </div>
      <div class="card-body py-4 px-4">
        <form action="<?= $agenda ? base_url('admin/agenda-kegiatan/update/' . $agenda['id']) : base_url('admin/agenda-kegiatan/simpan') ?>"
              method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="row g-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
              <select name="jenis" class="form-select" required>
                <option value="">Pilih jenis</option>
                <?php foreach (\App\Models\AgendaKegiatanModel::JENIS as $kode => $label): ?>
                  <option value="<?= $kode ?>" <?= $v('jenis') === $kode ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label>
              <input type="text" name="nama_kegiatan" class="form-control" maxlength="255" required value="<?= esc($v('nama_kegiatan')) ?>">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Tema</label>
              <input type="text" name="tema" class="form-control" maxlength="255" value="<?= esc($v('tema')) ?>">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi Kegiatan</label>
              <textarea name="deskripsi" class="form-control" rows="5"><?= esc($v('deskripsi')) ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Narasumber</label>
              <textarea name="narasumber" class="form-control" rows="3" placeholder="Satu narasumber per baris, mis. Dr. Nama, M.Psi., Psikolog"><?= esc($v('narasumber')) ?></textarea>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Mulai <span class="text-danger">*</span> <small class="text-muted fw-normal">(WIB)</small></label>
              <input type="datetime-local" name="tanggal_mulai" class="form-control" required value="<?= esc($dt('tanggal_mulai')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Selesai <small class="text-muted fw-normal">(opsional)</small></label>
              <input type="datetime-local" name="tanggal_selesai" class="form-control" value="<?= esc($dt('tanggal_selesai')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Batas Pendaftaran <small class="text-muted fw-normal">(opsional)</small></label>
              <input type="datetime-local" name="batas_pendaftaran" class="form-control" value="<?= esc($dt('batas_pendaftaran')) ?>">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
              <input type="text" name="lokasi" class="form-control" maxlength="255" required
                     placeholder="Alamat/ruang, atau Online (Zoom)" value="<?= esc($v('lokasi')) ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold d-block">Biaya</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="biaya_tipe" id="bt_gratis" value="gratis" <?= $biayaTipe === 'gratis' ? 'checked' : '' ?>>
                <label class="form-check-label" for="bt_gratis">Gratis</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="biaya_tipe" id="bt_berbayar" value="berbayar" <?= $biayaTipe === 'berbayar' ? 'checked' : '' ?>>
                <label class="form-check-label" for="bt_berbayar">Berbayar</label>
              </div>
            </div>
            <div class="col-md-4" id="wrapNominal" style="<?= $biayaTipe === 'berbayar' ? '' : 'display:none;' ?>">
              <label class="form-label fw-semibold">Nominal (Rp)</label>
              <input type="text" inputmode="numeric" name="biaya_nominal" class="form-control" value="<?= esc($v('biaya_nominal')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">No. Kontak</label>
              <input type="text" name="kontak" class="form-control" maxlength="255" placeholder="mis. Nama - 0812xxxxxxx" value="<?= esc($v('kontak')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Link Pendaftaran</label>
              <input type="url" name="link_pendaftaran" class="form-control" maxlength="500" placeholder="https://..." value="<?= esc($v('link_pendaftaran')) ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Pendaftaran</label>
              <select name="status" class="form-select">
                <option value="dibuka"  <?= $v('status', 'dibuka') === 'dibuka' ? 'selected' : '' ?>>Dibuka</option>
                <option value="ditutup" <?= $v('status', 'dibuka') === 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
              </select>
              <div class="form-text">Otomatis dianggap ditutup jika batas pendaftaran atau kegiatan sudah lewat.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Poster <small class="text-muted fw-normal">(JPG/PNG/WEBP, maks 2 MB)</small></label>
              <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
              <div class="form-text">Gunakan rasio <strong>4:5</strong> seperti postingan Instagram (disarankan 1080 × 1350 px) agar tampil penuh.</div>
              <?php if (! empty($agenda['poster'])): ?>
                <div class="mt-2 d-flex align-items-center gap-3">
                  <img src="<?= base_url($agenda['poster']) ?>" alt="Poster" style="height:70px;border-radius:.4rem;">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="hapus_poster" id="hapus_poster" value="1">
                    <label class="form-check-label" for="hapus_poster">Hapus poster</label>
                  </div>
                </div>
              <?php endif ?>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-5">
            <a href="<?= base_url('admin/agenda-kegiatan') ?>" class="btn btn-label-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i>Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('[name="biaya_tipe"]').forEach(r => r.addEventListener('change', () => {
    document.getElementById('wrapNominal').style.display = document.getElementById('bt_berbayar').checked ? '' : 'none';
  }));
</script>

<?= $this->endSection() ?>
