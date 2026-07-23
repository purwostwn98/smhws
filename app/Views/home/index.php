<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Layanan kesehatan mental dan wellbeing untuk mahasiswa dan civitas akademika Universitas Muhammadiyah Surakarta.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$timPsikolog = $timPsikolog ?? [];
$ketua       = $ketua       ?? [];
$staff       = $staff       ?? [];
?>

<!-- =============== HERO =============== -->
<section id="hero">
  <div id="landingHero" class="section-py landing-hero position-relative overflow-hidden">
    <!-- decorative blobs -->
    <div class="position-absolute top-0 end-0 opacity-25" style="width:500px;height:500px;background:radial-gradient(circle,#57c5b6 0%,transparent 70%);transform:translate(30%,-30%);pointer-events:none;"></div>
    <div class="position-absolute bottom-0 start-0 opacity-25" style="width:350px;height:350px;background:radial-gradient(circle,#1a5f7a 0%,transparent 70%);transform:translate(-30%,30%);pointer-events:none;"></div>

    <div class="container">
      <div class="row align-items-center gy-8">

        <!-- Teks -->
        <div class="col-lg-6">
          <h1 class="display-5 fw-extrabold mb-3" style="color:var(--smhws-dark);line-height:1.2;">
            Selamat Datang di<br>
            <span style="color:var(--smhws-primary);">SMHWS</span>
          </h1>
          <p class="fs-5 fw-medium mb-3" style="color:#2d6a8a;">
            Sahabat dekat mahasiswa untuk tumbuh lebih bermakna, sejahtera, tangguh, dan menginspirasi.
          </p>
          <p class="mb-6" style="color:#4a6378;line-height:1.75;">
            Kami percaya bahwa setiap mahasiswa memiliki potensi untuk tumbuh menjadi pribadi yang berdaya
            dan inspiratif. SMHWS berkomitmen untuk hadir membantu dan mendampingi mahasiswa dalam
            mengembangkan potensi diri, menjaga kesehatan mental, mengatasi berbagai tantangan kehidupan
            kampus, serta membangun ketangguhan untuk meraih prestasi dan memberikan makna.
          </p>
          <div class="d-flex flex-wrap gap-3 mb-5">
            <a href="#layanan" class="btn btn-outline-secondary btn-lg px-4">
              <i class="ti tabler-info-circle me-2"></i>Pelajari Layanan
            </a>
            <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg px-5">
              <i class="ti tabler-calendar-event me-2"></i>Daftar Konseling
            </a>
          </div>

          <!-- Crisis Support -->
          <div class="rounded-3 p-4 d-flex align-items-start gap-3"
               style="background:rgba(240,165,0,.1);border:1px solid rgba(240,165,0,.3);">
            <i class="ti tabler-alert-triangle text-warning mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
            <div>
              <div class="fw-semibold mb-1" style="color:#1a2b40;font-size:.9rem;">Dukungan Krisis</div>
              <p class="mb-0 small" style="color:#555;">
                Jika kamu mengalami kondisi darurat, silakan hubungi <strong>112</strong> atau langsung
                ke rumah sakit terdekat untuk mendapat penanganan segera.
              </p>
            </div>
          </div>
        </div>

        <!-- Gambar -->
        <div class="col-lg-6 text-center">
          <div class="position-relative d-inline-block">
            <img src="<?= base_url('myimg/landing1.jpg') ?>" alt="Layanan SMHWS UMS"
                 class="img-fluid rounded-4 shadow-lg"
                 style="max-height:460px;width:100%;object-fit:cover;">
            <!-- floating badge -->
            <div class="position-absolute bottom-0 start-0 translate-middle-y ms-4 bg-white shadow rounded-3 px-4 py-3 d-flex align-items-center gap-2">
              <i class="ti tabler-shield-check text-primary fs-4"></i>
              <div>
                <div class="fw-bold text-dark" style="font-size:.82rem;">100% Rahasia</div>
                <div class="text-muted" style="font-size:.7rem;">Sesuai kode etik psikologi</div>
              </div>
            </div>
            <div class="position-absolute top-0 end-0 translate-middle bg-white shadow rounded-3 px-3 py-2 d-flex align-items-center gap-2">
              <i class="ti tabler-currency-dollar-off text-success fs-5"></i>
              <div class="fw-bold" style="font-size:.82rem;color:#1a2b40;">Gratis</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
