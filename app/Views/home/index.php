<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Layanan kesehatan mental dan wellbeing untuk mahasiswa dan civitas akademika Universitas Muhammadiyah Surakarta.<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- =============== HERO =============== -->
<section id="hero">
  <div id="landingHero" class="section-py landing-hero position-relative overflow-hidden">
    <!-- decorative blobs -->
    <div class="position-absolute top-0 end-0 opacity-25" style="width:400px;height:400px;background:radial-gradient(circle,#57c5b6 0%,transparent 70%);transform:translate(30%,-30%);pointer-events:none;"></div>
    <div class="position-absolute bottom-0 start-0 opacity-25" style="width:300px;height:300px;background:radial-gradient(circle,#1a5f7a 0%,transparent 70%);transform:translate(-30%,30%);pointer-events:none;"></div>

    <div class="container">
      <div class="row align-items-center gy-8">
        <div class="col-lg-6">
          <!-- Emergency notice -->
          <div class="smhws-emergency p-3 rounded mb-6 d-inline-flex align-items-center gap-2">
            <i class="ti tabler-heart-handshake text-warning fs-5"></i>
            <span class="small fw-medium text-dark">Butuh bantuan segera? Hubungi <strong>119 ext 8</strong></span>
          </div>

          <h1 class="display-5 fw-extrabold mb-4" style="color:var(--smhws-dark);line-height:1.2;">
            Kesehatan Mental<br>
            <span style="color:var(--smhws-primary);">Kamu Penting</span> untuk Kami
          </h1>
          <p class="lead mb-6" style="color:#4a6378;">
            SMHWS UMS hadir sebagai ruang aman bagi mahasiswa dan civitas akademika untuk mendapatkan
            dukungan psikologis profesional, rahasia, dan bebas stigma.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <a href="#konsultasi" class="btn btn-primary btn-lg px-5">
              <i class="ti tabler-calendar-event me-2"></i>Buat Janji Konseling
            </a>
            <a href="#layanan" class="btn btn-outline-secondary btn-lg px-4">
              <i class="ti tabler-info-circle me-2"></i>Pelajari Layanan
            </a>
          </div>
        </div>
        <div class="col-lg-6 text-center">
          <!-- Illustrated stats row -->
          <div class="row gy-4">
            <div class="col-6">
              <div class="card smhws-stat rounded-3 p-4 h-100 border-0">
                <div class="display-6 fw-extrabold mb-1">1.200+</div>
                <div class="small opacity-75">Mahasiswa Dilayani</div>
              </div>
            </div>
            <div class="col-6">
              <div class="card smhws-stat rounded-3 p-4 h-100 border-0">
                <div class="display-6 fw-extrabold mb-1">8</div>
                <div class="small opacity-75">Konselor Berpengalaman</div>
              </div>
            </div>
            <div class="col-6">
              <div class="card rounded-3 p-4 h-100 border-0 shadow-sm" style="background:#fff;">
                <div class="display-6 fw-extrabold mb-1 text-primary">100%</div>
                <div class="small text-muted">Kerahasiaan Terjaga</div>
              </div>
            </div>
            <div class="col-6">
              <div class="card rounded-3 p-4 h-100 border-0 shadow-sm" style="background:#fff;">
                <div class="display-6 fw-extrabold mb-1 text-primary">Gratis</div>
                <div class="small text-muted">Untuk Sivitas UMS</div>
              </div>
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

    <div class="row g-5">

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-user-heart" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Konseling Individual</h5>
            <p class="text-muted mb-4">
              Sesi privat satu-satu dengan konselor profesional untuk membahas masalah pribadi,
              akademik, atau emosional secara rahasia.
            </p>
            <a href="#konsultasi" class="btn btn-label-primary btn-sm">Daftar Sekarang</a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-users-group" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Konseling Kelompok</h5>
            <p class="text-muted mb-4">
              Sesi kelompok yang dipandu konselor untuk berbagi pengalaman dan saling mendukung
              dalam lingkungan yang aman dan terstruktur.
            </p>
            <a href="#konsultasi" class="btn btn-label-primary btn-sm">Daftar Sekarang</a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-brain" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Asesmen Psikologi</h5>
            <p class="text-muted mb-4">
              Tes dan asesmen psikologis terstandar untuk memahami kondisi kesehatan mental,
              kepribadian, dan potensi diri kamu secara mendalam.
            </p>
            <a href="#konsultasi" class="btn btn-label-primary btn-sm">Daftar Sekarang</a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-school" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Psikoeduasi &amp; Workshop</h5>
            <p class="text-muted mb-4">
              Program edukasi interaktif tentang manajemen stres, kecemasan, mindfulness,
              dan keterampilan kesehatan mental lainnya.
            </p>
            <a href="#kontak" class="btn btn-label-primary btn-sm">Info Jadwal</a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-heart-rate-monitor" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Skrining Kesehatan Mental</h5>
            <p class="text-muted mb-4">
              Pemeriksaan awal kondisi kesehatan mental menggunakan instrumen terstandar
              seperti SRQ, PHQ-9, dan GAD-7 secara gratis.
            </p>
            <a href="#konsultasi" class="btn btn-label-primary btn-sm">Mulai Skrining</a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-6">
        <div class="card smhws-service-card h-100 shadow-sm">
          <div class="card-body p-5">
            <div class="mb-4 text-primary">
              <i class="ti tabler-stethoscope" style="font-size:2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-2">Rujukan Psikiater</h5>
            <p class="text-muted mb-4">
              Jika diperlukan penanganan lebih lanjut, kami akan membantu menghubungkan kamu
              dengan psikiater atau fasilitas kesehatan yang tepat.
            </p>
            <a href="#kontak" class="btn btn-label-primary btn-sm">Hubungi Kami</a>
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
    <div class="row align-items-center gy-8">
      <div class="col-lg-5">
        <!-- Visual block -->
        <div class="position-relative">
          <div class="rounded-4 p-8 text-white" style="background:linear-gradient(135deg,var(--smhws-primary),var(--smhws-secondary));">
            <div class="mb-4">
              <i class="ti tabler-shield-heart" style="font-size:4rem;opacity:.8;"></i>
            </div>
            <h3 class="fw-bold mb-3">Ruang Aman untuk Semua</h3>
            <p class="opacity-75 mb-0">
              SMHWS adalah layanan kesehatan mental resmi UMS yang berkomitmen memberikan
              dukungan profesional, empatik, dan bebas stigma bagi seluruh sivitas akademika.
            </p>
          </div>
          <!-- floating badge -->
          <div class="position-absolute bottom-0 end-0 translate-middle-y me-4 bg-white shadow rounded-3 px-4 py-3 d-flex align-items-center gap-2">
            <i class="ti tabler-certificate text-primary fs-4"></i>
            <div>
              <div class="fw-bold text-dark small">Terakreditasi</div>
              <div class="text-muted" style="font-size:.7rem;">Himpsi &amp; Kemenkes</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <span class="badge bg-label-primary px-3 py-2 mb-3">Tentang SMHWS</span>
        <h2 class="fw-extrabold mb-4">Mengapa Memilih <span style="color:var(--smhws-primary);">SMHWS UMS?</span></h2>
        <p class="text-muted mb-5">
          Student Mental Health &amp; Wellbeing Support (SMHWS) hadir sebagai respons nyata UMS terhadap
          meningkatnya kebutuhan dukungan psikologis di kalangan mahasiswa. Kami percaya bahwa
          kesehatan mental adalah fondasi keberhasilan akademik dan kehidupan yang berkualitas.
        </p>
        <div class="row g-4">
          <div class="col-sm-6">
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
          <div class="col-sm-6">
            <div class="d-flex gap-3">
              <span class="badge bg-label-primary p-2 flex-shrink-0" style="height:fit-content;">
                <i class="ti tabler-award text-primary fs-5"></i>
              </span>
              <div>
                <h6 class="fw-bold mb-1">Konselor Profesional</h6>
                <p class="text-muted small mb-0">Tim psikolog klinis dan konselor bersertifikat berpengalaman.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
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
          <div class="col-sm-6">
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
    </div>
  </div>
