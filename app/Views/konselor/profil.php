<?= $this->extend('layouts/dashboard') ?>
<?php
/** @var array $konselor */
/** @var array $user */
/** @var array $jadwalGrid */
/** @var array $jadwalSlots */
$spesialisasi  = is_array($konselor['spesialisasi']) ? implode(', ', $konselor['spesialisasi']) : '';
$old           = fn(string $key, $default = '') => old($key, $default);
$isDosen       = ! empty($konselor['uniid']);
$jadwalCurrent = old('jadwal', $jadwalGrid);
$hariList      = [
  'senin'  => 'Senin',
  'selasa' => 'Selasa',
  'rabu'   => 'Rabu',
  'kamis'  => 'Kamis',
  'jumat'  => "Jum'at",
  'sabtu'  => 'Sabtu',
];
?>
<?= $this->section('title') ?>Profil & Jadwal<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Profil &amp; Jadwal</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Kelola informasi profil dan jadwal konseling Anda.
    </p>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<form method="post" enctype="multipart/form-data" action="<?= base_url('konselor/profil/update') ?>">
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

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Nama Lengkap <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" class="form-control"
              placeholder="cth. Siti Rahayu"
              value="<?= esc($old('name', $user['name'] ?? '')) ?>" required>
            <div class="form-text">Nama tanpa gelar akademik.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">
              Email <span class="text-danger">*</span>
            </label>
            <input type="email" name="email" class="form-control"
              value="<?= esc($old('email', $user['email'] ?? '')) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Ganti Password</label>
            <div class="input-group">
              <input type="password" name="password" id="inputPassword" class="form-control"
                placeholder="Kosongkan jika tidak ingin diubah"
                minlength="8" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="ti tabler-eye" id="eyeIcon"></i>
              </button>
            </div>
            <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">NIP</label>
            <input type="text" name="uniid" class="form-control"
              placeholder="cth. 197801012005012001"
              value="<?= esc($old('uniid', $user['uniid'] ?? '')) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Nomor HP</label>
            <input type="text" name="phone" class="form-control"
              placeholder="cth. 081298765432"
              value="<?= esc($old('phone', $user['phone'] ?? '')) ?>">
          </div>

          <div class="d-flex align-items-center justify-content-between p-3 rounded-2 mb-3"
            style="background:#f8f9fa;">
            <div>
              <div class="fw-semibold" style="font-size:.875rem;color:#1a2b40;">Dosen UMS</div>
              <div class="text-muted" style="font-size:.78rem;">Aktifkan jika Anda merupakan dosen UMS.</div>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" name="is_dosen" id="isDosen"
                style="width:2.5rem;height:1.3rem;"
                <?= ($old('is_dosen', $isDosen ? '1' : '')) ? 'checked' : '' ?>
                onchange="document.getElementById('uniidWrapper').style.display=this.checked?'block':'none'">
            </div>
          </div>

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

    <!-- Kolom kanan: Profil Psikolog -->
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header py-3" style="border-bottom:1px solid #eee;">
          <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
            <i class="ti tabler-heart-handshake me-2 text-primary"></i>Profil Psikolog
          </h6>
        </div>
        <div class="card-body">

          <!-- Foto Profil -->
          <div class="mb-4 pb-3" style="border-bottom:1px solid #eee;">
            <label class="form-label fw-semibold d-block" style="font-size:.85rem;">Foto Profil</label>
            <div class="d-flex align-items-center gap-3">
              <div style="width:80px;height:80px;border-radius:50%;overflow:hidden;background:#f0f0f0;
                   flex-shrink:0;display:flex;align-items:center;justify-content:center;border:2px solid #e0e0e0;">
                <?php if (! empty($konselor['foto'])): ?>
                  <img id="fotoPreview" src="<?= base_url($konselor['foto']) ?>"
                    style="width:100%;height:100%;object-fit:cover;" alt="Foto">
                <?php else: ?>
                  <i id="fotoPlaceholder" class="ti tabler-user" style="font-size:2rem;color:#bbb;"></i>
                  <img id="fotoPreview" src="" style="width:100%;height:100%;object-fit:cover;display:none;" alt="Foto">
                <?php endif ?>
              </div>
              <div class="flex-grow-1">
                <input type="file" name="foto" id="inputFoto"
                  accept="image/jpeg,image/png,image/webp"
                  class="form-control form-control-sm" style="max-width:300px;"
                  onchange="previewFoto(this)">
                <div class="form-text">JPG, PNG, atau WebP. Maks. 2 MB.</div>
                <?php if (! empty($konselor['foto'])): ?>
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="hapus_foto" id="hapusFoto" value="1">
                    <label class="form-check-label text-danger" for="hapusFoto" style="font-size:.8rem;">Hapus foto saat ini</label>
                  </div>
                <?php endif ?>
              </div>
            </div>
          </div>

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

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Nomor STR / Lisensi</label>
            <input type="text" name="no_str" class="form-control"
              placeholder="cth. STR.1234/PSI/2023"
              value="<?= esc($old('no_str', $konselor['no_str'] ?? '')) ?>">
          </div>

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

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Spesialisasi</label>
            <input type="text" name="spesialisasi" class="form-control" id="inputSpesialisasi"
              placeholder="cth. Depresi, Kecemasan, Hubungan Interpersonal"
              value="<?= esc($old('spesialisasi', $spesialisasi)) ?>">
            <div class="form-text">Pisahkan dengan koma. Akan tampil sebagai label pada profil.</div>
            <div class="d-flex flex-wrap gap-1 mt-2" id="spesialisasiPreview"></div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.85rem;">Bio / Deskripsi Singkat</label>
            <textarea name="bio" class="form-control" rows="4"
              placeholder="Deskripsi singkat tentang diri Anda..."><?= esc($old('bio', $konselor['bio'] ?? '')) ?></textarea>
          </div>

          <div class="d-flex align-items-center justify-content-between p-3 rounded-2"
            style="background:#f8f9fa;">
            <div>
              <div class="fw-semibold" style="font-size:.875rem;color:#1a2b40;">Tersedia untuk Sesi</div>
              <div class="text-muted" style="font-size:.78rem;">
                Jika dinonaktifkan, Anda tidak akan muncul di pilihan mahasiswa.
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

    <!-- Jadwal Preferensi Konseling -->
    <div class="col-12">
      <div class="card">
        <div class="card-header py-3" style="border-bottom:1px solid #eee;">
          <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
            <i class="ti tabler-calendar-time me-2 text-primary"></i>Jadwal Preferensi Konseling
          </h6>
          <div class="text-muted mt-1" style="font-size:.78rem;">
            Centang sesi yang tersedia dan pilih modenya. Sabtu hanya tersedia mode Online.
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="min-width:680px;">
              <thead class="table-light">
                <tr>
                  <th class="text-center py-3" style="width:120px;font-size:.8rem;">Sesi</th>
                  <?php foreach ($hariList as $hari => $label): ?>
                    <th class="text-center py-3" style="font-size:.8rem;">
                      <?= $label ?>
                      <?php if ($hari === 'sabtu'): ?>
                        <div class="mt-1">
                          <span class="badge bg-label-info" style="font-size:.65rem;">online only</span>
                        </div>
                      <?php endif ?>
                    </th>
                  <?php endforeach ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($jadwalSlots as $slotKey => $slot): ?>
                  <tr>
                    <td class="text-center fw-semibold py-3" style="font-size:.82rem;background:#f8f9fa;white-space:nowrap;">
                      <?= $slot['label'] ?>
                    </td>
                    <?php foreach ($hariList as $hari => $hLabel):
                      $val = $jadwalCurrent[$hari][$slotKey] ?? '';
                    ?>
                      <td class="text-center p-2">
                        <select name="jadwal[<?= $hari ?>][<?= $slotKey ?>]"
                          class="form-select form-select-sm jadwal-select"
                          style="font-size:.78rem;text-align:center;"
                          onchange="colorizeJadwal(this)">
                          <option value="">—</option>
                          <option value="online" <?= $val === 'online' ? 'selected' : '' ?>>Online</option>
                          <?php if ($hari !== 'sabtu'): ?>
                            <option value="offline" <?= $val === 'offline'  ? 'selected' : '' ?>>Offline</option>
                            <option value="keduanya" <?= $val === 'keduanya' ? 'selected' : '' ?>>Online &amp; Offline</option>
                          <?php endif ?>
                        </select>
                      </td>
                    <?php endforeach ?>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Submit -->
  <div class="d-flex gap-2 justify-content-end mt-4">
    <button type="submit" class="btn btn-primary px-4">
      <i class="ti tabler-device-floppy me-2"></i>Simpan Perubahan
    </button>
  </div>

</form>

<script>
  function previewFoto(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      var preview = document.getElementById('fotoPreview');
      var placeholder = document.getElementById('fotoPlaceholder');
      preview.src = e.target.result;
      preview.style.display = 'block';
      if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }

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

  function colorizeJadwal(sel) {
    const map = {
      '': '',
      'online': '#e3f2fd',
      'offline': '#e8f5e9',
      'keduanya': '#f3e5f5',
    };
    sel.style.backgroundColor = map[sel.value] ?? '';
  }
  document.querySelectorAll('.jadwal-select').forEach(colorizeJadwal);

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