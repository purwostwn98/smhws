<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Dashboard Prodi<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// ── Persentase helper ─────────────────────────────────────────────────────────
$pct = static fn(int $n, int $total): float => $total > 0 ? round($n / $total * 100, 1) : 0.0;

// ── Summary cards ─────────────────────────────────────────────────────────────
$totalSesi    = $stats['total'];
$selesaiJanji = $stats['status']['selesai'] ?? 0;
$pctSelesai   = $pct($selesaiJanji, $totalSesi);

// ── Tren: pastikan 12 bulan terakhir semua terisi ────────────────────────────
$trenFull = [];
for ($i = 11; $i >= 0; $i--) {
    $trenFull[date('Y-m', strtotime("-{$i} months"))] = 0;
}
foreach ($stats['tren'] as $t) {
    if (isset($trenFull[$t['bulan']])) $trenFull[$t['bulan']] = (int) $t['jumlah'];
}
$bulanId     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
$trenLabels  = [];
$trenValues  = [];
foreach ($trenFull as $ym => $n) {
    [$y, $m] = explode('-', $ym);
    $trenLabels[] = $bulanId[(int)$m - 1] . ' \'' . substr($y, 2);
    $trenValues[] = $n;
}

// ── Jenis Kelamin chart data ──────────────────────────────────────────────────
$jkLabels       = ['Laki-laki', 'Perempuan'];
$jkMhsLaki      = $stats['jk_mahasiswa']['laki-laki'] ?? 0;
$jkMhsPerempuan = $stats['jk_mahasiswa']['perempuan'] ?? 0;
$jkMhsTotal     = $jkMhsLaki + $jkMhsPerempuan;
$jkValues       = [$jkMhsLaki, $jkMhsPerempuan];
$jkColors       = ['#0d6efd', '#e83e8c'];

// ── Semester chart data ───────────────────────────────────────────────────────
$smtLabels          = $stats['semester_groups'];
$smtValuesSesi      = array_values($stats['semester']);
$smtValuesMahasiswa = array_values($stats['semester_mahasiswa'] ?? array_fill(0, count($smtLabels), 0));

// ── Stressor chart data ───────────────────────────────────────────────────────
$stressorLabels = array_values($stats['stressor_labels']);
$stressorValues = array_values($stats['stressor']);
$stressorColors = ['#1a5f7a','#0d6efd','#f0a500','#20c997','#e83e8c','#fd7e14','#6f42c1','#28a745'];
$totalHk        = $stats['total_hk'];

// ── Masalah Utama chart data (sorted desc, from controller) ──────────────────
$masalahLabels = array_keys($stats['masalah']);
$masalahValues = array_values($stats['masalah']);

// ── Status Konseling chart data ───────────────────────────────────────────────
$statusKonsLabelsChart = array_values($stats['status_kons_labels']);
$statusKonsValues      = array_values($stats['status_kons']);
$statusKonsColors      = ['#28a745','#1a5f7a','#0d6efd','#dc3545','#6c757d'];

// ── Hasil Konseling chart data ────────────────────────────────────────────────
$hasilLabels = array_keys($stats['hasil']);
$hasilValues = array_values($stats['hasil']);
$hasilColors = ['#28a745','#20c997','#f0a500','#fd7e14','#dc3545'];
?>

<!-- ── Header ────────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">Dashboard Prodi</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;"><?= esc($prodi) ?></p>
  </div>
  <span class="badge rounded-pill px-3 py-2" style="background:rgba(26,95,122,.1);color:#1a5f7a;font-size:.8rem;">
    <i class="ti tabler-shield-lock me-1"></i>Data anonim — tanpa identitas pasien
  </span>
</div>

<!-- ── Keterangan Sesi vs Mahasiswa ───────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-2 mb-3 px-3 py-2 rounded" style="background:rgba(13,110,253,.06);border-left:3px solid rgba(13,110,253,.3);font-size:.8rem;color:#444;">
  <i class="ti tabler-info-circle" style="color:#0d6efd;flex-shrink:0;font-size:1rem;margin-top:1px;"></i>
  <span>
    <strong>Sesi</strong> — total kunjungan konseling yang tercatat; satu mahasiswa dapat memiliki lebih dari satu sesi.&ensp;
    <strong>Mahasiswa</strong> — jumlah mahasiswa unik yang pernah melakukan konseling (tanpa duplikasi).
  </span>