</section>
<!-- / Tentang -->

<!-- =============== TIM KONSELOR =============== -->
<section id="tim" class="section-py">
  <div class="container">
    <div class="text-center mb-2">
      <span class="badge bg-label-primary px-3 py-2">Tim Kami</span>
    </div>
    <h2 class="text-center fw-extrabold mb-2">Konselor &amp; <span style="color:var(--smhws-primary);">Psikolog Kami</span></h2>
    <p class="text-center text-muted mb-10 mx-auto" style="max-width:520px;">
      Tim profesional berpengalaman yang siap mendampingi perjalanan kesehatan mentalmu.
    </p>

    <div class="row justify-content-center g-5">

      <div class="col-lg-3 col-md-6 col-sm-8">
        <div class="card border-0 shadow-sm text-center h-100">
          <div class="card-body p-5">
            <div class="avatar avatar-xl mx-auto mb-4">
              <span class="avatar-initial rounded-circle fw-bold fs-3" style="background:linear-gradient(135deg,var(--smhws-primary),var(--smhws-accent));color:#fff;">SR</span>
            </div>
            <h6 class="fw-bold mb-1">Dr. Siti Rahmawati, M.Psi</h6>
            <p class="text-primary small fw-medium mb-2">Psikolog Klinis</p>
            <p class="text-muted small mb-3">Spesialisasi: Anxiety, Depresi, Trauma</p>
            <span class="badge bg-label-primary">10+ Tahun Pengalaman</span>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-8">
        <div class="card border-0 shadow-sm text-center h-100">
          <div class="card-body p-5">
            <div class="avatar avatar-xl mx-auto mb-4">
              <span class="avatar-initial rounded-circle fw-bold fs-3" style="background:linear-gradient(135deg,var(--smhws-secondary),var(--smhws-accent));color:#fff;">AH</span>
            </div>
            <h6 class="fw-bold mb-1">Ahmad Hidayat, S.Psi, M.Si</h6>
            <p class="text-primary small fw-medium mb-2">Konselor Psikologi</p>
            <p class="text-muted small mb-3">Spesialisasi: Stres Akademik, Hubungan Interpersonal</p>
            <span class="badge bg-label-primary">7 Tahun Pengalaman</span>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-8">
        <div class="card border-0 shadow-sm text-center h-100">
          <div class="card-body p-5">
            <div class="avatar avatar-xl mx-auto mb-4">
              <span class="avatar-initial rounded-circle fw-bold fs-3" style="background:linear-gradient(135deg,#f0a500,#e67e22);color:#fff;">DW</span>
            </div>
            <h6 class="fw-bold mb-1">Dewi Wulandari, M.Psi</h6>
            <p class="text-primary small fw-medium mb-2">Psikolog &amp; Konselor</p>
            <p class="text-muted small mb-3">Spesialisasi: Karir, Identitas Diri, Mindfulness</p>
            <span class="badge bg-label-primary">8 Tahun Pengalaman</span>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-8">
        <div class="card border-0 shadow-sm text-center h-100">
          <div class="card-body p-5">
            <div class="avatar avatar-xl mx-auto mb-4">
              <span class="avatar-initial rounded-circle fw-bold fs-3" style="background:linear-gradient(135deg,#6c5ce7,#a29bfe);color:#fff;">RP</span>
            </div>
            <h6 class="fw-bold mb-1">Reza Pratama, S.Psi</h6>
            <p class="text-primary small fw-medium mb-2">Konselor Mahasiswa</p>
            <p class="text-muted small mb-3">Spesialisasi: Kecanduan Digital, Kesejahteraan Sosial</p>
            <span class="badge bg-label-primary">5 Tahun Pengalaman</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- / Tim -->

