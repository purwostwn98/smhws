<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">
      Selamat datang, <?= esc(explode(' ', session()->get('user_name'))[0]) ?>! 👋
    </h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
      Pantau konseling dan kesehatan mentalmu di sini.
    </p>
  </div>
  <a href="<?= base_url('janji/buat') ?>" class="btn btn-primary d-none d-sm-flex align-items-center gap-2">
    <i class="ti tabler-calendar-plus"></i>Daftar Konseling Baru
  </a>
</div>

<!-- ===== STAT CARDS ===== -->
<div class="row g-4 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(26,95,122,.15);color:#1a5f7a;font-size:1.25rem;">
            <i class="ti tabler-calendar-check"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;"><?= $stats['janji_aktif'] ?? 0 ?></div>
          <div class="text-muted" style="font-size:.8rem;">Konseling Aktif</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(45,155,110,.15);color:#2d9b6e;font-size:1.25rem;">
            <i class="ti tabler-circle-check"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;"><?= $stats['sesi_selesai'] ?? 0 ?></div>
          <div class="text-muted" style="font-size:.8rem;">Sesi Selesai</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(87,197,182,.15);color:#57c5b6;font-size:1.25rem;">
            <i class="ti tabler-clock-hour-4"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;"><?= $stats['janji_menunggu'] ?? 0 ?></div>
          <div class="text-muted" style="font-size:.8rem;">Menunggu Konfirmasi</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="avatar flex-shrink-0">
          <div class="avatar-initial rounded" style="background:rgba(240,165,0,.15);color:#f0a500;font-size:1.25rem;">
            <i class="ti tabler-star"></i>
          </div>
        </div>
        <div>
          <div class="fw-bold" style="font-size:1.5rem;color:#1a2b40;"><?= $stats['total_sesi'] ?? 0 ?></div>
          <div class="text-muted" style="font-size:.8rem;">Total Sesi</div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ===== ROW 2: Janji Mendatang + Quick Actions ===== -->
