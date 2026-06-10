<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Detail Janji #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Models\KonselorModel;

$janji               = $janji ?? [];
$konselorList        = $konselorList ?? [];
$konselorNama        = $konselorNama ?? null;
$konselorPilihanList = $konselorPilihanList ?? [];
$dass                = $dass ?? null;
$safety              = $safety ?? null;
$hasil               = $hasil ?? null;

$statusMeta = [
    'menunggu'     => ['label' => 'Menunggu',      'color' => 'warning'],
    'dikonfirmasi' => ['label' => 'Dikonfirmasi',  'color' => 'info'],
    'terjadwal'    => ['label' => 'Terjadwal',     'color' => 'primary'],
    'berlangsung'  => ['label' => 'Berlangsung',   'color' => 'success'],
    'selesai'      => ['label' => 'Selesai',       'color' => 'dark'],
    'dibatalkan'   => ['label' => 'Dibatalkan',    'color' => 'danger'],
];
$sm = $statusMeta[$janji['status']] ?? ['label' => $janji['status'], 'color' => 'secondary'];
?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
  <a href="<?= base_url('admin/janji') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>Kelola Janji
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;">
    #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?>
  </span>
  <span class="badge bg-label-<?= $sm['color'] ?> ms-1"><?= $sm['label'] ?></span>
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