<!-- =============== PROSES KONSELING =============== -->
<section class="section-py" style="background:var(--smhws-bg);">
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
        <p class="text-muted small">Konselor akan melakukan asesmen singkat untuk memahami kebutuhanmu dan merencanakan sesi.</p>
      </div>
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-accent);">3</span>
        </div>
        <h6 class="fw-bold mb-2">Sesi Konseling</h6>
        <p class="text-muted small">Jalani sesi konseling tatap muka atau online dengan konselor pilihan secara nyaman dan rahasia.</p>
      </div>
      <div class="col-lg-3 col-md-6 text-center">
        <div class="mb-3">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold fs-4 text-white" style="width:64px;height:64px;background:var(--smhws-accent-warm);">4</span>
        </div>
        <h6 class="fw-bold mb-2">Tindak Lanjut</h6>
        <p class="text-muted small">Bersama konselor, susun rencana pengembangan diri dan jadwal sesi lanjutan sesuai kebutuhan.</p>
      </div>
    </div>
  </div>
</section>
<!-- / Proses -->

<!-- =============== FAQ =============== -->
<section id="faq" class="section-py">
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
                Ya, seluruh layanan SMHWS — termasuk konseling individual, konseling kelompok, dan skrining psikologi —
                sepenuhnya <strong>gratis</strong> bagi mahasiswa aktif dan civitas akademika Universitas Muhammadiyah Surakarta.
              </div>
            </div>
          </div>

          <div class="accordion-item border mb-3 rounded-3 overflow-hidden">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                Apakah informasi yang saya ceritakan akan dirahasiakan?
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted">
                Mutlak. Semua sesi dan informasi yang kamu bagikan dijaga kerahasiaannya sesuai
                <strong>Kode Etik Psikologi Indonesia</strong>. Informasi tidak akan dibagikan kepada siapapun,
                termasuk orang tua, dosen, atau pihak kampus, kecuali dalam situasi darurat yang mengancam keselamatan jiwa.
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
                Kamu bisa mendaftar dengan: (1) mengisi formulir pendaftaran online di bagian Buat Janji halaman ini,
                (2) menghubungi kami via WhatsApp di nomor yang tertera, atau (3) datang langsung ke
                Gedung Siti Walidah Lantai 2 UMS pada jam kerja Senin–Jumat pukul 08.00–16.00 WIB.
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
                Konselor akan mendiskusikan berapa banyak sesi yang dibutuhkan berdasarkan asesmen awal.
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
                bagi mahasiswa yang tidak dapat hadir secara langsung, misalnya karena jarak atau kondisi kesehatan.
                Kamu cukup mencantumkan preferensi "online" saat mendaftar.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