<!-- / Hero -->

<!-- =============== LAYANAN =============== -->
<section id="layanan" class="section-py">
  <div class="container">
    <div class="text-center mb-2">
      <span class="badge bg-label-primary px-3 py-2">Layanan Kami</span>
    </div>
    <h2 class="text-center fw-extrabold mb-2">Kami Siap <span style="color:var(--smhws-primary);">Mendukungmu</span></h2>
    <p class="text-center text-muted mb-10 mx-auto" style="max-width:560px;">
      Berbagai layanan profesional yang dirancang untuk membantu kamu menavigasi tantangan emosional dan psikologis.
    </p>

    <div class="row g-5 justify-content-center">

      <!-- Mental Health Check -->
      <div class="col-lg-3 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5 d-flex flex-column">
            <div class="mb-4" style="color:var(--smhws-primary);">
              <i class="ti tabler-heart-rate-monitor" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Mental Health Check</h5>
            <p class="text-muted mb-4 flex-grow-1">
              Di sini kamu bisa melakukan skrining kesehatan mental secara online untuk memahami kondisi dirimu.
            </p>
            <a href="<?= base_url('login') ?>" class="btn btn-label-primary btn-sm">Cek Sekarang</a>
          </div>
        </div>
      </div>

      <!-- SMHWS Talk & Care -->
      <div class="col-lg-3 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5 d-flex flex-column">
            <div class="mb-4" style="color:var(--smhws-primary);">
              <i class="ti tabler-user-heart" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">SMHWS Talk &amp; Care</h5>
            <p class="text-muted mb-4 flex-grow-1">
              Di sini kamu bisa curhat aman dan nyaman, atau melakukan konsultasi masalahmu dengan
              psikolog profesional secara offline atau online.
            </p>
            <a href="<?= base_url('login') ?>" class="btn btn-label-primary btn-sm">Daftar Sekarang</a>
          </div>
        </div>
      </div>

      <!-- Psychoeducation & Workshop -->
      <div class="col-lg-3 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5 d-flex flex-column">
            <div class="mb-4" style="color:var(--smhws-primary);">
              <i class="ti tabler-school" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Psychoeducation &amp; Workshop</h5>
            <p class="text-muted mb-4 flex-grow-1">
              Di sini kamu bisa mendaftarkan diri untuk mengikuti kegiatan pengembangan diri, mengelola
              stres, dan keterampilan kesehatan mental &amp; wellbeing lainnya.
            </p>
            <a href="<?= base_url('login') ?>" class="btn btn-label-primary btn-sm">Daftar Sekarang</a>
          </div>
        </div>
      </div>

      <!-- SMHWS Learn -->
      <div class="col-lg-3 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5 d-flex flex-column">
            <div class="mb-4" style="color:var(--smhws-primary);">
              <i class="ti tabler-books" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">SMHWS Learn</h5>
            <p class="text-muted mb-4 flex-grow-1">
              Di sini kamu bisa menemukan artikel, video, dan media belajar untuk meningkatkan
              kesehatan mental dan kondisi wellbeing-mu.
            </p>
            <a href="#kontak" class="btn btn-label-primary btn-sm">Jelajahi Sekarang</a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- / Layanan -->

