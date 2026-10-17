<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Ajukan Pertanyaan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php use App\Models\PertanyaanPsikologModel as P; ?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('tanya') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Ask Our Psychologist</a>
  <span class="text-muted">/</span><span class="fw-semibold" style="font-size:.875rem;">Ajukan Pertanyaan</span>
</div>

<?= $this->include('tanya/_flash') ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card shadow-sm mb-4" style="border-left:4px solid var(--bs-primary);">
      <div class="card-body">
        <h5 class="fw-bold mb-2"><i class="ti tabler-message-question me-2 text-primary"></i>Ask Our Psychologist</h5>
        <p class="mb-0 text-muted" style="line-height:1.7;">
          Di sini kamu bisa bertanya tentang apa pun yang sedang kamu pikirkan atau rasakan, mulai dari pengembangan diri,
          perkuliahan, pertemanan, stres, overthinking, kepercayaan diri, hingga berbagai hal yang mungkin sulit kamu ceritakan
          kepada orang lain. Tulis pertanyaanmu secara nyaman dan dapatkan perspektif serta saran dari psikolog SMHWS.
          Nggak perlu menunggu semuanya terasa berat untuk mulai bertanya.
        </p>
      </div>
    </div>

    <div class="alert alert-warning d-flex gap-2" role="alert">
      <i class="ti tabler-alert-triangle mt-1"></i>
      <div style="font-size:.85rem;">
        Layanan ini bukan untuk keadaan darurat. Jika kamu merasa tidak aman atau punya pikiran untuk menyakiti diri sendiri,
        segera <a href="<?= base_url('janji/buat') ?>" class="alert-link">daftar konseling (SMHWS Talk &amp; Care)</a>
        atau hubungi layanan darurat 119.
      </div>
    </div>

    <?php if ($jumlahAktif >= P::MAKS_AKTIF): ?>
      <div class="alert alert-info">Kamu masih punya <?= P::MAKS_AKTIF ?> pertanyaan yang sedang diproses. Kamu bisa bertanya lagi setelah salah satunya dijawab.</div>
    <?php else: ?>
    <form action="<?= base_url('tanya/simpan') ?>" method="post" class="card shadow-sm">
      <?= csrf_field() ?>
      <div class="card-body p-4">
        <h6 class="fw-bold mb-3">Identitas</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" value="<?= esc($user['name'] ?? '') ?>" readonly disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Program Studi</label>
            <input type="text" class="form-control" value="<?= esc(trim(($user['prodi'] ?? '') . ($user['fakultas'] ? ' · ' . $user['fakultas'] : ''), ' ·')) ?>" readonly disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
            <?php foreach (['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan'] as $val => $lbl): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_<?= $val ?>" value="<?= $val ?>" required <?= old('jenis_kelamin') === $val ? 'checked' : '' ?>>
                <label class="form-check-label" for="jk_<?= $val ?>"><?= $lbl ?></label>
              </div>
            <?php endforeach ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="semester">Semester <span class="text-danger">*</span></label>
            <select name="semester" id="semester" class="form-select" required>
              <option value="">Pilih</option>
              <?php for ($i = 1; $i <= 14; $i++): ?>
                <option value="<?= $i ?>" <?= (string) old('semester') === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
              <?php endfor ?>
            </select>
          </div>
        </div>

        <h6 class="fw-bold mb-3">Pertanyaan</h6>
        <?php if ($topik): ?>
          <div class="mb-3">
            <label class="form-label" for="topik_id">Topik <small class="text-muted">(opsional)</small></label>
            <select name="topik_id" id="topik_id" class="form-select">
              <option value="">Pilih topik</option>
              <?php foreach ($topik as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (string) old('topik_id') === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['nama']) ?></option>
              <?php endforeach ?>
            </select>
          </div>
        <?php endif ?>
        <div class="mb-3">
          <label class="form-label" for="judul">Inti Pertanyaan <span class="text-danger">*</span></label>
          <input type="text" name="judul" id="judul" class="form-control" maxlength="255" required value="<?= esc(old('judul')) ?>"
                 placeholder="mis. Bagaimana cara mengatasi overthinking?">
          <div class="form-text">Contoh: Bagaimana mengelola waktu untuk studi dan keorganisasian? Bagaimana agar tidak menunda-nunda mengerjakan tugas? Bagaimana mengatasi perasaan rendah diri?</div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="pertanyaan">Ceritakan Lebih Lengkap <span class="text-danger">*</span></label>
          <textarea name="pertanyaan" id="pertanyaan" class="form-control" rows="7" maxlength="5000" required
                    placeholder="Ceritakan apa yang kamu rasakan atau alami, sejauh kamu nyaman."><?= esc(old('pertanyaan')) ?></textarea>
        </div>

        <div class="form-check p-3 rounded mb-4" style="background:#f6fafb;">
          <input class="form-check-input ms-0 me-2" type="checkbox" name="izin_publikasi" id="izin_publikasi" value="1" <?= old('izin_publikasi') ? 'checked' : '' ?>>
          <label class="form-check-label" for="izin_publikasi" style="font-size:.88rem;">
            Saya ingin pertanyaan saya ditampilkan secara anonim (tanpa nama) di halaman "SMHWS Learn" agar dapat membantu
            mahasiswa lain yang memiliki pertanyaan yang sama dengan saya.
          </label>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <a href="<?= base_url('tanya') ?>" class="btn btn-label-secondary">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="ti tabler-send me-1"></i>Kirim Pertanyaan</button>
        </div>
      </div>
    </form>
    <?php endif ?>
  </div>
</div>
<?= $this->endSection() ?>
