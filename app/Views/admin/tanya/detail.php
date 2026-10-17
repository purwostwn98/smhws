<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Detail Pertanyaan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Models\KonselorModel;
use App\Models\PertanyaanPsikologModel as P;
$bisaTugaskan = in_array($row['status'], ['menunggu', 'ditugaskan'], true);
$bisaTerbit   = $row['status'] === 'dijawab' && $row['izin_publikasi'];
?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/tanya?status=' . $row['status']) ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Ask Our Psychologist</a>
  <span class="text-muted">/</span><span class="fw-semibold" style="font-size:.875rem;">Detail</span>
</div>

<?= $this->include('tanya/_flash') ?>

<div class="row g-4">
  <div class="col-lg-8">
    <?= view('tanya/_qa', ['row' => $row, 'tampilNama' => true]) ?>

    <?php if ($row['status'] === 'dijawab' && ! $row['izin_publikasi']): ?>
      <div class="alert alert-secondary"><i class="ti tabler-lock me-1"></i>Penanya tidak mengizinkan tanya-jawab ini dipublikasikan, sehingga tidak dapat diterbitkan ke SMHWS Learn.</div>
    <?php endif ?>

    <?php if ($bisaTerbit):
      $sudah = (bool) $learn;
      $judulAwal = $sudah ? $learn['judul'] : $row['judul'];
    ?>
      <div class="card shadow-sm">
        <div class="card-header py-3" style="border-left:4px solid #2d9b6e;background:rgba(45,155,110,.05);">
          <h6 class="fw-bold mb-0" style="color:#2d9b6e;"><i class="ti tabler-world me-2"></i><?= $sudah ? 'Perbarui di SMHWS Learn' : 'Terbitkan ke SMHWS Learn' ?></h6>
        </div>
        <div class="card-body pt-4">
          <?php if ($sudah): ?>
            <p class="small mb-3">
              Status di Learn: <span class="badge <?= $learn['status'] === 'terbit' ? 'bg-label-success' : 'bg-label-secondary' ?>"><?= $learn['status'] === 'terbit' ? 'Terbit' : 'Draft' ?></span>
              <?php if ($learn['status'] === 'terbit'): ?> · <a href="<?= base_url('learn/' . $learn['slug']) ?>" target="_blank">Lihat halaman</a><?php endif ?>
              · Untuk menyembunyikan, jadikan draft dari menu <a href="<?= base_url('admin/learn?tipe=tanya_jawab') ?>">SMHWS Learn</a>.
            </p>
          <?php endif ?>
          <p class="text-muted small">Teks di bawah terisi otomatis <strong>tanpa nama penanya</strong>. Sunting bila perlu untuk menghapus detail yang bisa mengungkap identitas (nama orang, tempat, organisasi).
            Identitas yang ditampilkan: "Mahasiswa semester <?= (int) $row['semester'] ?><?= $row['fakultas'] ? ', ' . esc($row['fakultas']) : '' ?>".</p>
          <form action="<?= base_url('admin/tanya/terbitkan/' . $row['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label fw-semibold">Judul</label>
              <input type="text" name="judul" class="form-control" maxlength="255" required value="<?= esc(old('judul', $judulAwal)) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Topik</label>
              <select name="topik_id" class="form-select">
                <option value="">Tanpa topik</option>
                <?php foreach ($topik as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= (string) old('topik_id', $learn['topik_id'] ?? $row['topik_id']) === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['nama']) ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Pertanyaan (anonim)</label>
              <textarea name="pertanyaan" class="form-control" rows="6" required><?= esc(old('pertanyaan', $row['pertanyaan'])) ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Jawaban</label>
              <textarea name="jawaban" class="form-control" rows="8" required><?= esc(old('jawaban', $row['jawaban'])) ?></textarea>
              <div class="form-text">Ditampilkan dengan nama psikolog: <strong><?= esc(P::namaPsikolog($row)) ?></strong>.</div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success"><i class="ti tabler-world-upload me-1"></i><?= $sudah ? 'Perbarui & Terbitkan' : 'Terbitkan ke Learn' ?></button>
            </div>
          </form>
        </div>
      </div>
    <?php endif ?>
  </div>

  <div class="col-lg-4">
    <?php if ($bisaTugaskan): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3" style="border-left:4px solid #696cff;background:#696cff0a;">
          <h6 class="fw-bold mb-0" style="color:#696cff;"><i class="ti tabler-user-check me-2"></i><?= $row['konselor_id'] ? 'Ganti Psikolog' : 'Tugaskan Psikolog' ?></h6>
        </div>
        <div class="card-body pt-4">
          <?php if ($row['konselor_id']): ?>
            <p class="small mb-3">Saat ini: <strong><?= esc(P::namaPsikolog($row)) ?></strong></p>
          <?php endif ?>
          <form action="<?= base_url('admin/tanya/tugaskan/' . $row['id']) ?>" method="post">
            <?= csrf_field() ?>
            <select name="konselor_id" class="form-select mb-3" required>
              <option value="">Pilih psikolog</option>
              <?php foreach ($konselorList as $k): ?>
                <option value="<?= $k['id'] ?>" <?= (string) $row['konselor_id'] === (string) $k['id'] ? 'selected' : '' ?>><?= esc(KonselorModel::namaLengkap($k)) ?></option>
              <?php endforeach ?>
            </select>
            <button type="submit" class="btn btn-primary w-100">Simpan</button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="fw-bold mb-2">Tolak Pertanyaan</h6>
          <p class="text-muted small mb-2">Misalnya bukan ranah layanan ini, atau perlu ditangani lewat konseling langsung. Alasan akan dibaca mahasiswa.</p>
          <form action="<?= base_url('admin/tanya/tolak/' . $row['id']) ?>" method="post" onsubmit="return confirm('Tolak pertanyaan ini?')">
            <?= csrf_field() ?>
            <textarea name="alasan_ditolak" class="form-control mb-2" rows="3" required placeholder="Alasan / arahan untuk mahasiswa"></textarea>
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Tolak</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="card shadow-sm">
        <div class="card-body small">
          <div class="mb-2"><span class="text-muted">Status:</span> <?= P::badge($row['status']) ?></div>
          <?php if ($row['konselor_id']): ?><div class="mb-2"><span class="text-muted">Psikolog:</span> <?= esc(P::namaPsikolog($row)) ?></div><?php endif ?>
          <div><span class="text-muted">Izin publikasi:</span> <?= $row['izin_publikasi'] ? 'Ya' : 'Tidak' ?></div>
        </div>
      </div>
    <?php endif ?>
  </div>
</div>
<?= $this->endSection() ?>
