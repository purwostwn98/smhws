<?= $this->extend('layouts/dashboard') ?>
<?php
/** @var array|null $konselor */
/** @var array|null $user */
$konselor     = $konselor ?? null;
$user         = $user ?? null;
$isEdit       = $konselor !== null;
$spesialisasi = $isEdit && is_array($konselor['spesialisasi']) ? implode(', ', $konselor['spesialisasi']) : '';
$old          = fn(string $key, $default = '') => old($key, $default);
$isDosen      = $isEdit && ! empty($konselor['uniid']);
?>
<?= $this->section('title') ?><?= $konselor ? 'Edit Konselor' : 'Tambah Konselor' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= base_url('admin/konselor') ?>" class="btn btn-sm btn-label-secondary">
    <i class="ti tabler-arrow-left me-1"></i>Kembali
  </a>
  <div>
    <h4 class="fw-bold mb-0" style="color:#1a2b40;">
      <?= $isEdit ? 'Edit Konselor' : 'Tambah Konselor Baru' ?>
    </h4>
    <p class="text-muted mb-0" style="font-size:.8rem;">
      <?= $isEdit ? 'Perbarui data profil dan akun konselor.' : 'Isi data untuk menambahkan konselor baru.' ?>
    </p>
  </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<form method="post"
  action="<?= $isEdit ? base_url('admin/konselor/update/' . $konselor['id']) : base_url('admin/konselor/simpan') ?>">
  <?= csrf_field() ?>

  <div class="row g-4">

    <!-- Kolom kiri: Data Akun -->
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header py-3" style="border-bottom:1px solid #eee;">
          <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
            <i class="ti tabler-user-circle me-2 text-primary"></i>Data Akun
          </h6>
        </div>
        <div class="card-body">

          <!-- Nama Lengkap (tanpa gelar) -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Nama Lengkap <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" class="form-control"
              placeholder="cth. Siti Rahayu"
              value="<?= esc($old('name', $user['name'] ?? '')) ?>" required>
            <div class="form-text">Nama tanpa gelar akademik.</div>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Email <span class="text-danger">*</span>
            </label>
            <input type="email" name="email" class="form-control"
              placeholder="konselor@smhws.ums.ac.id"
              value="<?= esc($old('email', $user['email'] ?? '')) ?>" required>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
            </label>
            <div class="input-group">
              <input type="password" name="password" id="inputPassword" class="form-control"
                placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' ?>"
                <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="ti tabler-eye" id="eyeIcon"></i>
              </button>
            </div>
            <?php if ($isEdit): ?>
              <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
            <?php endif ?>
          </div>

          <!-- NIP -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">NIP</label>
            <input type="text" name="nim_nip" class="form-control"
              placeholder="cth. 197801012005012001"
              value="<?= esc($old('nim_nip', $user['nim_nip'] ?? '')) ?>">
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Nomor HP</label>
            <input type="text" name="phone" class="form-control"
              placeholder="cth. 081298765432"
              value="<?= esc($old('phone', $user['phone'] ?? '')) ?>">
          </div>

          <!-- Toggle Dosen UMS -->
          <div class="d-flex align-items-center justify-content-between p-3 rounded-2 mb-3"
            style="background:#f8f9fa;">
            <div>
              <div class="fw-semibold" style="font-size:.875rem;color:#1a2b40;">Dosen UMS</div>
              <div class="text-muted" style="font-size:.78rem;">Aktifkan jika konselor merupakan dosen UMS.</div>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="is_dosen" id="isDosen"
                style="width:2.5rem;height:1.3rem;"
                <?= ($old('is_dosen', $isDosen ? '1' : '')) ? 'checked' : '' ?>
                onchange="document.getElementById('uniidWrapper').style.display=this.checked?'block':'none'">
            </div>
          </div>

          <!-- UniID (tampil jika is_dosen aktif) -->
          <div id="uniidWrapper" class="mb-0"
            style="display:<?= ($old('is_dosen', $isDosen ? '1' : '')) ? 'block' : 'none' ?>;">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              UniID <span class="text-muted fw-normal">(ID Pegawai UMS)</span>
            </label>
            <input type="text" name="uniid" class="form-control"
              placeholder="cth. ps839"
              value="<?= esc($old('uniid', $konselor['uniid'] ?? '')) ?>">
            <div class="form-text">ID pegawai/dosen dari sistem UNIID UMS.</div>
          </div>

        </div>
      </div>
    </div>

    <!-- Kolom kanan: Profil Konselor -->
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header py-3" style="border-bottom:1px solid #eee;">
          <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
            <i class="ti tabler-heart-handshake me-2 text-primary"></i>Profil Konselor
          </h6>
        </div>
        <div class="card-body">

          <!-- Gelar -->
          <div class="row g-3 mb-3">
            <div class="col-sm-4">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Gelar Depan</label>
              <input type="text" name="gelar_depan" class="form-control"
                placeholder="cth. Dr."
                value="<?= esc($old('gelar_depan', $konselor['gelar_depan'] ?? '')) ?>">
            </div>
            <div class="col-sm-8">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Gelar Belakang</label>
              <input type="text" name="gelar_belakang" class="form-control"
                placeholder="cth. M.Psi., Psikolog"
                value="<?= esc($old('gelar_belakang', $konselor['gelar_belakang'] ?? '')) ?>">
            </div>
          </div>

          <!-- No. STR -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Nomor STR / Lisensi</label>
            <input type="text" name="no_str" class="form-control"
              placeholder="cth. STR.1234/PSI/2023"
              value="<?= esc($old('no_str', $konselor['no_str'] ?? '')) ?>">
          </div>

          <!-- Pengalaman & Kapasitas -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Tahun Pengalaman</label>
              <input type="number" name="tahun_pengalaman" class="form-control"
                min="0" max="50"
                value="<?= esc($old('tahun_pengalaman', $konselor['tahun_pengalaman'] ?? 0)) ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" style="font-size:.85rem;">Maks. Pasien/Hari</label>
              <input type="number" name="max_pasien_per_hari" class="form-control"
                min="1" max="20"
                value="<?= esc($old('max_pasien_per_hari', $konselor['max_pasien_per_hari'] ?? 5)) ?>">
            </div>
          </div>

          <!-- Spesialisasi -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Spesialisasi</label>
            <input type="text" name="spesialisasi" class="form-control" id="inputSpesialisasi"
              placeholder="cth. Depresi, Kecemasan, Hubungan Interpersonal"
              value="<?= esc($old('spesialisasi', $spesialisasi)) ?>">
            <div class="form-text">Pisahkan dengan koma. Akan tampil sebagai label pada profil.</div>
            <!-- Preview badges -->
            <div class="d-flex flex-wrap gap-1 mt-2" id="spesialisasiPreview"></div>
          </div>

          <!-- Bio -->
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Bio / Deskripsi Singkat</label>
            <textarea name="bio" class="form-control" rows="4"
              placeholder="Deskripsi singkat tentang konselor..."><?= esc($old('bio', $konselor['bio'] ?? '')) ?></textarea>
          </div>

          <!-- Ketersediaan -->
          <div class="d-flex align-items-center justify-content-between p-3 rounded-2"
            style="background:#f8f9fa;">
            <div>
              <div class="fw-semibold" style="font-size:.875rem;color:#1a2b40;">Tersedia untuk Sesi</div>
              <div class="text-muted" style="font-size:.78rem;">
                Jika dinonaktifkan, konselor tidak akan muncul di pilihan mahasiswa.
              </div>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable"
                style="width:2.5rem;height:1.3rem;"
                <?= $old('is_available', $konselor['is_available'] ?? 1) ? 'checked' : '' ?>>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

  <!-- Submit -->
  <div class="d-flex gap-2 justify-content-end mt-4">
    <a href="<?= base_url('admin/konselor') ?>" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary px-4">
      <i class="ti <?= $isEdit ? 'tabler-device-floppy' : 'tabler-user-plus' ?> me-2"></i>
      <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Konselor' ?>
    </button>
  </div>

</form>

<script>
  // Toggle password visibility
  document.getElementById('togglePassword').addEventListener('click', function() {
    var input = document.getElementById('inputPassword');
    var icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('tabler-eye', 'tabler-eye-off');
    } else {
      input.type = 'password';
      icon.classList.replace('tabler-eye-off', 'tabler-eye');
    }
  });

  // Preview spesialisasi badges
  function renderSpesialisasi(val) {
    var preview = document.getElementById('spesialisasiPreview');
    preview.innerHTML = '';
    val.split(',').forEach(function(s) {
      s = s.trim();
      if (s) {
        var badge = document.createElement('span');
        badge.className = 'badge bg-label-primary';
        badge.style.fontSize = '.75rem';
        badge.textContent = s;
        preview.appendChild(badge);
      }
    });
  }

  var inputSp = document.getElementById('inputSpesialisasi');
  renderSpesialisasi(inputSp.value);
  inputSp.addEventListener('input', function() {
    renderSpesialisasi(this.value);
  });
</script>

<?= $this->endSection() ?>