</div>

<!-- ── Filter Bar ────────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" id="filterForm" class="row g-2 align-items-end">
      <div class="col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1" style="color:#666;">Tahun Akademik</label>
        <select name="tahun_akd" class="form-select form-select-sm filter-auto" style="font-size:.8rem;">
          <option value="">Semua Tahun</option>
          <?php foreach ($tahunAkdOptions as $opt): ?>
            <option value="<?= $opt ?>" <?= ($filters['tahun_akd'] === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1" style="color:#666;">Semester Akademik</label>
        <select name="smt_akd" class="form-select form-select-sm filter-auto" style="font-size:.8rem;">
          <option value="" <?= empty($filters['smt_akd']) ? 'selected' : '' ?>>Semua Semester</option>
          <option value="ganjil" <?= ($filters['smt_akd'] === 'ganjil') ? 'selected' : '' ?>>Ganjil (Jul–Des)</option>
          <option value="genap"  <?= ($filters['smt_akd'] === 'genap')  ? 'selected' : '' ?>>Genap (Jan–Jun)</option>
        </select>
      </div>
      <div class="col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1" style="color:#666;">Jenis Kelamin</label>
        <select name="jk" class="form-select form-select-sm filter-auto" style="font-size:.8rem;">
          <option value="" <?= empty($filters['jk']) ? 'selected' : '' ?>>Semua</option>
          <option value="laki-laki"  <?= ($filters['jk'] === 'laki-laki')  ? 'selected' : '' ?>>Laki-laki</option>
          <option value="perempuan"  <?= ($filters['jk'] === 'perempuan')  ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1" style="color:#666;">Dari Tanggal</label>
        <input type="date" name="tgl_mulai" value="<?= esc($filters['tgl_mulai']) ?>"
               class="form-control form-control-sm filter-auto" style="font-size:.8rem;">
      </div>
      <div class="col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1" style="color:#666;">Sampai Tanggal</label>
        <input type="date" name="tgl_selesai" value="<?= esc($filters['tgl_selesai']) ?>"
               class="form-control form-control-sm filter-auto" style="font-size:.8rem;">
      </div>
      <div class="col-sm-6 col-md-3 col-xl-2 d-flex gap-2 flex-wrap align-self-end">
        <button type="submit" class="btn btn-sm btn-primary flex-grow-1" style="font-size:.8rem;">
          <i class="ti tabler-filter me-1"></i>Filter
        </button>
        <a href="<?= base_url('dosen/dashboard-prodi') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter" style="font-size:.8rem;">
          <i class="ti tabler-refresh"></i>
        </a>
        <a href="#" target="_blank" class="btn btn-sm btn-outline-danger" title="Export PDF" style="font-size:.8rem;"
           onclick="this.href='<?= base_url('dosen/dashboard-prodi/pdf') ?>?'+new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString()">
          <i class="ti tabler-file-type-pdf me-1"></i>PDF
        </a>
      </div>
    </form>
  </div>
</div>

<!-- ── Summary Cards ─────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Total Sesi','value'=>$totalSesi,'icon'=>'tabler-calendar-stats','color'=>'#1a5f7a','bg'=>'rgba(26,95,122,.12)'],
    ['label'=>'Mahasiswa Konseling','value'=>$stats['mahasiswa_unik'],'icon'=>'tabler-users','color'=>'#0d6efd','bg'=>'rgba(13,110,253,.12)'],
    ['label'=>'Sesi Selesai','value'=>$selesaiJanji,'icon'=>'tabler-circle-check','color'=>'#28a745','bg'=>'rgba(40,167,69,.12)'],
    ['label'=>'Follow-up','value'=>$stats['total_followup'],'icon'=>'tabler-repeat','color'=>'#f0a500','bg'=>'rgba(240,165,0,.12)'],
    ['label'=>'Dirujuk','value'=>$stats['total_dirujuk'],'icon'=>'tabler-external-link','color'=>'#dc3545','bg'=>'rgba(220,53,69,.12)'],
  ];
  ?>
  <?php foreach ($cards as $card): ?>
  <div class="col-sm-6 col-md-4 col-xl">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:<?= $card['bg'] ?>;color:<?= $card['color'] ?>;font-size:1.2rem;">
            <i class="ti <?= $card['icon'] ?>"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;line-height:1;"><?= $card['value'] ?></div>
          <div class="text-muted" style="font-size:.75rem;"><?= $card['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach ?>
  <div class="col-sm-6 col-md-4 col-xl">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(32,201,151,.12);color:#20c997;font-size:1.2rem;">
            <i class="ti tabler-percentage"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;line-height:1;"><?= $pctSelesai ?>%</div>
          <div class="text-muted" style="font-size:.75rem;">Tingkat Penyelesaian</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Tren Bulanan ──────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
    <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
      <i class="ti tabler-trending-up me-1 text-primary"></i>Tren Konseling 12 Bulan Terakhir
    </h6>
  </div>
  <div class="card-body px-4 pb-4">
    <canvas id="chartTren" height="80"></canvas>
  </div>
</div>

<!-- ── Semester + Jenis Kelamin ─────────────────────────────────────────────── -->
<div class="row g-4 mb-4">

  <!-- Semester -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
        <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
          <i class="ti tabler-school me-1 text-primary"></i>Distribusi Semester Mahasiswa
        </h6>
      </div>
      <div class="card-body px-4">
        <div class="row g-3">
          <div class="col-md-12">
            <canvas id="chartSmt" height="160"></canvas>
          </div>
          <div class="col-md-12 mt-2">
            <div class="table-responsive">
              <table class="table table-sm mb-0" style="font-size:.8rem;">
                <thead>
                  <tr style="color:#888;">
                    <th>Semester</th>
                    <th class="text-end">Mahasiswa</th>
                    <th class="text-end">%</th>
                    <th class="text-end">Sesi</th>
                    <th class="text-end">%</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($stats['semester_groups'] as $g): ?>
                  <?php $nMhs = $stats['semester_mahasiswa'][$g] ?? 0; $nSesi = $stats['semester'][$g] ?? 0; ?>
                  <tr>
                    <td><?= $g ?></td>
                    <td class="text-end fw-semibold"><?= $nMhs ?></td>
                    <td class="text-end text-muted"><?= $pct($nMhs, $stats['mahasiswa_unik']) ?>%</td>
                    <td class="text-end fw-semibold"><?= $nSesi ?></td>
                    <td class="text-end text-muted"><?= $pct($nSesi, $totalSesi) ?>%</td>
                  </tr>
                  <?php endforeach ?>
                  <tr class="table-light fw-bold">
                    <td>Total</td>
                    <td class="text-end"><?= $stats['mahasiswa_unik'] ?></td>
                    <td class="text-end">100%</td>
                    <td class="text-end"><?= $totalSesi ?></td>
                    <td class="text-end">100%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Jenis Kelamin -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
        <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
          <i class="ti tabler-gender-bigender me-1 text-primary"></i>Jenis Kelamin
        </h6>
      </div>
      <div class="card-body px-4 d-flex flex-column align-items-center">
        <canvas id="chartJk" width="200" height="200" style="max-width:200px;"></canvas>
        <div class="table-responsive w-100 mt-3">
          <table class="table table-sm mb-0" style="font-size:.8rem;">
            <thead><tr style="color:#888;"><th>Jenis Kelamin</th><th class="text-end">Mahasiswa</th><th class="text-end">%</th></tr></thead>
            <tbody>
              <?php foreach (['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan'] as $key => $label): ?>
              <?php $n = $stats['jk_mahasiswa'][$key] ?? 0; ?>
              <tr>
                <td><?= $label ?></td>
                <td class="text-end fw-semibold"><?= $n ?></td>
                <td class="text-end text-muted"><?= $pct($n, $jkMhsTotal) ?>%</td>
              </tr>
              <?php endforeach ?>
              <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= $jkMhsTotal ?></td><td class="text-end">100%</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ── Masalah Utama ─────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
    <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
      <i class="ti tabler-stethoscope me-1 text-primary"></i>Masalah Utama yang Dialami Klien
      <span class="ms-2 badge rounded-pill" style="background:rgba(232,62,140,.1);color:#e83e8c;font-size:.7rem;">DSM-5-TR</span>
    </h6>
    <p class="text-muted mb-0 mt-1" style="font-size:.75rem;">Berdasarkan sesi yang sudah memiliki laporan konseling (<?= $totalHk ?> sesi)</p>
  </div>
  <div class="card-body px-4">
    <div class="row g-3">
      <div class="col-md-7">
        <canvas id="chartMasalah" height="260"></canvas>
      </div>
      <div class="col-md-5">
        <div class="table-responsive">
          <table class="table table-sm mb-0" style="font-size:.78rem;">
            <thead><tr style="color:#888;"><th>Masalah</th><th class="text-end">Jml</th><th class="text-end">%</th></tr></thead>
            <tbody>
              <?php $total_masalah = 0; foreach ($stats['masalah'] as $lbl=>$n): $total_masalah += $n; ?>
              <tr>
                <td style="max-width:200px;white-space:normal;word-break:break-word;"><?= esc($lbl) ?></td>
                <td class="text-end fw-semibold"><?= $n ?></td>
                <td class="text-end text-muted"><?= $pct($n, $totalHk) ?>%</td>
              </tr>
              <?php endforeach ?>
              <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= $total_masalah ?></td><td class="text-end">—</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Stressor ──────────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
    <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
      <i class="ti tabler-flame me-1 text-primary"></i>Stressor Utama Pemicu Permasalahan
    </h6>
    <p class="text-muted mb-0 mt-1" style="font-size:.75rem;">Satu sesi dapat memiliki lebih dari satu stressor</p>
  </div>
  <div class="card-body px-4">
    <div class="row g-3">
      <div class="col-md-8">
        <canvas id="chartStressor" height="220"></canvas>
      </div>
      <div class="col-md-4">
        <div class="table-responsive">
          <table class="table table-sm mb-0" style="font-size:.8rem;">
            <thead><tr style="color:#888;"><th>Stressor</th><th class="text-end">Sesi</th><th class="text-end">%</th></tr></thead>
            <tbody>
              <?php
              $stressorKeys  = array_keys($stats['stressor_labels']);
              $stressorPairs = array_combine($stressorKeys, array_values($stats['stressor']));
              arsort($stressorPairs);
              foreach ($stressorPairs as $key => $n):
              ?>
              <tr>
                <td>
                  <a href="#" class="stressor-detail-link text-decoration-none text-dark"
                     data-key="<?= esc($key) ?>"
                     data-bs-toggle="modal" data-bs-target="#modalStressorDetail"
                     title="Lihat detail">
                    <?= esc($stats['stressor_labels'][$key]) ?>
                    <i class="ti tabler-info-circle ms-1" style="font-size:.75rem;color:#6c757d;vertical-align:middle;"></i>
                  </a>
                </td>
                <td class="text-end fw-semibold"><?= $n ?></td>
                <td class="text-end text-muted"><?= $pct($n, $totalHk) ?>%</td>
              </tr>
              <?php endforeach ?>
              <tr class="table-light fw-bold"><td>Total Sesi</td><td class="text-end"><?= $totalHk ?></td><td class="text-end">—</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Status Konseling + Hasil Konseling ────────────────────────────────────── -->
<div class="row g-4 mb-4">

  <!-- Status Konseling -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
        <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
          <i class="ti tabler-checklist me-1 text-primary"></i>Status Konseling
        </h6>
        <p class="text-muted mb-0 mt-1" style="font-size:.75rem;">Dari laporan hasil konseling (<?= $totalHk ?> sesi)</p>
      </div>
      <div class="card-body px-4">
        <div class="row g-2 align-items-center">
          <div class="col-sm-5 d-flex justify-content-center">
            <canvas id="chartStatusKons" width="180" height="180" style="max-width:180px;"></canvas>
          </div>
          <div class="col-sm-7">
            <div class="table-responsive">
              <table class="table table-sm mb-0" style="font-size:.78rem;">
                <thead><tr style="color:#888;"><th>Status</th><th class="text-end">Jml</th><th class="text-end">%</th></tr></thead>
                <tbody>
                  <?php
                  $i = 0;
                  foreach ($stats['status_kons'] as $key => $n):
                  $lbl = $stats['status_kons_labels'][$key];
                  $clr = $statusKonsColors[$i++] ?? '#999';
                  ?>
                  <tr>
                    <td>
                      <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:<?= $clr ?>;"></span>
                      <?= esc($lbl) ?>
                    </td>
                    <td class="text-end fw-semibold"><?= $n ?></td>
                    <td class="text-end text-muted"><?= $pct($n, $totalHk) ?>%</td>
                  </tr>
                  <?php endforeach ?>
                  <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= $totalHk ?></td><td class="text-end">100%</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Hasil Konseling -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pb-0 pt-3 px-4">
        <h6 class="fw-semibold mb-0" style="color:#1a2b40;">
          <i class="ti tabler-chart-bar me-1 text-primary"></i>Hasil Konseling
          <span class="ms-2 badge rounded-pill" style="background:rgba(111,66,193,.1);color:#6f42c1;font-size:.7rem;">Penilaian Psikolog</span>
        </h6>
        <p class="text-muted mb-0 mt-1" style="font-size:.75rem;">Respons klien terhadap sesi konseling</p>
      </div>
      <div class="card-body px-4">
        <canvas id="chartHasil" height="140"></canvas>
        <div class="table-responsive mt-3">
          <table class="table table-sm mb-0" style="font-size:.78rem;">
            <thead><tr style="color:#888;"><th>Penilaian</th><th class="text-end">Jumlah</th><th class="text-end">%</th></tr></thead>
            <tbody>
              <?php
              $i = 0;
              foreach ($stats['hasil'] as $lbl => $n):
              $clr = $hasilColors[$i++] ?? '#999';
              ?>
              <tr>
                <td>
                  <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:<?= $clr ?>;"></span>
                  <?= esc(ucwords($lbl)) ?>
                </td>
                <td class="text-end fw-semibold"><?= $n ?></td>
                <td class="text-end text-muted"><?= $pct($n, $totalHk) ?>%</td>
              </tr>
              <?php endforeach ?>
              <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= $totalHk ?></td><td class="text-end">100%</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ── Chart.js ──────────────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const fontFamily = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.font.family = fontFamily;
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 6;

// ── Tren Bulanan ──────────────────────────────────────────────────────────────
new Chart(document.getElementById('chartTren'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($trenLabels) ?>,
    datasets: [{
      label: 'Sesi Konseling',
      data: <?= json_encode($trenValues) ?>,
      backgroundColor: 'rgba(26,95,122,.2)',
      borderColor: '#1a5f7a',
      borderWidth: 2,
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
  }
});

// ── Semester Bar (grouped) ────────────────────────────────────────────────────
new Chart(document.getElementById('chartSmt'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($smtLabels) ?>,
    datasets: [
      {
        label: 'Total Sesi',
        data: <?= json_encode($smtValuesSesi) ?>,
        backgroundColor: 'rgba(26,95,122,.75)',
        borderColor: '#1a5f7a',
        borderWidth: 1,
        borderRadius: 4,
      },
      {
        label: 'Mahasiswa',
        data: <?= json_encode($smtValuesMahasiswa) ?>,
        backgroundColor: 'rgba(32,201,151,.75)',
        borderColor: '#20c997',
        borderWidth: 1,
        borderRadius: 4,
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: { font: { size: 11 }, padding: 12, usePointStyle: true, pointStyle: 'rect' }
      },
      tooltip: {
        callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` }
      }
    },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
  }
});

// ── Jenis Kelamin Donut ───────────────────────────────────────────────────────
new Chart(document.getElementById('chartJk'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($jkLabels) ?>,
    datasets: [{ data: <?= json_encode($jkValues) ?>, backgroundColor: <?= json_encode($jkColors) ?>, hoverOffset: 4 }]
  },
  options: {
    responsive: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.parsed} mahasiswa`
        }
      }
    }
  }
});

