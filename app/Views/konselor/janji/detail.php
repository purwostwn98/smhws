<?= $this->extend('layouts/dashboard') ?>
<?php
$janji           = $janji ?? [];
$safety          = $safety ?? null;
$dass            = $dass ?? null;
$hasil           = $hasil ?? null;
$instansiRujukan = $instansiRujukan ?? []; ?>
<?= $this->section('title') ?>Detail Sesi #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/quill/typography.css') ?>" />
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/quill/editor.css') ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$statusMeta = [
  'dikonfirmasi' => ['label' => 'Dikonfirmasi', 'color' => 'info'],
  'terjadwal'    => ['label' => 'Terjadwal',    'color' => 'primary'],
  'berlangsung'  => ['label' => 'Berlangsung',  'color' => 'success'],
  'selesai'      => ['label' => 'Selesai',      'color' => 'dark'],
  'dibatalkan'   => ['label' => 'Dibatalkan',   'color' => 'danger'],
];
$sm = $statusMeta[$janji['status']] ?? ['label' => $janji['status'], 'color' => 'secondary'];
?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('konselor/janji') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Sesi Saya
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;">#<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?></span>
  <span class="badge bg-label-<?= $sm['color'] ?> ms-1"><?= $sm['label'] ?></span>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible mb-4">
    <i class="ti tabler-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger alert-dismissible mb-4">
    <i class="ti tabler-alert-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<!-- Safety Alert -->
<?php if ($safety && ($safety['pikiran_mengakhiri_hidup'] === 'ya' || $safety['pernah_selfharm'] === 'ya')): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start mb-4">
    <i class="ti tabler-alert-triangle" style="font-size:1.5rem;flex-shrink:0;"></i>
    <div>
      <div class="fw-bold mb-1">Perhatian: Safety Flag Aktif</div>
      <ul class="mb-0" style="font-size:.875rem;">
        <?php if ($safety['pikiran_mengakhiri_hidup'] === 'ya'): ?>
          <li>Mahasiswa memiliki pikiran mengakhiri hidup</li>
        <?php endif ?>
        <?php if ($safety['pernah_selfharm'] === 'ya'): ?>
          <li>Mahasiswa pernah melakukan self-harm</li>
        <?php endif ?>
      </ul>
    </div>
  </div>
<?php endif ?>

