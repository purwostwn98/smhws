<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Buat Janji Konseling<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
/* Step indicator */
.smhws-steps { display:flex; gap:0; margin-bottom:2rem; }
.smhws-step {
  flex:1; text-align:center; position:relative;
  padding:.75rem .5rem .5rem;
}
.smhws-step::after {
  content:''; position:absolute; top:1.4rem; left:50%; width:100%;
  height:2px; background:#e0e0e0; z-index:0;
}
.smhws-step:last-child::after { display:none; }
.smhws-step-icon {
  width:2.2rem; height:2.2rem; border-radius:50%;
  background:#e0e0e0; color:#888; font-weight:700; font-size:.85rem;
  display:inline-flex; align-items:center; justify-content:center;
  position:relative; z-index:1; margin-bottom:.3rem; transition:.25s;
}
.smhws-step.active   .smhws-step-icon { background:#1a5f7a; color:#fff; }
.smhws-step.done     .smhws-step-icon { background:#2d9b6e; color:#fff; }
.smhws-step-label { font-size:.72rem; color:#999; display:block; line-height:1.2; }
.smhws-step.active .smhws-step-label { color:#1a5f7a; font-weight:600; }
.smhws-step.done   .smhws-step-label { color:#2d9b6e; }

/* Form panels */
.step-panel { display:none; }
.step-panel.active { display:block; }

/* DASS item rows */
.dass-row { padding:.6rem .75rem; border-radius:.5rem; transition:background .15s; }
.dass-row:hover { background:rgba(26,95,122,.04); }
.dass-row .form-check-inline { margin-right:0; }
.dass-answer { display:flex; gap:.5rem; }
.dass-answer label { cursor:pointer; width:2.2rem; height:2.2rem; border-radius:.4rem;
  border:2px solid #e0e0e0; display:flex; align-items:center; justify-content:center;
  font-size:.8rem; font-weight:600; color:#888; transition:.15s; user-select:none; }
.dass-answer input[type=radio] { display:none; }
.dass-answer input[type=radio]:checked + label { border-color:#1a5f7a; background:#1a5f7a; color:#fff; }

/* Safety radio cards */
.safety-card { display:flex; gap:.75rem; flex-wrap:wrap; }
.safety-card label {
  flex:1; min-width:120px; padding:.6rem 1rem; border-radius:.5rem;
  border:2px solid #e0e0e0; cursor:pointer; text-align:center; font-size:.82rem;
  transition:.15s; color:#555;
}
.safety-card input[type=radio] { display:none; }
.safety-card input[type=radio]:checked + label { border-color:#1a5f7a; background:#eaf4f8; color:#1a5f7a; font-weight:600; }

/* Required star */
.req { color:#dc3545; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb & header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Formulir Pendaftaran Konseling</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Isi semua bagian dengan jujur. Data bersifat <strong>rahasia</strong> dan hanya dapat diakses oleh konselor SMHWS.
    </p>
  </div>
</div>

<?php
$flashErrors  = session()->getFlashdata('errors') ?? [];
$flashError   = session()->getFlashdata('error');
$flashErrStep = (int)(session()->getFlashdata('error_step') ?? 1);
$fieldLabels  = [
    'jenis_kelamin'  => 'Jenis Kelamin',
    'usia'           => 'Usia',
    'agama'          => 'Agama',
    'semester'       => 'Semester',
    'metode'         => 'Metode Konseling',
    'tema_konseling' => 'Tema Konseling',
    'keluhan_utama'  => 'Keluhan Utama',
    'mulai_keluhan'  => 'Mulai Keluhan',
    'upaya_dilakukan'=> 'Upaya yang Dilakukan',
];
?>
<?php if ($flashError): ?>
  <div class="alert alert-danger alert-dismissible mb-4" role="alert" id="formErrorAlert">
    <div class="fw-semibold mb-1"><i class="ti tabler-alert-circle me-2"></i><?= esc($flashError) ?></div>
    <?php if (! empty($flashErrors)): ?>
      <ul class="mb-0 mt-2" style="font-size:.875rem;padding-left:1.4rem;">
        <?php foreach ($flashErrors as $field => $msg): ?>
          <li><strong><?= esc($fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field))) ?>:</strong> <?= esc($msg) ?></li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Step Indicator -->
<div class="smhws-steps mb-2" id="stepIndicator">
  <div class="smhws-step active" data-step="1">
    <div class="smhws-step-icon">1</div>
    <span class="smhws-step-label">Identitas &amp;<br>Preferensi</span>
  </div>
  <div class="smhws-step" data-step="2">
    <div class="smhws-step-icon">2</div>
    <span class="smhws-step-label">Keluhan<br>Utama</span>
  </div>
  <div class="smhws-step" data-step="3">
    <div class="smhws-step-icon">3</div>
    <span class="smhws-step-label">Asesmen<br>DASS-21</span>
  </div>
  <div class="smhws-step" data-step="4">
    <div class="smhws-step-icon">4</div>
    <span class="smhws-step-label">Skrining<br>Keselamatan</span>
  </div>
</div>

<form action="<?= base_url('janji/simpan') ?>" method="POST" id="formJanji" novalidate>
  <?= csrf_field() ?>

<!-- ═══════════════════════════════════════════════════════════════
     STEP 1 — Identitas & Preferensi Konseling
═════════════════════════════════════════════════════════════════ -->
<div class="step-panel active" id="step1">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="ti tabler-user me-2" style="color:#1a5f7a;"></i>Data Diri</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">

        <!-- Nama (read-only dari sesi) -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Nama Lengkap</label>
          <input type="text" class="form-control" value="<?= esc($user['name']) ?>" readonly />
        </div>
        <!-- NIM -->
        <div class="col-md-6">
          <label class="form-label fw-medium">NIM</label>
          <input type="text" class="form-control" value="<?= esc($user['nim_nip'] ?? '-') ?>" readonly />
        </div>
        <!-- Email -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Email</label>
          <input type="email" class="form-control" value="<?= esc($user['email']) ?>" readonly />
        </div>
        <!-- No. WA -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Nomor WhatsApp <span class="req">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti tabler-brand-whatsapp" style="color:#25D366;"></i></span>
            <input type="tel" name="phone" class="form-control"
              placeholder="08xxxxxxxxxx" value="<?= esc($user['phone'] ?? '') ?>" required />
          </div>
        </div>
        <!-- Prodi & Fakultas -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Program Studi</label>
          <input type="text" class="form-control" value="<?= esc($user['prodi'] ?? '-') ?>" readonly />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-medium">Fakultas</label>
          <input type="text" class="form-control" value="<?= esc($user['fakultas'] ?? '-') ?>" readonly />
        </div>
        <!-- Jenis Kelamin -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Jenis Kelamin <span class="req">*</span></label>
          <select name="jenis_kelamin" class="form-select" required>
            <option value="">-- Pilih --</option>
            <option value="laki-laki">Laki-laki</option>
            <option value="perempuan">Perempuan</option>
          </select>
        </div>
        <!-- Usia -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Usia <span class="req">*</span></label>
          <input type="number" name="usia" class="form-control" placeholder="Contoh: 20" min="15" max="99" required />
        </div>
        <!-- Semester -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Semester <span class="req">*</span></label>
          <select name="semester" class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php for ($s = 1; $s <= 14; $s++): ?>
              <option value="<?= $s ?>">Semester <?= $s ?></option>
            <?php endfor ?>
          </select>
        </div>
        <!-- Agama -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Agama <span class="req">*</span></label>
          <select name="agama" class="form-select" required>
            <option value="">-- Pilih --</option>
            <option>Islam</option><option>Kristen</option><option>Katolik</option>
            <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
            <option>Lainnya</option>
          </select>
        </div>
        <!-- Status Pernikahan -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Status Pernikahan</label>
          <select name="status_pernikahan" class="form-select">
            <option value="belum_menikah">Belum Menikah</option>
            <option value="menikah">Menikah</option>
            <option value="cerai">Cerai</option>
          </select>
        </div>
        <!-- Dosen PA -->
        <div class="col-md-4">
          <label class="form-label fw-medium">Dosen Pembimbing Akademik</label>
          <input type="text" name="dosen_pa" class="form-control" placeholder="Nama dosen PA" />
        </div>
        <!-- Domisili -->
        <div class="col-12">
          <label class="form-label fw-medium">Domisili / Tempat Tinggal Saat Ini</label>
          <input type="text" name="domisili" class="form-control" placeholder="Contoh: Kos Jl. Ahmad Yani No. 5, Surakarta" />
        </div>

      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="ti tabler-calendar-event me-2" style="color:#1a5f7a;"></i>Preferensi Konseling</h5>
    </div>
    <div class="card-body">
      <div class="row g-3">

        <!-- Metode -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Metode Konseling <span class="req">*</span></label>
          <div class="d-flex gap-3 flex-wrap mt-1">
            <?php foreach (['offline'=>'Tatap Muka (Offline)', 'online'=>'Daring (Online)', 'hybrid'=>'Fleksibel (Hybrid)'] as $val => $lbl): ?>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="metode" id="metode_<?= $val ?>" value="<?= $val ?>" <?= $val==='offline'?'checked':'' ?> required />
                <label class="form-check-label" for="metode_<?= $val ?>"><?= $lbl ?></label>
              </div>
            <?php endforeach ?>
          </div>
        </div>

        <!-- Pernah konseling -->
        <div class="col-md-6">
          <label class="form-label fw-medium">Riwayat Layanan SMHWS</label>
          <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="pernah_konseling_smhws" id="pernahKonseling" value="1" />
            <label class="form-check-label" for="pernahKonseling">
              Saya <strong>sudah pernah</strong> menggunakan layanan konseling SMHWS sebelumnya
            </label>
          </div>
        </div>

        <!-- Jadwal pilihan -->
        <div class="col-12">
          <label class="form-label fw-medium">Jadwal yang Diinginkan <small class="text-muted">(pilih satu atau lebih)</small></label>
          <div class="table-responsive mt-1">
            <table class="table table-bordered table-sm align-middle" style="min-width:500px;">
              <thead class="table-light text-center">
                <tr>
                  <th>Hari</th>
                  <th>Pagi (08.00–12.00)</th>
                  <th>Siang (13.00–16.00)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (['senin','selasa','rabu','kamis','jumat','sabtu'] as $h): ?>
                <tr>
                  <td class="fw-medium ps-3"><?= ucfirst($h) ?></td>
                  <td class="text-center">
                    <input type="radio" name="jadwal_<?= $h ?>" id="j_<?= $h ?>_pagi" value="08:00-12:00"
                      class="form-check-input" />
                  </td>
                  <td class="text-center">
                    <input type="radio" name="jadwal_<?= $h ?>" id="j_<?= $h ?>_siang" value="13:00-16:00"
                      class="form-check-input" />
                  </td>
                </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pilihan konselor -->
        <div class="col-12">
          <label class="form-label fw-medium">Konselor yang Diinginkan <small class="text-muted">(boleh pilih lebih dari 1)</small></label>
          <?php if (empty($konselor)): ?>
            <p class="text-muted" style="font-size:.85rem;">Belum ada konselor tersedia saat ini.</p>
          <?php else: ?>
          <div class="row g-2 mt-1">
            <?php foreach ($konselor as $k): ?>
              <?php
                $inisial  = strtoupper(substr($k['name'], 0, 2));
                $namaLengkap = trim(($k['gelar_depan'] ? $k['gelar_depan'].' ' : '') . $k['name'] . ($k['gelar_belakang'] ? ', '.$k['gelar_belakang'] : ''));
                $spesialis = is_array($k['spesialisasi']) ? implode(', ', array_slice($k['spesialisasi'], 0, 2)) : ($k['spesialisasi'] ?? '-');
              ?>
              <div class="col-sm-6 col-lg-4">
                <label class="d-flex align-items-start gap-2 p-2 border rounded-3 cursor-pointer h-100"
                  style="transition:border-color .15s;"
                  onmouseover="this.style.borderColor='#1a5f7a'"
                  onmouseout="this.style.borderColor=''">
                  <input type="checkbox" name="konselor_pilihan[]"
                    class="form-check-input mt-1 flex-shrink-0" value="<?= $k['id'] ?>" />
                  <div class="d-flex align-items-start gap-2">
                    <div class="avatar avatar-sm flex-shrink-0">
                      <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.15);color:#1a5f7a;font-weight:700;font-size:.75rem;">
                        <?= $inisial ?>
                      </div>
                    </div>
                    <div>
                      <div class="fw-semibold" style="font-size:.82rem;color:#1a2b40;"><?= esc($namaLengkap) ?></div>
                      <div class="text-muted" style="font-size:.72rem;"><?= esc($spesialis) ?></div>
                    </div>
                  </div>
                </label>
              </div>
            <?php endforeach ?>
          </div>
          <?php endif ?>
        </div>

      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end">
    <button type="button" class="btn btn-primary btn-next" data-to="2">
      Lanjut: Keluhan <i class="ti tabler-arrow-right ms-1"></i>
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     STEP 2 — Keluhan Utama
═════════════════════════════════════════════════════════════════ -->
<div class="step-panel" id="step2">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0"><i class="ti tabler-message-heart me-2" style="color:#1a5f7a;"></i>Keluhan &amp; Kondisi</h5>
    </div>
    <div class="card-body">
      <div class="row g-4">

        <div class="col-12">
          <label class="form-label fw-medium">Tema Konseling <span class="req">*</span></label>
          <div class="d-flex flex-wrap gap-2 mt-1">
            <?php
            $temaOptions = [
                'akademik'         => ['label' => 'Akademik',           'icon' => 'tabler-school'],
                'keorganisasian'   => ['label' => 'Keorganisasian',     'icon' => 'tabler-building-community'],
                'pengembangan_diri'=> ['label' => 'Pengembangan Diri',  'icon' => 'tabler-plant-2'],
                'relasi'           => ['label' => 'Relasi',             'icon' => 'tabler-users'],
                'pribadi'          => ['label' => 'Pribadi',            'icon' => 'tabler-user-heart'],
                'keluarga'         => ['label' => 'Keluarga',           'icon' => 'tabler-home-heart'],
                'lainnya'          => ['label' => 'Lainnya',            'icon' => 'tabler-dots-circle-horizontal'],
            ];
            $oldTema = old('tema_konseling', '');
            foreach ($temaOptions as $val => $opt): ?>
            <div>
              <input type="radio" class="btn-check" name="tema_konseling"
                     id="tema_<?= $val ?>" value="<?= $val ?>"
                     <?= $oldTema === $val ? 'checked' : '' ?> required />
              <label class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-3" for="tema_<?= $val ?>">
                <i class="ti <?= $opt['icon'] ?>" style="font-size:1rem;"></i>
                <?= $opt['label'] ?>
              </label>
            </div>
            <?php endforeach ?>
          </div>
          <div class="form-text">Pilih tema yang paling sesuai dengan yang ingin kamu konsultasikan.</div>
        </div>

        <div class="col-12">
          <label class="form-label fw-medium">Keluhan Utama yang Ingin Dibicarakan <span class="req">*</span></label>
          <textarea name="keluhan_utama" class="form-control" rows="5"
            placeholder="Ceritakan secara singkat apa yang sedang kamu rasakan atau alami. Tidak perlu sempurna, tuliskan apa adanya..."
            required minlength="20"></textarea>
          <div class="form-text">Minimal 20 karakter. Informasi ini bersifat rahasia.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-medium">Keluhan Dirasakan Sejak</label>
          <input type="text" name="mulai_keluhan" class="form-control"
            placeholder="Contoh: 2 minggu lalu, sejak awal semester, dll." />
        </div>

        <div class="col-md-6">
          <label class="form-label fw-medium">Upaya yang Sudah Dilakukan</label>
          <textarea name="upaya_dilakukan" class="form-control" rows="3"
            placeholder="Contoh: bercerita ke teman, berdoa, olahraga, dll. Jika belum pernah, tulis 'belum ada'."></textarea>
        </div>

      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between">
    <button type="button" class="btn btn-label-primary btn-prev" data-to="1">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </button>
    <button type="button" class="btn btn-primary btn-next" data-to="3">
      Lanjut: Asesmen DASS <i class="ti tabler-arrow-right ms-1"></i>
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     STEP 3 — DASS-21
═════════════════════════════════════════════════════════════════ -->
<div class="step-panel" id="step3">
  <div class="card mb-4">
    <div class="card-header">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
        <div>
          <h5 class="card-title mb-0"><i class="ti tabler-brain me-2" style="color:#1a5f7a;"></i>Asesmen DASS-21</h5>
          <p class="text-muted mb-0 mt-1" style="font-size:.82rem;">
            Pilih jawaban yang paling sesuai dengan kondisi kamu <strong>dalam 1 minggu terakhir</strong>.
          </p>
        </div>
        <div id="dassProgress" class="badge bg-label-primary" style="font-size:.8rem;">0 / 21 dijawab</div>
      </div>
    </div>
    <div class="card-body p-0">
      <!-- Skala legend -->
      <div class="d-flex gap-3 flex-wrap px-4 py-3 border-bottom" style="background:#f8fafb;">
        <div class="d-flex align-items-center gap-2">
          <div class="dass-answer"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;border:2px solid #ccc;">0</label></div>
          <small class="text-muted">Tidak pernah</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="dass-answer"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;border:2px solid #ccc;">1</label></div>
          <small class="text-muted">Kadang-kadang</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="dass-answer"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;border:2px solid #ccc;">2</label></div>
          <small class="text-muted">Cukup sering</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="dass-answer"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;border:2px solid #ccc;">3</label></div>
          <small class="text-muted">Sering sekali</small>
        </div>
      </div>

      <!-- Items -->
      <div class="px-2 py-1">
        <?php foreach ($dassItems as $item): ?>
        <div class="dass-row d-flex align-items-center gap-3" id="row_dass_<?= $item['id'] ?>">
          <div class="flex-shrink-0 text-muted fw-bold" style="width:1.8rem;font-size:.82rem;text-align:right;">
            <?= $item['nomor'] ?>.
          </div>
          <div class="flex-grow-1" style="font-size:.875rem;">
            <?= esc($item['pernyataan']) ?>
          </div>
          <div class="dass-answer flex-shrink-0" style="gap:.35rem;">
            <?php foreach ([0,1,2,3] as $val): ?>
              <input type="radio" name="dass_item_<?= $item['id'] ?>" id="di_<?= $item['id'] ?>_<?= $val ?>"
                value="<?= $val ?>" required class="dass-radio" data-item="<?= $item['id'] ?>" />
              <label for="di_<?= $item['id'] ?>_<?= $val ?>"><?= $val ?></label>
            <?php endforeach ?>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between">
    <button type="button" class="btn btn-label-primary btn-prev" data-to="2">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </button>
    <button type="button" class="btn btn-primary btn-next" data-to="4" id="btnNextDass">
      Lanjut: Skrining <i class="ti tabler-arrow-right ms-1"></i>
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     STEP 4 — Safety Screening
═════════════════════════════════════════════════════════════════ -->
<div class="step-panel" id="step4">
  <div class="card mb-4">
    <div class="card-header" style="background:rgba(240,165,0,.08);border-left:4px solid #f0a500;">
      <h5 class="card-title mb-0">
        <i class="ti tabler-shield-heart me-2" style="color:#f0a500;"></i>Skrining Keselamatan
      </h5>
      <p class="text-muted mb-0 mt-1" style="font-size:.82rem;">
        Pertanyaan ini bersifat <strong>rahasia penuh</strong>. Jawabanmu membantu konselor memberikan bantuan yang tepat.
        Tidak ada jawaban yang benar atau salah.
      </p>
    </div>
    <div class="card-body">
      <div class="d-flex flex-column gap-4">

        <!-- Q1 -->
        <div>
          <label class="form-label fw-medium">
            1. Dalam <strong>3 bulan terakhir</strong>, apakah kamu pernah melukai diri sendiri (<em>self-harm</em>),
            baik dengan atau tanpa niat mengakhiri hidup? <span class="req">*</span>
          </label>
          <div class="safety-card">
            <?php foreach (['tidak'=>'Tidak', 'ya'=>'Ya', 'tidak_mau_menjawab'=>'Tidak Ingin Menjawab Saat Ini'] as $v => $l): ?>
              <input type="radio" name="pernah_selfharm" id="sh_<?= $v ?>" value="<?= $v ?>" required />
              <label for="sh_<?= $v ?>"><?= $l ?></label>
            <?php endforeach ?>
          </div>
        </div>

        <!-- Q2 -->
        <div>
          <label class="form-label fw-medium">
            2. Saat ini, apakah kamu merasa <strong>aman</strong> dengan dirimu sendiri? <span class="req">*</span>
          </label>
          <div class="safety-card">
            <?php foreach (['ya'=>'Ya, Merasa Aman', 'tidak'=>'Tidak', 'tidak_mau_menjawab'=>'Tidak Ingin Menjawab Saat Ini'] as $v => $l): ?>
              <input type="radio" name="merasa_aman" id="ma_<?= $v ?>" value="<?= $v ?>" required />
              <label for="ma_<?= $v ?>"><?= $l ?></label>
            <?php endforeach ?>
          </div>
        </div>

        <!-- Q3 -->
        <div>
          <label class="form-label fw-medium">
            3. Dalam <strong>1 bulan terakhir</strong>, apakah kamu pernah memiliki pikiran untuk
            mengakhiri hidup atau berharap tidak hidup lagi? <span class="req">*</span>
          </label>
          <div class="safety-card">
            <?php foreach (['tidak'=>'Tidak', 'ya'=>'Ya', 'tidak_mau_menjawab'=>'Tidak Ingin Menjawab Saat Ini'] as $v => $l): ?>
              <input type="radio" name="pikiran_mengakhiri_hidup" id="pm_<?= $v ?>" value="<?= $v ?>" required />
              <label for="pm_<?= $v ?>"><?= $l ?></label>
            <?php endforeach ?>
          </div>
        </div>

        <!-- Q4 (muncul jika Q3 = ya / tidak_mau_menjawab) -->
        <div id="q4Wrap" style="display:none;">
          <label class="form-label fw-medium">
            4. Jika demikian, apakah pikiran tersebut terasa <strong>mengganggu atau sulit dikendalikan</strong>?
          </label>
          <div class="safety-card">
            <?php foreach (['ya'=>'Ya', 'tidak'=>'Tidak', 'tidak_berlaku'=>'Tidak Berlaku'] as $v => $l): ?>
              <input type="radio" name="pikiran_mengganggu" id="pg_<?= $v ?>" value="<?= $v ?>" />
              <label for="pg_<?= $v ?>"><?= $l ?></label>
            <?php endforeach ?>
          </div>
          <input type="hidden" name="pikiran_mengganggu" value="tidak_berlaku" id="pgDefault" />
        </div>

        <!-- Riwayat tambahan -->
        <div id="selfharmDetail" style="display:none;">
          <label class="form-label fw-medium">Keterangan Tambahan (opsional)</label>
          <textarea name="riwayat_selfharm_keterangan" class="form-control" rows="3"
            placeholder="Jika kamu ingin berbagi lebih lanjut, silakan tulis di sini. Ini sepenuhnya opsional."></textarea>
        </div>

        <!-- Informasi dukungan -->
        <div class="p-3 rounded-3 smhws-emergency">
          <div class="d-flex gap-3 align-items-start">
            <i class="ti tabler-heart-handshake mt-1" style="color:#f0a500;font-size:1.2rem;flex-shrink:0;"></i>
            <div style="font-size:.82rem;">
              <strong>Kamu tidak sendirian.</strong> Apapun yang kamu rasakan, tim konselor SMHWS
              siap mendampingimu.<br/>
              Jika kamu membutuhkan bantuan segera:
              <strong>Hotline 24 Jam: 119 ext 8</strong> &nbsp;|&nbsp;
              <strong>WA: 0877-7777-0000</strong>
            </div>
          </div>
        </div>

        <!-- Persetujuan -->
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="persetujuan" name="persetujuan" required />
          <label class="form-check-label fw-medium" for="persetujuan">
            Saya menyatakan bahwa semua informasi yang saya isi adalah <strong>benar dan jujur</strong>,
            dan saya setuju data ini digunakan untuk keperluan layanan konseling SMHWS secara <strong>rahasia</strong>.
          </label>
        </div>

      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between">
    <button type="button" class="btn btn-label-primary btn-prev" data-to="3">
      <i class="ti tabler-arrow-left me-1"></i> Kembali
    </button>
    <button type="submit" class="btn btn-primary btn-lg" id="btnKirim">
      <i class="ti tabler-send me-1"></i> Kirim Pendaftaran
    </button>
  </div>
</div>

</form>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
(function () {
  'use strict';

  // Jika ada error dari server, mulai di step yang bermasalah
  let currentStep = <?= $flashErrStep > 1 ? $flashErrStep : 1 ?>;
  const TOTAL = 4;

  // ── Step navigation ────────────────────────────────────────────────────
  function goTo(step) {
    if (step < 1 || step > TOTAL) return;
    if (step > currentStep && !validateStep(currentStep)) return;

    document.getElementById('step' + currentStep).classList.remove('active');
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    updateIndicator();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateIndicator() {
    document.querySelectorAll('#stepIndicator .smhws-step').forEach(el => {
      const s = parseInt(el.dataset.step);
      el.classList.remove('active', 'done');
      if (s === currentStep) el.classList.add('active');
      if (s < currentStep)  el.classList.add('done');
    });
  }

  // Aktifkan step awal (bisa bukan step 1 jika ada error dari server)
  if (currentStep !== 1) {
    document.getElementById('step1').classList.remove('active');
    document.getElementById('step' + currentStep).classList.add('active');
    updateIndicator();
  }

  document.querySelectorAll('.btn-next').forEach(btn =>
    btn.addEventListener('click', () => goTo(parseInt(btn.dataset.to)))
  );
  document.querySelectorAll('.btn-prev').forEach(btn =>
    btn.addEventListener('click', () => goTo(parseInt(btn.dataset.to)))
  );

  // ── Validation per step ────────────────────────────────────────────────
  function validateStep(step) {
    const panel = document.getElementById('step' + step);
    const required = panel.querySelectorAll('[required]');
    let valid = true;

    required.forEach(el => {
      if (el.type === 'radio') {
        const group = panel.querySelectorAll(`[name="${el.name}"]`);
        const checked = [...group].some(r => r.checked);
        const wrap = document.getElementById('row_dass_' + el.dataset?.item) || el.closest('.dass-row, .col-md-6, .col-12, .col-md-4');
        if (!checked) {
          valid = false;
          if (wrap) wrap.style.outline = '2px solid #dc3545';
        } else {
          if (wrap) wrap.style.outline = '';
        }
      } else if (el.type === 'checkbox') {
        if (!el.checked) { valid = false; el.classList.add('is-invalid'); }
        else el.classList.remove('is-invalid');
      } else {
        if (!el.value.trim()) { valid = false; el.classList.add('is-invalid'); }
        else el.classList.remove('is-invalid');
      }
    });

    if (!valid) {
      const first = panel.querySelector('[required]:invalid, .is-invalid, [style*="outline"]');
      first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return valid;
  }

  // ── DASS progress counter ──────────────────────────────────────────────
  const dassProgress = document.getElementById('dassProgress');
  document.querySelectorAll('.dass-radio').forEach(radio => {
    radio.addEventListener('change', () => {
      const answered = new Set(
        [...document.querySelectorAll('.dass-radio:checked')].map(r => r.dataset.item)
      ).size;
      dassProgress.textContent = answered + ' / 21 dijawab';
      dassProgress.className = 'badge ' + (answered === 21 ? 'bg-label-success' : 'bg-label-primary');

      // Hapus highlight error pada item yang sudah dijawab
      const wrap = document.getElementById('row_dass_' + radio.dataset.item);
      if (wrap) wrap.style.outline = '';
    });
  });

  // ── Safety Q4 visibility ───────────────────────────────────────────────
  document.querySelectorAll('[name="pikiran_mengakhiri_hidup"]').forEach(r => {
    r.addEventListener('change', () => {
      const show = r.value === 'ya' || r.value === 'tidak_mau_menjawab';
      document.getElementById('q4Wrap').style.display = show ? 'block' : 'none';
      document.getElementById('pgDefault').disabled = show;
    });
  });

  // Tampilkan detail self-harm jika ya
  document.querySelectorAll('[name="pernah_selfharm"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('selfharmDetail').style.display =
        (r.value === 'ya' || r.value === 'tidak_mau_menjawab') ? 'block' : 'none';
    });
  });

  // ── Submit guard — validasi semua step ────────────────────────────────
  document.getElementById('formJanji').addEventListener('submit', function (e) {
    for (let s = 1; s <= TOTAL; s++) {
      if (!validateStep(s)) {
        e.preventDefault();
        // Navigasi ke step yang gagal tanpa melewati validasi
        document.getElementById('step' + currentStep).classList.remove('active');
        currentStep = s;
        document.getElementById('step' + currentStep).classList.add('active');
        updateIndicator();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
    }
    document.getElementById('btnKirim').disabled = true;
    document.getElementById('btnKirim').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
  });

})();
</script>
<?= $this->endSection() ?>