// ── Masalah Utama Horizontal Bar ──────────────────────────────────────────────
new Chart(document.getElementById('chartMasalah'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($masalahLabels) ?>,
    datasets: [{
      label: 'Sesi',
      data: <?= json_encode($masalahValues) ?>,
      backgroundColor: 'rgba(232,62,140,.7)',
      borderColor: '#e83e8c',
      borderWidth: 1,
      borderRadius: 4,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => { const pct = <?= $totalHk ?> > 0 ? Math.round(ctx.parsed.x / <?= $totalHk ?> * 1000) / 10 : 0; return ` ${ctx.parsed.x} sesi (${pct}%)`; }
        }
      }
    },
    scales: { x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
  }
});

// ── Stressor Bar ──────────────────────────────────────────────────────────────
new Chart(document.getElementById('chartStressor'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($stressorLabels) ?>,
    datasets: [{
      label: 'Sesi',
      data: <?= json_encode($stressorValues) ?>,
      backgroundColor: <?= json_encode($stressorColors) ?>,
      borderRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.parsed.y} sesi`
        }
      }
    },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
  }
});

// ── Status Konseling Donut ────────────────────────────────────────────────────
new Chart(document.getElementById('chartStatusKons'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusKonsLabelsChart) ?>,
    datasets: [{ data: <?= json_encode($statusKonsValues) ?>, backgroundColor: <?= json_encode($statusKonsColors) ?>, hoverOffset: 4 }]
  },
  options: {
    responsive: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` }
      }
    }
  }
});