<!-- =============== TENTANG =============== -->
<section id="tentang" class="section-py" style="background:var(--smhws-bg);">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center mb-8">
        <span class="badge bg-label-primary px-3 py-2 mb-3">Tentang SMHWS</span>
        <h2 class="fw-extrabold mb-4">Mengenal <span style="color:var(--smhws-primary);">SMHWS UMS</span></h2>
        <p class="text-muted mb-0" style="line-height:1.8;">
          SMHWS hadir sejak tahun <strong>2019</strong>, sebagai bentuk komitmen UMS untuk mendukung
          kesehatan mental dan well-being mahasiswa. Kami percaya bahwa kesehatan mental dan wellbeing
          merupakan fondasi keberhasilan akademik dan kehidupan yang berkualitas.
        </p>
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex gap-3">
          <span class="badge bg-label-primary p-2 flex-shrink-0" style="height:fit-content;">
            <i class="ti tabler-lock text-primary fs-5"></i>
          </span>
          <div>
            <h6 class="fw-bold mb-1">Kerahasiaan Penuh</h6>
            <p class="text-muted small mb-0">Semua sesi dijaga kerahasiaannya sesuai kode etik psikologi.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex gap-3">
          <span class="badge bg-label-primary p-2 flex-shrink-0" style="height:fit-content;">
            <i class="ti tabler-award text-primary fs-5"></i>
          </span>
          <div>
            <h6 class="fw-bold mb-1">Psikolog Profesional</h6>
            <p class="text-muted small mb-0">Tim psikolog bersertifikat dan berpengalaman di bidangnya.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex gap-3">
          <span class="badge bg-label-primary p-2 flex-shrink-0" style="height:fit-content;">
            <i class="ti tabler-currency-dollar-off text-primary fs-5"></i>
          </span>
          <div>
            <h6 class="fw-bold mb-1">Gratis untuk Sivitas UMS</h6>
            <p class="text-muted small mb-0">Seluruh layanan tidak dipungut biaya bagi mahasiswa aktif UMS.</p>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="d-flex gap-3">
          <span class="badge bg-label-primary p-2 flex-shrink-0" style="height:fit-content;">
            <i class="ti tabler-heart-handshake text-primary fs-5"></i>
          </span>
          <div>
            <h6 class="fw-bold mb-1">Pendekatan Holistik</h6>
            <p class="text-muted small mb-0">Menggabungkan pendekatan psikologis modern dan nilai Islam.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- / Tentang -->