<div class="row g-4">
  <div class="col-12">

    <!-- Jadwal Sesi -->
    <div class="card shadow-sm mb-4 border-primary" style="border-left:4px solid #696cff!important;">
      <div class="card-body py-3">
        <div class="text-muted mb-1" style="font-size:.75rem;">JADWAL SESI</div>
        <div class="fw-bold mb-1">
          <i class="ti tabler-calendar me-1"></i>
          <?= $janji['tanggal_konseling'] ? date('l, d F Y', strtotime($janji['tanggal_konseling'])) : 'Belum ditetapkan' ?>
        </div>
        <?php if ($janji['jam_konseling']): ?>
          <div class="text-muted mb-1"><i class="ti tabler-clock me-1"></i><?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB</div>
        <?php endif ?>
        <?php if (! empty($janji['lokasi_link'])): ?>
          <div class="text-muted"><i class="ti tabler-map-pin me-1"></i>
            <?php if (str_starts_with($janji['lokasi_link'], 'http')): ?>
              <a href="<?= esc($janji['lokasi_link']) ?>" target="_blank" rel="noopener"><?= esc($janji['lokasi_link']) ?></a>
            <?php else: ?>
              <?= esc($janji['lokasi_link']) ?>
            <?php endif ?>
          </div>
        <?php endif ?>
        <?php if (! empty($janji['catatan_admin'])): ?>
          <div class="mt-2 p-3 rounded-2" style="background:#1a5f7a;font-size:.82rem;">
            <div class="d-flex align-items-center gap-1 mb-1" style="color:#7fd6f0;font-size:.73rem;font-weight:600;letter-spacing:.03em;">
              <i class="ti tabler-message-2" style="font-size:.85rem;"></i>CATATAN ADMIN
            </div>
            <div style="color:#fff;line-height:1.6;"><?= nl2br(esc($janji['catatan_admin'])) ?></div>
          </div>
        <?php endif ?>
      </div>
    </div>

    <!-- Info Mahasiswa -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-user me-2 text-primary"></i>Identitas Mahasiswa</h6>
      </div>
      <div class="card-body">
        <div class="row g-3" style="font-size:.875rem;">
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Nama</div>
            <div class="fw-semibold"><?= esc($janji['name'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">NIM</div>
            <div><?= esc($janji['nim_nip'] ?? '—') ?></div>
          </div>
          <div class="col-sm-4">
            <div class="text-muted" style="font-size:.75rem;">Usia / Semester</div>
            <div><?= esc($janji['usia']) ?> th / Smt <?= esc($janji['semester']) ?></div>
          </div>
          <div class="col-sm-4">
            <div class="text-muted" style="font-size:.75rem;">Agama</div>
            <div><?= esc($janji['agama']) ?></div>
          </div>
          <div class="col-sm-4">
            <div class="text-muted" style="font-size:.75rem;">Jenis Kelamin</div>
            <div class="text-capitalize"><?= esc(str_replace('-', ' ', $janji['jenis_kelamin'])) ?></div>
          </div>
          <?php if (! empty($janji['fakultas'])): ?>
            <div class="col-12">
              <div class="text-muted" style="font-size:.75rem;">Fakultas / Prodi</div>
              <div><?= esc($janji['fakultas']) ?> / <?= esc($janji['prodi'] ?? '—') ?></div>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>

    <!-- Keluhan -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-notes me-2 text-warning"></i>Keluhan & Kondisi</h6>
      </div>
      <div class="card-body">
        <div class="row g-3" style="font-size:.875rem;">
          <?php if (! empty($janji['tema_konseling'])): ?>
            <div class="col-12">
              <div class="text-muted" style="font-size:.75rem;">Tema</div>
              <span class="badge bg-label-primary mt-1"><?= esc(ucwords(str_replace('_', ' ', $janji['tema_konseling']))) ?></span>
            </div>
          <?php endif ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Keluhan Utama</div>
            <div class="mt-1" style="white-space:pre-wrap;"><?= esc($janji['keluhan_utama']) ?></div>
          </div>
          <?php if (! empty($janji['urgensi'])):
            $urgensiMeta = [
              'biasa'        => ['label' => 'Biasa',        'bg' => 'bg-label-success', 'icon' => 'tabler-circle'],
              'cukup_urgen'  => ['label' => 'Cukup Urgen',  'bg' => 'bg-label-warning', 'icon' => 'tabler-alert-circle'],
              'sangat_urgen' => ['label' => 'Sangat Urgen', 'bg' => 'bg-label-danger',  'icon' => 'tabler-alert-triangle'],
            ];
            $urg = $urgensiMeta[$janji['urgensi']] ?? ['label' => $janji['urgensi'], 'bg' => 'bg-label-secondary', 'icon' => 'tabler-circle'];
          ?>
            <div class="col-12">
              <div class="text-muted" style="font-size:.75rem;">Tingkat Urgensi</div>
              <span class="badge <?= $urg['bg'] ?> d-inline-flex align-items-center gap-1 mt-1" style="font-size:.8rem;padding:.35em .7em;">
                <i class="ti <?= $urg['icon'] ?>"></i><?= $urg['label'] ?>
              </span>
            </div>
          <?php endif ?>
          <?php if (! empty($janji['mulai_keluhan'])): ?>
            <div class="col-sm-6">
              <div class="text-muted" style="font-size:.75rem;">Sejak Kapan</div>
              <div><?= esc($janji['mulai_keluhan']) ?></div>
            </div>
          <?php endif ?>
          <?php if (! empty($janji['upaya_dilakukan'])): ?>
            <div class="col-sm-6">
              <div class="text-muted" style="font-size:.75rem;">Upaya Dilakukan</div>
              <div><?= esc($janji['upaya_dilakukan']) ?></div>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>

    <!-- DASS -->
    <?php if ($dass): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="mb-0 fw-semibold"><i class="ti tabler-chart-bar me-2 text-danger"></i>Hasil DASS-21</h6>
        </div>
        <div class="card-body">
          <?php
          $dassKat = [
            'normal'       => ['label' => 'Normal',       'color' => 'success'],
            'ringan'       => ['label' => 'Ringan',       'color' => 'info'],
            'sedang'       => ['label' => 'Sedang',       'color' => 'warning'],
            'berat'        => ['label' => 'Berat',        'color' => 'danger'],
            'sangat_berat' => ['label' => 'Sangat Berat', 'color' => 'danger'],
          ];
          $dList = [
            ['label' => 'Depresi',  'skor' => $dass['skor_depresi'] ?? 0, 'kat' => $dass['kategori_depresi'] ?? 'normal'],
            ['label' => 'Ansietas', 'skor' => $dass['skor_anxiety'] ?? 0, 'kat' => $dass['kategori_anxiety'] ?? 'normal'],
            ['label' => 'Stres',    'skor' => $dass['skor_stress']  ?? 0, 'kat' => $dass['kategori_stress']  ?? 'normal'],
          ];
          ?>
          <div class="row g-3">
            <?php foreach ($dList as $d): $k = $dassKat[$d['kat']] ?? ['label' => $d['kat'], 'color' => 'secondary']; ?>
              <div class="col-4 col-md-2 text-center">
                <div class="text-muted mb-1" style="font-size:.75rem;"><?= $d['label'] ?></div>
                <div class="fw-bold fs-4"><?= $d['skor'] ?></div>
                <span class="badge bg-label-<?= $k['color'] ?>" style="font-size:.7rem;"><?= $k['label'] ?></span>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </div>
    <?php endif ?>

    <!-- Safety Screening -->
    <?php if ($safety): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="mb-0 fw-semibold"><i class="ti tabler-shield-check me-2 text-info"></i>Safety Screening</h6>
        </div>
        <div class="card-body">
          <div class="row g-2" style="font-size:.875rem;">
            <?php
            $si = [
              ['label' => 'Pernah self-harm',         'key' => 'pernah_selfharm',           'danger' => 'ya'],
              ['label' => 'Merasa aman saat ini',      'key' => 'merasa_aman',               'danger' => 'tidak'],
              ['label' => 'Pikiran mengakhiri hidup',  'key' => 'pikiran_mengakhiri_hidup',  'danger' => 'ya'],
              ['label' => 'Pikiran yang mengganggu',   'key' => 'pikiran_mengganggu',        'danger' => 'ya'],
            ];
            foreach ($si as $item):
              $val     = $safety[$item['key']] ?? '—';
              $isDanger = $val === $item['danger'];
            ?>
              <div class="col-sm-6 d-flex justify-content-between align-items-center border-bottom pb-2">
                <span class="text-muted"><?= $item['label'] ?></span>
                <span class="fw-semibold text-<?= $isDanger ? 'danger' : 'success' ?>"><?= esc(ucfirst($val)) ?></span>
              </div>
            <?php endforeach ?>
          </div>
          <?php if (! empty($safety['riwayat_selfharm_keterangan'])): ?>
            <div class="mt-3 p-2 bg-danger bg-opacity-10 rounded" style="font-size:.82rem;">
              <strong>Keterangan:</strong> <?= esc($safety['riwayat_selfharm_keterangan']) ?>
            </div>
          <?php endif ?>
        </div>
      </div>
    <?php endif ?>

  </div>

  <!-- Aksi & Hasil -->
  <div class="col-12">

    <?php if ($janji['status'] === 'terjadwal'): ?>
      <!-- Tombol Mulai Sesi -->
      <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-4">
          <i class="ti tabler-player-play" style="font-size:2.5rem;color:#696cff;display:block;margin-bottom:.5rem;"></i>
          <div class="fw-semibold mb-2">Sesi siap dimulai</div>
          <p class="text-muted mb-3" style="font-size:.875rem;">
            Mahasiswa telah mengkonfirmasi kehadiran. Klik tombol di bawah saat sesi dimulai.
          </p>
          <form action="<?= base_url('konselor/janji/mulai/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">
              <i class="ti tabler-player-play me-1"></i>Mulai Sesi
            </button>
          </form>
        </div>
      </div>
    <?php endif ?>

    <?php if ($hasil): ?>
      <!-- Tampilkan Hasil yang Sudah Ada -->
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="mb-0 fw-semibold"><i class="ti tabler-clipboard-check me-2 text-success"></i>Hasil Konseling</h6>
        </div>
        <div class="card-body" style="font-size:.875rem;">
          <div class="row g-3">
            <div class="col-6">
              <div class="text-muted" style="font-size:.75rem;">Rujukan</div>
              <span class="badge bg-label-<?= $hasil['ada_rujukan'] ? 'danger' : 'success' ?>">
                <?= $hasil['ada_rujukan'] ? 'Dirujuk' : 'Tidak dirujuk' ?>
              </span>
            </div>
            <div class="col-6">
              <div class="text-muted" style="font-size:.75rem;">Sesi Lanjutan</div>
              <span class="badge bg-label-<?= $hasil['sesi_lanjutan'] ? 'warning' : 'secondary' ?>">
                <?= $hasil['sesi_lanjutan'] ? 'Diperlukan' : 'Tidak' ?>
              </span>
            </div>
            <?php if ($hasil['ada_rujukan']): ?>
              <div class="col-12">
                <div class="text-muted" style="font-size:.75rem;">Instansi Rujukan</div>
                <?php
                $namaInstansi = '—';
                if (! empty($hasil['instansi_rujukan_id'])) {
                  foreach ($instansiRujukan as $inst) {
                    if ($inst['id'] == $hasil['instansi_rujukan_id']) {
                      $namaInstansi = $inst['singkatan'] . ' — ' . $inst['nama_instansi'];
                      break;
                    }
                  }
                } elseif (! empty($hasil['instansi_rujukan'])) {
                  $namaInstansi = $hasil['instansi_rujukan'];
                }
                ?>
                <div><?= esc($namaInstansi) ?></div>
              </div>
            <?php endif ?>
            <?php if (! empty($hasil['catatan_sesi'])): ?>
              <div class="col-12">
                <div class="text-muted" style="font-size:.75rem;">Laporan Konseling</div>
                <div class="mt-1" style="white-space:pre-wrap;"><?= esc($hasil['catatan_sesi']) ?></div>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    <?php endif ?>

    <a href="<?= base_url('konselor/janji') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="ti tabler-arrow-left me-1"></i>Kembali ke Daftar
    </a>
  </div>

</div>

<?php if ($janji['status'] === 'berlangsung' && ! $hasil): ?>
  <!-- Form Isi Hasil Konseling — full width -->
  <div class="row mt-2">
    <div class="col-12">
      <div class="card" style="border:2px solid #28c76f;border-radius:.75rem;box-shadow:0 4px 20px rgba(40,199,111,.15);">
        <div class="card-header py-3" style="background:linear-gradient(135deg,#28c76f 0%,#1a9e5a 100%);border-radius:.6rem .6rem 0 0;">
          <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
            <i class="ti tabler-clipboard-check" style="font-size:1.1rem;"></i>Isi Hasil Konseling
          </h6>
          <div class="text-white mt-1" style="font-size:.75rem;opacity:.85;">Lengkapi hasil sesi untuk menyelesaikan konseling</div>
        </div>
        <div class="card-body pt-4">
          <form action="<?= base_url('konselor/janji/hasil/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="row g-4">

              <div class="col-12">
                <label class="form-label fw-semibold">Laporan Konseling</label>

                <!-- Panduan SOAP -->
                <div class="mb-3 p-3 rounded-2" style="background:#f8f9fa;border-left:3px solid #1a5f7a;font-size:.8rem;">
                  <div class="fw-semibold mb-2" style="color:#1a5f7a;font-size:.78rem;letter-spacing:.04em;">PANDUAN FORMAT SOAP</div>
                  <div class="row g-2">
                    <div class="col-sm-6">
                      <div class="d-flex gap-2">
                        <span class="fw-bold" style="color:#696cff;min-width:1.2rem;">S</span>
                        <div><span class="fw-semibold">Subjective</span> — Keluhan & cerita klien saat sesi, kondisi emosional yang dilaporkan sendiri.</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="d-flex gap-2">
                        <span class="fw-bold" style="color:#28c76f;min-width:1.2rem;">O</span>
                        <div><span class="fw-semibold">Objective</span> — Observasi konselor: afek, perilaku, penampilan, respons selama sesi.</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="d-flex gap-2">
                        <span class="fw-bold" style="color:#f0a500;min-width:1.2rem;">A</span>
                        <div><span class="fw-semibold">Assessment</span> — Penilaian klinis konselor terhadap kondisi & perkembangan klien.</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="d-flex gap-2">
                        <span class="fw-bold" style="color:#dc3545;min-width:1.2rem;">P</span>
                        <div><span class="fw-semibold">Plan</span> — Rencana tindak lanjut, intervensi, rekomendasi, atau sesi lanjutan.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Quill toolbar -->
                <div id="quillToolbar">
                  <span class="ql-formats">
                    <select class="ql-header">
                      <option value="1">Heading 1</option>
                      <option value="2">Heading 2</option>
                      <option value="">Normal</option>
                    </select>
                  </span>
                  <span class="ql-formats">
                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>
                  </span>
                  <span class="ql-formats">
                    <button class="ql-list" value="ordered"></button>
                    <button class="ql-list" value="bullet"></button>
                  </span>
                  <span class="ql-formats">
                    <button class="ql-blockquote"></button>
                    <button class="ql-clean"></button>
                  </span>
                </div>
                <!-- Quill editor container -->
                <div id="quillEditor" style="min-height:260px;"></div>
                <!-- Hidden input untuk submit -->
                <input type="hidden" name="catatan_sesi" id="catatanSesiInput" />
              </div>

              <div class="col-md-6">
                <div class="p-3 border rounded-2 h-100">
                  <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="adaRujukan" name="ada_rujukan"
                      onchange="toggleRujukan(this.checked)">
                    <label class="form-check-label fw-semibold" for="adaRujukan">
                      Mahasiswa perlu dirujuk
                    </label>
                  </div>
                  <div id="rujukanDetail" style="display:none;" class="mt-3">
                    <div class="mb-2">
                      <label class="form-label fw-semibold" style="font-size:.82rem;">Instansi Rujukan</label>
                      <select name="instansi_rujukan_id" id="selectInstansi" class="form-select form-select-sm">
                        <option value="">— Pilih instansi rujukan —</option>
                        <?php foreach ($instansiRujukan as $inst): ?>
                          <option value="<?= esc($inst['id']) ?>">
                            <?= esc($inst['singkatan']) ?> — <?= esc($inst['nama_instansi']) ?>
                          </option>
                        <?php endforeach ?>
                        <option value="lainnya">Lainnya (isi manual)</option>
                      </select>
                      <input type="text" name="instansi_rujukan" id="instansiLainnya"
                        class="form-control form-control-sm mt-2" style="display:none;"
                        placeholder="Tuliskan nama instansi rujukan...">
                    </div>
                    <div>
                      <label class="form-label fw-semibold" style="font-size:.82rem;">Alasan Rujukan</label>
                      <textarea name="alasan_rujukan" class="form-control form-control-sm" rows="2"
                        placeholder="Jelaskan alasan perlu dirujuk..."></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="p-3 border rounded-2 h-100 d-flex align-items-start">
                  <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="sesiLanjutan" name="sesi_lanjutan">
                    <label class="form-check-label fw-semibold" for="sesiLanjutan">
                      Diperlukan sesi lanjutan
                    </label>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-success px-5" style="font-size:.95rem;padding:.65rem 2rem;">
                  <i class="ti tabler-circle-check me-1"></i>Simpan Hasil & Selesaikan Sesi
                </button>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="<?= base_url('assets/vendor/libs/quill/quill.js') ?>"></script>
<script>
  function toggleRujukan(show) {
    document.getElementById('rujukanDetail').style.display = show ? 'block' : 'none';
  }

  document.addEventListener('DOMContentLoaded', function() {
    const selectInstansi = document.getElementById('selectInstansi');
    const inputLainnya = document.getElementById('instansiLainnya');
    if (selectInstansi) {
      selectInstansi.addEventListener('change', function() {
        inputLainnya.style.display = this.value === 'lainnya' ? 'block' : 'none';
        inputLainnya.required = this.value === 'lainnya';
      });
    }
  });

  <?php if ($janji['status'] === 'berlangsung' && ! $hasil): ?>
      (function() {
        const quill = new Quill('#quillEditor', {
          modules: {
            toolbar: '#quillToolbar'
          },
          placeholder: 'S (Subjective):\nKlien melaporkan...\n\nO (Objective):\nKlien tampak...\n\nA (Assessment):\nBerdasarkan sesi, klien...\n\nP (Plan):\nRencana tindak lanjut...',
          theme: 'snow'
        });

        document.querySelector('form[action*="hasil"]').addEventListener('submit', function() {
          document.getElementById('catatanSesiInput').value = quill.root.innerHTML;
        });
      })();
  <?php endif ?>
</script>
<?= $this->endSection() ?>