// ── Hasil Konseling Horizontal Bar ────────────────────────────────────────────
new Chart(document.getElementById('chartHasil'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map('ucwords', $hasilLabels)) ?>,
    datasets: [{
      label: 'Sesi',
      data: <?= json_encode($hasilValues) ?>,
      backgroundColor: <?= json_encode($hasilColors) ?>,
      borderRadius: 4,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
  }
});

// ── Auto-submit filter on select/date change ──────────────────────────────────
document.querySelectorAll('.filter-auto').forEach(el => {
  el.addEventListener('change', () => document.getElementById('filterForm').submit());
});

// ── Stressor detail modal ─────────────────────────────────────────────────────
function _escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

let _stressorChart = null;

document.querySelectorAll('.stressor-detail-link').forEach(el => {
  el.addEventListener('click', function (e) {
    e.preventDefault();
    const key   = this.dataset.key;
    const title = document.getElementById('stressorDetailTitle');
    const body  = document.getElementById('stressorDetailBody');

    title.innerHTML = 'Detail Stressor';
    body.innerHTML  = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted" style="font-size:.85rem;">Memuat data...</div></div>';

    const params = new URLSearchParams(window.location.search);
    params.set('key', key);

    fetch('<?= base_url('dosen/stressor-detail') ?>?' + params.toString())
      .then(r => r.json())
      .then(data => {
        title.innerHTML = `Detail Stressor: <strong>${_escHtml(data.label)}</strong>`
          + ` <span class="badge rounded-pill ms-1" style="background:rgba(13,110,253,.12);color:#0d6efd;font-size:.75rem;">${data.total} sesi</span>`;

        if (data.total === 0) {
          body.innerHTML = '<p class="text-center text-muted py-3 fst-italic">Tidak ada data untuk filter ini.</p>';
          return;
        }

        // Sub-items
        let html = '<div class="mb-4">';
        html += '<div class="text-muted fw-semibold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Distribusi Sub-item</div>';
        html += '<div class="row g-3 align-items-center">';
        html += '<div class="col-md-5 d-flex align-items-center justify-content-center"><canvas id="chartStressorSubItem" style="max-height:200px;"></canvas></div>';
        html += '<div class="col-md-7"><div class="table-responsive"><table class="table table-sm mb-0" style="font-size:.82rem;">'
              + '<thead><tr style="color:#888;"><th>Sub-item</th><th class="text-end" style="width:55px;">Sesi</th><th class="text-end" style="width:55px;">%</th></tr></thead><tbody>';
        data.sub_items.forEach(item => {
          const pct = data.total > 0 ? Math.round(item.count / data.total * 1000) / 10 : 0;
          html += `<tr><td>${_escHtml(item.label)}</td><td class="text-end fw-semibold">${item.count}</td><td class="text-end text-muted">${pct}%</td></tr>`;
        });
        html += '</tbody></table></div></div>';
        html += '</div></div>';

        // By Semester + By JK
        html += '<div class="row g-3">';
        html += '<div class="col-md-7"><div class="text-muted fw-semibold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Sebaran Semester</div>'
              + '<div class="table-responsive"><table class="table table-sm mb-0" style="font-size:.82rem;">'
              + '<thead><tr style="color:#888;"><th>Semester</th><th class="text-end">Sesi</th><th class="text-end">%</th></tr></thead><tbody>';
        Object.entries(data.by_semester).forEach(([grp, n]) => {
          if (n === 0) return;
          const pct = data.total > 0 ? Math.round(n / data.total * 1000) / 10 : 0;
          html += `<tr><td>Sem. ${grp}</td><td class="text-end fw-semibold">${n}</td><td class="text-end text-muted">${pct}%</td></tr>`;
        });
        html += '</tbody></table></div></div>';

        const jkTotal = (data.by_jk['laki-laki'] || 0) + (data.by_jk['perempuan'] || 0);
        html += '<div class="col-md-5"><div class="text-muted fw-semibold mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Jenis Kelamin</div>'
              + '<div class="table-responsive"><table class="table table-sm mb-0" style="font-size:.82rem;">'
              + '<thead><tr style="color:#888;"><th>JK</th><th class="text-end">Sesi</th><th class="text-end">%</th></tr></thead><tbody>';
        [['laki-laki', 'Laki-laki'], ['perempuan', 'Perempuan']].forEach(([k, lbl]) => {
          const n   = data.by_jk[k] || 0;
          const pct = jkTotal > 0 ? Math.round(n / jkTotal * 1000) / 10 : 0;
          html += `<tr><td>${lbl}</td><td class="text-end fw-semibold">${n}</td><td class="text-end text-muted">${pct}%</td></tr>`;
        });
        html += `<tr class="table-light fw-bold"><td>Total</td><td class="text-end">${jkTotal}</td><td class="text-end">—</td></tr>`;
        html += '</tbody></table></div></div>';
        html += '</div>';

        body.innerHTML = html;

        // Pie chart sub-items
        if (_stressorChart) { _stressorChart.destroy(); _stressorChart = null; }
        const _subCanvas = document.getElementById('chartStressorSubItem');
        if (_subCanvas && data.sub_items.length) {
          const _palette = [
            'rgba(13,110,253,.8)','rgba(32,201,151,.8)','rgba(255,193,7,.9)',
            'rgba(220,53,69,.8)','rgba(111,66,193,.8)','rgba(253,126,20,.8)',
            'rgba(13,202,240,.8)','rgba(102,16,242,.8)','rgba(214,51,132,.8)',
            'rgba(25,135,84,.8)',
          ];
          _stressorChart = new Chart(_subCanvas, {
            type: 'doughnut',
            data: {
              labels: data.sub_items.map(i => i.label),
              datasets: [{
                data: data.sub_items.map(i => i.count),
                backgroundColor: _palette.slice(0, data.sub_items.length),
                borderWidth: 1,
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { display: false },
                tooltip: {
                  callbacks: {
                    label: ctx => {
                      const tot = ctx.dataset.data.reduce((a, b) => a + b, 0);
                      const pct = tot > 0 ? Math.round(ctx.parsed / tot * 1000) / 10 : 0;
                      return ` ${ctx.parsed} sesi (${pct}%)`;
                    }
                  }
                }
              }
            }
          });
        }
      })
      .catch(() => {
        body.innerHTML = '<div class="alert alert-danger m-3">Gagal memuat data. Coba lagi.</div>';
      });
  });
});
</script>

<!-- ── Modal Detail Stressor ─────────────────────────────────────────────────── -->
<div class="modal fade" id="modalStressorDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-semibold" id="stressorDetailTitle" style="font-size:1rem;">Detail Stressor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2" id="stressorDetailBody">
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