<div class="row g-4 mb-4">

  <!-- Janji Mendatang -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Konseling Mendatang</h5>
        <a href="<?= base_url('janji') ?>" class="btn btn-sm btn-label-primary">Lihat Semua</a>
      </div>
      <div class="card-body">
        <?php if (empty($janji_mendatang)): ?>
          <div class="text-center py-5">
            <div class="avatar avatar-lg mx-auto mb-3">
              <div class="avatar-initial rounded-circle" style="background:rgba(26,95,122,.1);color:#1a5f7a;font-size:1.75rem;">
                <i class="ti tabler-calendar-off"></i>
              </div>
            </div>
            <h6 class="text-muted">Belum ada konseling aktif</h6>
            <p class="text-muted mb-3" style="font-size:.85rem;">Daftar konseling pertamamu sekarang</p>
            <a href="<?= base_url('janji/buat') ?>" class="btn btn-primary btn-sm">
              <i class="ti tabler-calendar-plus me-1"></i>Daftar Konseling
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>No. Konseling</th>
                  <th>Tanggal & Waktu</th>
                  <th>Metode</th>
                  <th>Konselor</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($janji_mendatang as $janji): ?>
                  <?php
                    [$statusClass, $statusLabel] = match ($janji['status']) {
                        'menunggu'     => ['bg-label-warning', 'Menunggu'],
                        'dikonfirmasi' => ['bg-label-primary',  'Dikonfirmasi'],
                        'berlangsung'  => ['bg-label-info',     'Berlangsung'],
                        default        => ['bg-label-secondary', ucfirst($janji['status'])],
                    };
                    [$metodeClass, $metodeIcon] = match ($janji['metode']) {
                        'online'  => ['bg-label-info',      'tabler-video'],
                        'hybrid'  => ['bg-label-primary',   'tabler-arrows-exchange'],
                        default   => ['bg-label-secondary', 'tabler-map-pin'],
                    };
                    $konselorNama = $konselorMap[$janji['konselor_id']] ?? null;
                  ?>
                  <tr>
                    <td class="fw-semibold" style="font-size:.85rem;color:#1a2b40;">
                      #<?= str_pad($janji['id'], 5, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td>
                      <?php if (! empty($janji['tanggal_konseling'])): ?>
                        <div class="fw-semibold" style="font-size:.85rem;">
                          <?= date('d M Y', strtotime($janji['tanggal_konseling'])) ?>
                        </div>
                        <?php if (! empty($janji['jam_konseling'])): ?>
                          <small class="text-muted"><?= substr($janji['jam_konseling'], 0, 5) ?> WIB</small>
                        <?php endif ?>
                      <?php else: ?>
                        <span class="text-muted" style="font-size:.82rem;">Belum dijadwalkan</span>
                      <?php endif ?>
                    </td>
                    <td>
                      <span class="badge <?= $metodeClass ?>" style="font-size:.75rem;">
                        <i class="ti <?= $metodeIcon ?> me-1"></i><?= ucfirst($janji['metode']) ?>
                      </span>
                    </td>
                    <td style="font-size:.83rem;">
                      <?= $konselorNama ? esc($konselorNama) : '<span class="text-muted">Belum ditetapkan</span>' ?>
                    </td>
                    <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <td>
                      <a href="<?= base_url('janji/' . $janji['id']) ?>" class="btn btn-icon btn-sm btn-label-primary">
                        <i class="ti tabler-eye"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Aksi Cepat</h5>
      </div>
      <div class="card-body d-flex flex-column gap-3">

        <a href="<?= base_url('janji/buat') ?>" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
          style="background:rgba(26,95,122,.06);transition:background .2s;"
          onmouseover="this.style.background='rgba(26,95,122,.12)'"
          onmouseout="this.style.background='rgba(26,95,122,.06)'">
          <div class="avatar">
            <div class="avatar-initial rounded" style="background:#1a5f7a;color:#fff;">
              <i class="ti tabler-calendar-plus"></i>
            </div>
          </div>
          <div>
            <div class="fw-semibold text-heading">Daftar Konseling Baru</div>
            <small class="text-muted">Jadwalkan sesi konseling</small>
          </div>
          <i class="ti tabler-chevron-right ms-auto text-muted"></i>
        </a>

        <a href="<?= base_url('riwayat') ?>" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
          style="background:rgba(45,155,110,.06);transition:background .2s;"
          onmouseover="this.style.background='rgba(45,155,110,.12)'"
          onmouseout="this.style.background='rgba(45,155,110,.06)'">
          <div class="avatar">
            <div class="avatar-initial rounded" style="background:#2d9b6e;color:#fff;">
              <i class="ti tabler-history"></i>
            </div>
          </div>
          <div>
            <div class="fw-semibold text-heading">Riwayat Sesi</div>
            <small class="text-muted">Lihat catatan konseling</small>
          </div>
          <i class="ti tabler-chevron-right ms-auto text-muted"></i>
        </a>

        <a href="<?= base_url('profil') ?>" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
          style="background:rgba(87,197,182,.06);transition:background .2s;"
          onmouseover="this.style.background='rgba(87,197,182,.12)'"
          onmouseout="this.style.background='rgba(87,197,182,.06)'">
          <div class="avatar">
            <div class="avatar-initial rounded" style="background:#57c5b6;color:#fff;">
              <i class="ti tabler-user-circle"></i>
            </div>
          </div>
          <div>
            <div class="fw-semibold text-heading">Profil Saya</div>
            <small class="text-muted">Perbarui data diri</small>
          </div>
          <i class="ti tabler-chevron-right ms-auto text-muted"></i>
        </a>

        <a href="<?= base_url('/#faq') ?>" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
          style="background:rgba(240,165,0,.06);transition:background .2s;"
          onmouseover="this.style.background='rgba(240,165,0,.12)'"
          onmouseout="this.style.background='rgba(240,165,0,.06)'">
          <div class="avatar">
            <div class="avatar-initial rounded" style="background:#f0a500;color:#fff;">
              <i class="ti tabler-help-circle"></i>
            </div>
          </div>
          <div>
            <div class="fw-semibold text-heading">Bantuan & FAQ</div>
            <small class="text-muted">Pertanyaan umum</small>
          </div>
          <i class="ti tabler-chevron-right ms-auto text-muted"></i>
        </a>

      </div>
    </div>
  </div>