<!-- =============== TIM PSIKOLOG =============== -->
<section id="tim" class="section-py">
  <div class="container">
    <div class="text-center mb-2">
      <span class="badge bg-label-primary px-3 py-2">Tim Kami</span>
    </div>
    <h2 class="text-center fw-extrabold mb-2">
      Didukung oleh <span style="color:var(--smhws-primary);">Psikolog Profesional</span>
    </h2>
    <p class="text-center text-muted mb-10">Kenali tim psikolog SMHWS yang siap mendampingi perjalananmu.</p>

    <!-- Ketua & Staff -->
    <?php if (!empty($ketua) || !empty($staff)): ?>
    <div class="mb-4">
      <p class="text-center fw-semibold mb-6" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;color:#aaa;">Pimpinan</p>
      <div class="row justify-content-center g-5">

        <?php foreach ($ketua as $p): ?>
        <div class="col-auto">
          <div class="tim-card text-center" style="width:200px;">
            <div class="position-relative mx-auto mb-4" style="width:180px;height:240px;">
              <div class="position-absolute bottom-0 start-50 translate-middle-x rounded-4"
                   style="width:130px;height:195px;background:#3ecfca;z-index:0;"></div>
              <img src="<?= $p['url'] ?>" alt="<?= esc($p['nama']) ?>"
                   class="position-absolute bottom-0 start-50 translate-middle-x"
                   style="z-index:1;width:160px;height:220px;object-fit:cover;object-position:top center;">
            </div>
            <h6 class="fw-bold mb-1 px-1" style="font-size:.88rem;color:#1a2b40;line-height:1.4;"><?= esc($p['nama']) ?></h6>
            <span class="badge bg-label-warning px-3 py-2 mt-1" style="font-size:.72rem;">Ketua SMHWS</span>
          </div>
        </div>
        <?php endforeach ?>

        <?php foreach ($staff as $p): ?>
        <div class="col-auto">
          <div class="tim-card text-center" style="width:200px;">
            <div class="position-relative mx-auto mb-4" style="width:180px;height:240px;">
              <div class="position-absolute bottom-0 start-50 translate-middle-x rounded-4"
                   style="width:130px;height:195px;background:#3ecfca;z-index:0;"></div>
              <img src="<?= $p['url'] ?>" alt="<?= esc($p['nama']) ?>"
                   class="position-absolute bottom-0 start-50 translate-middle-x"
                   style="z-index:1;width:160px;height:220px;object-fit:cover;object-position:top center;">
            </div>
            <h6 class="fw-bold mb-1 px-1" style="font-size:.88rem;color:#1a2b40;line-height:1.4;"><?= esc($p['nama']) ?></h6>
            <span class="badge bg-label-info px-3 py-2 mt-1" style="font-size:.72rem;">Staf SMHWS</span>
          </div>
        </div>
        <?php endforeach ?>

      </div>
    </div>

    <!-- Divider -->
    <div class="d-flex align-items-center gap-4 mb-10">
      <hr class="flex-grow-1" style="border-color:#e8e8e8;">
      <span class="text-muted fw-semibold" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;">Tim Psikolog</span>
      <hr class="flex-grow-1" style="border-color:#e8e8e8;">
    </div>
    <?php endif ?>

    <?php if (empty($timPsikolog)): ?>
      <div class="text-center py-5 text-muted">
        <i class="ti tabler-users-minus" style="font-size:3rem;opacity:.3;"></i>
        <p class="mt-3">Data psikolog belum tersedia</p>
      </div>
    <?php else: ?>
    <?php
    $pastelBg = [
      '#e8e4f8', /* lavender */
      '#d4eff0', /* mint     */
      '#fde4e8', /* pink     */
      '#d4f0e4', /* green    */
      '#fef0d4', /* peach    */
      '#d4e4f8', /* sky blue */
      '#f8e4f4', /* lilac    */
      '#e4f8e8', /* sage     */
    ];
    $gradients = [
      'linear-gradient(135deg,#6c5ce7,#a29bfe)',
      'linear-gradient(135deg,#1a5f7a,#57c5b6)',
      'linear-gradient(135deg,#e17055,#fab1a0)',
      'linear-gradient(135deg,#2d9b6e,#55efc4)',
      'linear-gradient(135deg,#f0a500,#fdcb6e)',
      'linear-gradient(135deg,#0984e3,#74b9ff)',
      'linear-gradient(135deg,#e84393,#fd79a8)',
      'linear-gradient(135deg,#00b894,#00cec9)',
    ];
    ?>

    <!-- Swiper carousel -->
    <div class="position-relative">
      <!-- Prev/Next buttons -->
      <button class="tim-prev d-none d-md-flex align-items-center justify-content-center position-absolute top-50 translate-middle-y"
              style="left:-24px;z-index:10;width:48px;height:48px;border-radius:50%;border:none;
                     background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.12);cursor:pointer;color:var(--smhws-primary);">
        <i class="ti tabler-chevron-left fs-4"></i>
      </button>
      <button class="tim-next d-none d-md-flex align-items-center justify-content-center position-absolute top-50 translate-middle-y"
              style="right:-24px;z-index:10;width:48px;height:48px;border-radius:50%;border:none;
                     background:#fff;box-shadow:0 4px 16px rgba(0,0,0,.12);cursor:pointer;color:var(--smhws-primary);">
        <i class="ti tabler-chevron-right fs-4"></i>
      </button>

      <div class="swiper tim-psikolog-swiper" style="padding-bottom:48px;">
        <div class="swiper-wrapper">
          <?php foreach ($timPsikolog as $idx => $k):
            $namaLengkap = \App\Models\KonselorModel::namaLengkap($k);
            $inisial = strtoupper(implode('', array_map(
              fn($w) => $w[0],
              array_filter(explode(' ', $k['name'] ?? ''), fn($w) => ctype_upper($w[0] ?? ''))
            )));
            if (strlen($inisial) > 2) $inisial = substr($inisial, 0, 2);
            if (!$inisial) $inisial = strtoupper(substr($k['name'] ?? 'P', 0, 1));
            $bg   = $pastelBg[$idx % count($pastelBg)];
            $grad = $gradients[$idx % count($gradients)];
            $spList = is_array($k['spesialisasi']) ? $k['spesialisasi'] : [];
          ?>
          <div class="swiper-slide">
            <div class="tim-card text-center" style="transition:transform .25s;">

              <!-- Foto + blok teal di belakang -->
              <div class="position-relative mx-auto mb-4" style="width:180px;height:240px;">
                <!-- Blok teal -->
                <div class="position-absolute bottom-0 start-50 translate-middle-x rounded-4"
                     style="width:130px;height:195px;background:#3ecfca;z-index:0;"></div>
                <!-- Foto / Avatar -->
                <?php if (!empty($k['foto'])): ?>
                  <img src="<?= base_url($k['foto']) ?>" alt="<?= esc($k['name']) ?>"
                       class="position-absolute bottom-0 start-50 translate-middle-x"
                       style="z-index:1;width:160px;height:220px;object-fit:cover;object-position:top center;display:block;">
                <?php else: ?>
                  <div class="position-absolute bottom-0 start-50 translate-middle-x d-flex align-items-end justify-content-center"
                       style="z-index:1;width:160px;height:220px;padding-bottom:16px;">
                    <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                          style="width:96px;height:96px;background:<?= $grad ?>;color:#fff;font-size:1.8rem;
                                 box-shadow:0 6px 20px rgba(0,0,0,.18);">
                      <?= $inisial ?>
                    </span>
                  </div>
                <?php endif ?>
              </div>

              <!-- Info -->
              <h6 class="fw-bold mb-2 px-2" style="font-size:.92rem;color:#1a2b40;line-height:1.4;"><?= esc($namaLengkap) ?></h6>
              <?php if (!empty($spList)): ?>
                <div class="d-flex flex-wrap justify-content-center gap-1 mb-3 px-2">
                  <?php foreach ($spList as $sp): ?>
                    <span class="badge bg-label-primary" style="font-size:.7rem;font-weight:500;"><?= esc($sp) ?></span>
                  <?php endforeach ?>
                </div>
              <?php else: ?>
                <p class="text-muted mb-3" style="font-size:.8rem;">Psikolog</p>
              <?php endif ?>
              <div class="d-flex align-items-center justify-content-center gap-3">
                <div class="d-flex align-items-center gap-1">
                  <i class="ti tabler-star-filled" style="color:#f0a500;font-size:.85rem;"></i>
                  <span class="fw-semibold" style="font-size:.82rem;"><?= number_format((float)$k['rating'], 1) ?></span>
                </div>
                <span style="color:#ddd;">|</span>
                <span style="font-size:.78rem;color:#888;"><?= number_format($k['total_sesi']) ?> sesi</span>
              </div>

            </div>
          </div>
          <?php endforeach ?>
        </div>
        <!-- Pagination dots -->
        <div class="swiper-pagination tim-pagination" style="bottom:0;"></div>
      </div>
    </div>
    <?php endif ?>
  </div>