<!-- Safety Alert -->
<?php if ($safety && ($safety['pikiran_mengakhiri_hidup'] === 'ya' || $safety['pernah_selfharm'] === 'ya')): ?>
  <div class="alert alert-danger d-flex gap-3 align-items-start mb-4" role="alert">
    <i class="ti tabler-alert-triangle" style="font-size:1.5rem;flex-shrink:0;margin-top:.1rem;"></i>
    <div>
      <div class="fw-bold mb-1">Safety Flag Aktif</div>
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
  <!-- Kolom Kiri: Info Mahasiswa + Keluhan -->
  <div class="col-lg-7">

    <!-- Info Mahasiswa -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-user me-2 text-primary"></i>Identitas Mahasiswa</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Nama</div>
            <div class="fw-semibold"><?= esc($janji['name'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">NIM/NIP</div>
            <div><?= esc($janji['nim_nip'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Email</div>
            <div><?= esc($janji['email'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">No. HP</div>
            <div><?= esc($janji['phone'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Fakultas / Prodi</div>
            <div><?= esc($janji['fakultas'] ?? '—') ?> / <?= esc($janji['prodi'] ?? '—') ?></div>
          </div>
          <div class="col-sm-3">
            <div class="text-muted" style="font-size:.75rem;">Usia / Semester</div>
            <div><?= esc($janji['usia']) ?> th / Smt <?= esc($janji['semester']) ?></div>
          </div>
          <div class="col-sm-3">
            <div class="text-muted" style="font-size:.75rem;">Agama</div>
            <div><?= esc($janji['agama']) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Jenis Kelamin</div>
            <div class="text-capitalize"><?= esc(str_replace('-', ' ', $janji['jenis_kelamin'])) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Metode</div>
            <div class="text-capitalize"><?= esc($janji['metode']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Keluhan -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-notes me-2 text-warning"></i>Keluhan & Kondisi</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php if (! empty($janji['tema_konseling'])): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Tema Konseling</div>
            <span class="badge bg-label-primary mt-1"><?= esc(ucwords(str_replace('_', ' ', $janji['tema_konseling']))) ?></span>
          </div>
          <?php endif ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Keluhan Utama</div>
            <div class="mt-1" style="font-size:.875rem;white-space:pre-wrap;"><?= esc($janji['keluhan_utama']) ?></div>
          </div>
          <?php if (! empty($janji['mulai_keluhan'])): ?>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Sejak Kapan</div>
            <div><?= esc($janji['mulai_keluhan']) ?></div>
          </div>
          <?php endif ?>
          <?php if (! empty($janji['upaya_dilakukan'])): ?>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Upaya Dilakukan</div>
            <div style="font-size:.875rem;"><?= esc($janji['upaya_dilakukan']) ?></div>
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
        $dassKategori = [
            'normal'       => ['label' => 'Normal',        'color' => 'success'],
            'ringan'       => ['label' => 'Ringan',        'color' => 'info'],
            'sedang'       => ['label' => 'Sedang',        'color' => 'warning'],
            'berat'        => ['label' => 'Berat',         'color' => 'danger'],
            'sangat_berat' => ['label' => 'Sangat Berat',  'color' => 'danger'],
        ];
        $dList = [
            ['label' => 'Depresi',  'skor' => $dass['skor_depresi'] ?? 0,  'kat' => $dass['kategori_depresi'] ?? 'normal'],
            ['label' => 'Ansietas', 'skor' => $dass['skor_anxiety'] ?? 0,  'kat' => $dass['kategori_anxiety'] ?? 'normal'],
            ['label' => 'Stres',    'skor' => $dass['skor_stress'] ?? 0,   'kat' => $dass['kategori_stress']  ?? 'normal'],
        ];
        ?>
        <div class="row g-3">
          <?php foreach ($dList as $d):
            $k = $dassKategori[$d['kat']] ?? ['label' => $d['kat'], 'color' => 'secondary'];
          ?>
          <div class="col-4 text-center">
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
          $safetyItems = [
              ['label' => 'Pernah self-harm',               'key' => 'pernah_selfharm',             'danger' => 'ya'],
              ['label' => 'Merasa aman saat ini',            'key' => 'merasa_aman',                 'danger' => 'tidak'],
              ['label' => 'Pikiran mengakhiri hidup',        'key' => 'pikiran_mengakhiri_hidup',    'danger' => 'ya'],
              ['label' => 'Pikiran yang mengganggu',         'key' => 'pikiran_mengganggu',          'danger' => 'ya'],
          ];
          foreach ($safetyItems as $si):
            $val     = $safety[$si['key']] ?? '—';
            $isDanger = $val === $si['danger'];
          ?>
          <div class="col-sm-6 d-flex justify-content-between align-items-center border-bottom pb-2">
            <span class="text-muted"><?= $si['label'] ?></span>
            <span class="fw-semibold text-<?= $isDanger ? 'danger' : 'success' ?>">
              <?= esc(ucfirst($val)) ?>
            </span>
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

    <!-- Hasil Konseling (baca-saja untuk admin) -->
    <?php if ($hasil): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-clipboard-check me-2 text-success"></i>Hasil Konseling</h6>
      </div>
      <div class="card-body" style="font-size:.875rem;">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Rujukan</div>
            <span class="badge bg-label-<?= $hasil['ada_rujukan'] ? 'danger' : 'success' ?>">
              <?= $hasil['ada_rujukan'] ? 'Ya, dirujuk' : 'Tidak dirujuk' ?>
            </span>
          </div>
          <div class="col-sm-6">
            <div class="text-muted" style="font-size:.75rem;">Sesi Lanjutan</div>
            <span class="badge bg-label-<?= $hasil['sesi_lanjutan'] ? 'warning' : 'secondary' ?>">
              <?= $hasil['sesi_lanjutan'] ? 'Diperlukan' : 'Tidak' ?>
            </span>
          </div>
          <?php if ($hasil['ada_rujukan'] && $hasil['instansi_rujukan']): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Instansi Rujukan</div>
            <div><?= esc($hasil['instansi_rujukan']) ?></div>
          </div>
          <?php endif ?>
          <?php if (! empty($hasil['catatan_sesi'])): ?>
          <div class="col-12">
            <div class="text-muted" style="font-size:.75rem;">Catatan Sesi</div>
            <div class="mt-1" style="white-space:pre-wrap;"><?= esc($hasil['catatan_sesi']) ?></div>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>
    <?php endif ?>

  </div>

  <!-- Kolom Kanan: Tindakan Admin -->
  <div class="col-lg-5">

    <!-- Jadwal & Konselor Saat Ini -->
    <?php if (! empty($janji['tanggal_konseling'])): ?>
    <div class="card shadow-sm mb-4 border-primary" style="border-left:4px solid #696cff!important;">
      <div class="card-body py-3">
        <div class="text-muted mb-2" style="font-size:.75rem;">JADWAL DITETAPKAN</div>
        <div class="fw-bold mb-1">
          <i class="ti tabler-calendar me-1"></i>
          <?= date('l, d F Y', strtotime($janji['tanggal_konseling'])) ?>
        </div>
        <?php if ($janji['jam_konseling']): ?>
          <div class="text-muted mb-1"><i class="ti tabler-clock me-1"></i><?= date('H:i', strtotime($janji['jam_konseling'])) ?> WIB</div>
        <?php endif ?>
        <?php if (! empty($janji['lokasi_link'])): ?>
          <div class="text-muted"><i class="ti tabler-map-pin me-1"></i><?= esc($janji['lokasi_link']) ?></div>
        <?php endif ?>
        <?php if ($konselorNama): ?>
          <hr class="my-2">
          <div class="text-muted" style="font-size:.75rem;">Konselor</div>
          <div class="fw-semibold"><?= esc($konselorNama) ?></div>
        <?php endif ?>
        <?php if (! empty($janji['mahasiswa_konfirmasi_at'])): ?>
          <div class="mt-2 text-success" style="font-size:.78rem;">
            <i class="ti tabler-circle-check me-1"></i>
            Dikonfirmasi mahasiswa: <?= date('d M Y H:i', strtotime($janji['mahasiswa_konfirmasi_at'])) ?>
          </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Konselor Pilihan Mahasiswa -->
    <?php if (! empty($konselorPilihanList)): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:.85rem;"><i class="ti tabler-heart-handshake me-2 text-info"></i>Konselor Pilihan Mahasiswa</h6>
      </div>
      <div class="card-body py-2">
        <?php foreach ($konselorPilihanList as $kNama): ?>
          <div class="d-flex align-items-center gap-2 py-1">
            <i class="ti tabler-user-check text-info"></i>
            <span style="font-size:.875rem;"><?= esc($kNama) ?></span>
          </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>

    <!-- Form Tetapkan Jadwal -->
    <?php if (in_array($janji['status'], ['menunggu', 'dikonfirmasi'])): ?>
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-calendar-plus me-2 text-primary"></i>
          <?= $janji['status'] === 'dikonfirmasi' ? 'Ubah Jadwal' : 'Tetapkan Jadwal & Konselor' ?>
        </h6>
      </div>
      <div class="card-body">
        <form action="<?= base_url('admin/janji/proses/' . $janji['id']) ?>" method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Konselor <span class="text-danger">*</span></label>
            <select name="konselor_id" class="form-select form-select-sm" required>
              <option value="">— Pilih Konselor —</option>
              <?php foreach ($konselorList as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($janji['konselor_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                  <?= esc(KonselorModel::namaLengkap($k)) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Tanggal <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_konseling" class="form-control form-control-sm"
                     value="<?= esc($janji['tanggal_konseling'] ?? '') ?>" required
                     min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-5">
              <label class="form-label fw-semibold" style="font-size:.82rem;">Jam <span class="text-danger">*</span></label>
              <input type="time" name="jam_konseling" class="form-control form-control-sm"
                     value="<?= esc($janji['jam_konseling'] ?? '') ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Lokasi / Link Meeting</label>
            <input type="text" name="lokasi_link" class="form-control form-control-sm"
                   placeholder="cth: Ruang BK Lt. 2 / https://meet.google.com/..."
                   value="<?= esc($janji['lokasi_link'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Catatan untuk Mahasiswa</label>
            <textarea name="catatan_admin" class="form-control form-control-sm" rows="2"
                      placeholder="Informasi tambahan..."><?= esc($janji['catatan_admin'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="ti tabler-calendar-check me-1"></i>Tetapkan Jadwal
          </button>
        </form>
      </div>
    </div>
    <?php endif ?>

    <!-- Aksi Status -->
    <div class="card shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="mb-0 fw-semibold"><i class="ti tabler-settings me-2"></i>Aksi</h6>
      </div>
      <div class="card-body d-flex flex-column gap-2">

        <?php if ($janji['status'] === 'terjadwal'): ?>
          <form action="<?= base_url('admin/janji/mulai/' . $janji['id']) ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success btn-sm w-100">
              <i class="ti tabler-player-play me-1"></i>Tandai Berlangsung
            </button>
          </form>
        <?php endif ?>

        <?php if (! in_array($janji['status'], ['selesai', 'dibatalkan'])): ?>
          <form action="<?= base_url('admin/janji/batal/' . $janji['id']) ?>" method="post"
                onsubmit="return confirm('Batalkan janji ini?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
              <i class="ti tabler-ban me-1"></i>Batalkan Janji
            </button>
          </form>
        <?php endif ?>

        <a href="<?= base_url('admin/janji') ?>" class="btn btn-outline-secondary btn-sm">
          <i class="ti tabler-arrow-left me-1"></i>Kembali ke Daftar
        </a>
      </div>
    </div>

  </div>
</div>

<?= $this->endSection() ?>