</div>

<!-- ===== ROW 3: Profil Ringkas + Tips ===== -->
<div class="row g-4">

  <!-- Profil Ringkas -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body text-center pt-4">
        <div class="avatar avatar-xl mx-auto mb-3">
          <div class="avatar-initial rounded-circle fw-bold"
            style="background:linear-gradient(135deg,#1a5f7a,#2d9b6e);color:#fff;font-size:1.75rem;">
            <?= strtoupper(substr(session()->get('user_name'), 0, 1)) ?>
          </div>
        </div>
        <h5 class="fw-semibold mb-1"><?= esc(session()->get('user_name')) ?></h5>
        <span class="badge bg-label-primary mb-2">Mahasiswa</span>
        <p class="text-muted mb-3" style="font-size:.82rem;">
          <?= esc(session()->get('user_email')) ?>
        </p>
        <?php if (!empty($user['fakultas'])): ?>
          <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
            <i class="ti tabler-building-community text-muted" style="font-size:.9rem;"></i>
            <small class="text-muted"><?= esc($user['fakultas']) ?></small>
          </div>
        <?php endif ?>
        <?php if (!empty($user['nim_nip'])): ?>
          <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
            <i class="ti tabler-id-badge text-muted" style="font-size:.9rem;"></i>
            <small class="text-muted"><?= esc($user['nim_nip']) ?></small>
          </div>
        <?php endif ?>
        <a href="<?= base_url('profil') ?>" class="btn btn-sm btn-label-primary w-100">
          <i class="ti tabler-edit me-1"></i>Edit Profil
        </a>
      </div>
    </div>
  </div>

  <!-- Tips Kesehatan Mental -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="ti tabler-bulb me-2" style="color:#f0a500;"></i>Tips Kesehatan Mental
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-sm-6">
            <div class="p-3 rounded-3 h-100" style="background:#f4fafc;border-left:3px solid #1a5f7a;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-moon-stars mt-1" style="color:#1a5f7a;font-size:1.1rem;flex-shrink:0;"></i>
                <div>
                  <div class="fw-semibold mb-1" style="font-size:.85rem;color:#1a2b40;">Tidur yang Cukup</div>
                  <div class="text-muted" style="font-size:.78rem;">Tidur 7–8 jam per malam membantu otak memproses emosi dan mengurangi stres akademik.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="p-3 rounded-3 h-100" style="background:#f4fafc;border-left:3px solid #2d9b6e;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-run mt-1" style="color:#2d9b6e;font-size:1.1rem;flex-shrink:0;"></i>
                <div>
                  <div class="fw-semibold mb-1" style="font-size:.85rem;color:#1a2b40;">Aktif Bergerak</div>
                  <div class="text-muted" style="font-size:.78rem;">Olahraga ringan 30 menit sehari terbukti menurunkan kecemasan dan meningkatkan mood.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="p-3 rounded-3 h-100" style="background:#f4fafc;border-left:3px solid #57c5b6;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-heart mt-1" style="color:#57c5b6;font-size:1.1rem;flex-shrink:0;"></i>
                <div>
                  <div class="fw-semibold mb-1" style="font-size:.85rem;color:#1a2b40;">Jaga Koneksi Sosial</div>
                  <div class="text-muted" style="font-size:.78rem;">Luangkan waktu untuk berbicara dengan teman atau keluarga, bahkan sekadar pesan singkat.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="p-3 rounded-3 h-100" style="background:#f4fafc;border-left:3px solid #f0a500;">
              <div class="d-flex gap-2 align-items-start">
                <i class="ti tabler-brain mt-1" style="color:#f0a500;font-size:1.1rem;flex-shrink:0;"></i>
                <div>
                  <div class="fw-semibold mb-1" style="font-size:.85rem;color:#1a2b40;">Mindfulness</div>
                  <div class="text-muted" style="font-size:.78rem;">Latihan nafas dalam 5 menit sehari dapat menenangkan sistem saraf dan meningkatkan fokus.</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