</section>
<!-- / Tim Psikolog -->

<!-- =============== PROSES KONSELING =============== -->
<section class="section-py">
  <div class="container">
    <div class="text-center mb-2">
      <span class="badge bg-label-primary px-3 py-2">Alur Layanan</span>
    </div>
    <h2 class="text-center fw-extrabold mb-10">Bagaimana <span style="color:var(--smhws-primary);">Prosesnya?</span></h2>

    <div class="row g-4 justify-content-center">
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-primary);">1</span>
        </div>
        <h6 class="fw-bold mb-2">Daftar Online</h6>
        <p class="text-muted small">Isi formulir pendaftaran online atau hubungi kami via WhatsApp/email untuk membuat jadwal.</p>
      </div>
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-secondary);">2</span>
        </div>
        <h6 class="fw-bold mb-2">Asesmen Awal</h6>
        <p class="text-muted small">Psikolog akan melakukan asesmen singkat untuk memahami kebutuhanmu dan merencanakan sesi.</p>
      </div>
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-accent);">3</span>
        </div>
        <h6 class="fw-bold mb-2">Sesi Konseling</h6>
        <p class="text-muted small">Jalani sesi konseling tatap muka atau online dengan psikolog secara nyaman dan rahasia.</p>
      </div>
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-accent-warm);">4</span>
        </div>
        <h6 class="fw-bold mb-2">Tindak Lanjut</h6>
        <p class="text-muted small">Bersama psikolog, susun rencana pengembangan diri dan jadwal sesi lanjutan sesuai kebutuhan.</p>
      </div>
    </div>
  </div>