<!-- / FAQ -->

<!-- =============== KONTAK & BUAT JANJI =============== -->
<section id="kontak" class="section-py" style="background:var(--smhws-bg);">
  <div class="container">
    <div class="row g-6">

      <!-- Contact Info -->
      <div class="col-lg-5">
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
        <div class="smhws-emergency p-4 rounded-3">
          <h6 class="fw-bold mb-2"><i class="ti tabler-urgent text-warning me-2"></i>Layanan Darurat 24 Jam</h6>
          <p class="text-dark small mb-1">Hotline Nasional: <strong>119 ext 8</strong></p>
          <p class="text-dark small mb-0">Into The Light Indonesia: <strong>119 ext 8</strong></p>
        </div>
      </div>

      <!-- Appointment Form -->
      <div class="col-lg-7" id="konsultasi">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-6 p-md-8">
            <h4 class="fw-bold mb-2">Buat Janji Konseling</h4>
            <p class="text-muted mb-6">Isi formulir di bawah ini dan tim kami akan menghubungimu dalam 1×24 jam.</p>
            <form>
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" placeholder="Nama lengkap kamu" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">NIM / NIK</label>
                  <input type="text" class="form-control" placeholder="Nomor Induk Mahasiswa" />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" placeholder="email@student.ums.ac.id" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">No. WhatsApp <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Jenis Layanan <span class="text-danger">*</span></label>
                  <select class="form-select" required>
                    <option value="" disabled selected>Pilih layanan</option>
                    <option>Konseling Individual</option>
                    <option>Konseling Kelompok</option>
                    <option>Asesmen Psikologi</option>
                    <option>Skrining Kesehatan Mental</option>
                    <option>Psikoeduasi / Workshop</option>
                    <option>Lainnya</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Mode Sesi <span class="text-danger">*</span></label>
                  <select class="form-select" required>
                    <option value="" disabled selected>Pilih mode</option>
                    <option>Tatap Muka (Offline)</option>
                    <option>Online (Video Call)</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">Ceritakan Kebutuhanmu (opsional)</label>
                  <textarea class="form-control" rows="4" placeholder="Jelaskan secara singkat apa yang ingin kamu diskusikan. Informasi ini sepenuhnya rahasia."></textarea>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="privacyCheck" required />
                    <label class="form-check-label text-muted small" for="privacyCheck">
                      Saya memahami bahwa informasi yang saya berikan akan dijaga kerahasiaannya sesuai kode etik psikologi.
                    </label>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="ti tabler-send me-2"></i>Kirim Permintaan Janji
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- / Kontak -->

<?= $this->endSection() ?>