</section>
<!-- / Proses -->

<!-- =============== FAQ =============== -->
<section id="faq" class="section-py" style="background:var(--smhws-bg);">
  <div class="container">
    <div class="text-center mb-2">
      <span class="badge bg-label-primary px-3 py-2">FAQ</span>
    </div>
    <h2 class="text-center fw-extrabold mb-2">Pertanyaan yang <span style="color:var(--smhws-primary);">Sering Diajukan</span></h2>
    <p class="text-center text-muted mb-10 mx-auto" style="max-width:520px;">
      Temukan jawaban atas pertanyaan umum seputar layanan SMHWS UMS.
    </p>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="accordion" id="faqAccordion">

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                Apakah layanan konseling ini gratis untuk mahasiswa UMS?
              </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Ya, seluruh layanan SMHWS — termasuk konseling individual, konseling kelompok, skrining psikologi,
                dan kegiatan psychoeducation — sepenuhnya <strong>gratis</strong> bagi mahasiswa aktif dan
                civitas akademika Universitas Muhammadiyah Surakarta.
              </div>
            </div>
          </div>

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                Apakah informasi yang saya ceritakan akan dijaga kerahasiannya?
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Tentu. Semua informasi yang kamu bagikan kepada psikolog yang menanganimu akan dijaga
                kerahasiannya sesuai dengan <strong>Kode Etik Psikologi Indonesia</strong>. Informasi tidak
                dibagikan kepada orang lain, termasuk teman, orang tua, dosen, dan pihak lainnya, kecuali
                atas ijin dari kamu atau ketika dalam situasi darurat yang membahayakan keselamatan jiwa.
              </div>
            </div>
          </div>

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                Bagaimana cara mendaftar untuk konseling?
              </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Kamu bisa mendaftar dengan: (1) mengisi formulir pendaftaran online di bagian Daftar Konseling
                halaman ini, (2) menghubungi kami via WhatsApp di nomor yang tertera, atau (3) datang langsung
                ke Gedung Siti Walidah Lantai 2 UMS pada jam kerja Senin–Jumat pukul 08.00–16.00 WIB.
              </div>
            </div>
          </div>

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                Berapa lama durasi satu sesi konseling?
              </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Setiap sesi konseling individual berlangsung selama <strong>50–60 menit</strong>.
                Psikolog akan mendiskusikan berapa banyak sesi yang dibutuhkan berdasarkan asesmen awal.
              </div>
            </div>
          </div>

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                Apakah bisa konseling secara online?
              </button>
            </h2>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Ya, kami menyediakan layanan konseling <strong>online via video call</strong> (Zoom/Google Meet)
                bagi mahasiswa yang tidak dapat hadir secara langsung. Kamu cukup mencantumkan preferensi
                "online" saat mendaftar.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
<!-- / FAQ -->

<!-- =============== KONTAK =============== -->
<section id="kontak" class="section-py">
  <div class="container">
    <div class="row g-6 justify-content-center">

      <!-- Contact Info -->
      <div class="col-lg-8">
        <span class="badge bg-label-primary px-3 py-2 mb-3">Hubungi Kami</span>
        <h2 class="fw-extrabold mb-4">Ada yang Ingin <span style="color:var(--smhws-primary);">Ditanyakan?</span></h2>
        <p class="text-muted mb-6">
          Tim kami siap membantu menjawab pertanyaan dan mendampingi kamu dalam proses mencari bantuan.
        </p>

        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="badge bg-label-primary p-2"><i class="ti tabler-map-pin text-primary fs-5"></i></div>
          <div>
            <h6 class="fw-bold mb-1">Lokasi</h6>
            <p class="text-muted small mb-0">Gedung Siti Walidah Lt. 2, UMS<br>Jl. Ahmad Yani, Pabelan, Surakarta 57162</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="badge bg-label-primary p-2"><i class="ti tabler-clock text-primary fs-5"></i></div>
          <div>
            <h6 class="fw-bold mb-1">Jam Operasional</h6>
            <p class="text-muted small mb-0">Senin – Jumat: 08.00 – 16.00 WIB<br>Sabtu &amp; Minggu: Tutup</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-4">
          <div class="badge bg-label-primary p-2"><i class="ti tabler-brand-whatsapp text-primary fs-5"></i></div>
          <div>
            <h6 class="fw-bold mb-1">WhatsApp</h6>
            <a href="https://wa.me/6208777770000" class="text-primary small" target="_blank">0877-7777-0000</a>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-6">
          <div class="badge bg-label-primary p-2"><i class="ti tabler-mail text-primary fs-5"></i></div>
          <div>
            <h6 class="fw-bold mb-1">Email</h6>
            <a href="mailto:smhws@ums.ac.id" class="text-primary small">smhws@ums.ac.id</a>
          </div>
        </div>

        <!-- Emergency -->
        <div class="rounded-3 p-4" style="background:rgba(240,165,0,.1);border:1px solid rgba(240,165,0,.3);">
          <h6 class="fw-bold mb-2">
            <i class="ti tabler-alert-triangle text-warning me-2"></i>Kondisi Darurat
          </h6>
          <p class="small mb-1" style="color:#555;">
            Jika kamu mengalami kondisi darurat, hubungi <strong>112</strong> atau langsung ke
            rumah sakit terdekat untuk mendapat penanganan segera.
          </p>
          <p class="small mb-0" style="color:#555;">
            Hotline Nasional Into The Light: <strong>119 ext 8</strong>
          </p>
        </div>
      </div>

      <!-- CTA Daftar Konseling -->
      <div class="col-lg-8 mt-2">
        <div class="rounded-4 p-5 text-center"
             style="background:linear-gradient(135deg,var(--smhws-primary),var(--smhws-secondary));">
          <i class="ti tabler-calendar-event text-white mb-3" style="font-size:2.5rem;opacity:.85;"></i>
          <h4 class="fw-bold text-white mb-2">Siap Mulai Perjalananmu?</h4>
          <p class="mb-4" style="color:rgba(255,255,255,.8);">
            Login untuk membuat janji konseling dengan psikolog SMHWS. Gratis, rahasia, dan mudah.
          </p>
          <a href="<?= base_url('login') ?>" class="btn btn-lg px-6 fw-semibold"
             style="background:#fff;color:var(--smhws-primary);">
            <i class="ti tabler-login me-2"></i>Login &amp; Daftar Konseling
          </a>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- / Kontak -->

<?= $this->section('extra_css') ?>
<style>
  .tim-card:hover {
    transform: translateY(-8px);
  }
  .tim-card:hover .rounded-4 {
    background: #2bb8b3 !important;
  }
  .tim-psikolog-swiper .swiper-pagination-bullet {
    background: var(--smhws-primary);
    opacity: .35;
  }
  .tim-psikolog-swiper .swiper-pagination-bullet-active {
    opacity: 1;
    width: 24px;
    border-radius: 4px;
  }
  .tim-prev:hover, .tim-next:hover {
    background: var(--smhws-primary) !important;
    color: #fff !important;
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  (function () {
    if (typeof Swiper === 'undefined') return;
    new Swiper('.tim-psikolog-swiper', {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: true,
      autoplay: { delay: 4000, disableOnInteraction: false },
      pagination: { el: '.tim-pagination', clickable: true },
      navigation: { prevEl: '.tim-prev', nextEl: '.tim-next' },
      breakpoints: {
        576:  { slidesPerView: 2 },
        768:  { slidesPerView: 3 },
        1024: { slidesPerView: 4 },
      },
    });
  })();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
