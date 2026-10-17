<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Jawab Pertanyaan<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('konselor/tanya') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Pertanyaan Masuk</a>
  <span class="text-muted">/</span><span class="fw-semibold" style="font-size:.875rem;">Detail</span>
</div>

<?= $this->include('tanya/_flash') ?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <?php
      // Psikolog melihat identitas tanpa nama; jawaban ditampilkan di form di bawah, bukan di kartu.
      $tampil = $row; $tampil['status'] = $row['status'] === 'dijawab' ? 'ditugaskan' : $row['status'];
    ?>
    <?= view('tanya/_qa', ['row' => $tampil, 'tampilNama' => false]) ?>

    <?php if (! empty($row['learn_konten_id'])): ?>
      <div class="alert alert-info">Jawaban ini sudah diterbitkan admin di SMHWS Learn, sehingga tidak dapat diubah dari sini.</div>
      <div class="card shadow-sm"><div class="card-body" style="white-space:pre-line;line-height:1.75;"><?= esc($row['jawaban']) ?></div></div>
    <?php else: ?>
      <form action="<?= base_url('konselor/tanya/jawab/' . $row['id']) ?>" method="post" class="card shadow-sm">
        <?= csrf_field() ?>
        <div class="card-body p-4">
          <label class="form-label fw-semibold" for="jawaban"><?= $row['status'] === 'dijawab' ? 'Jawaban Anda' : 'Tulis Jawaban' ?></label>
          <textarea name="jawaban" id="jawaban" class="form-control" rows="10" maxlength="10000" required
                    placeholder="Berikan perspektif dan saran yang hangat serta praktis..."><?= esc(old('jawaban', $row['jawaban'] ?? '')) ?></textarea>
          <div class="form-text">Nama Anda akan ditampilkan sebagai psikolog yang menjawab.<?= $row['izin_publikasi'] ? ' Penanya mengizinkan tanya-jawab ini ditampilkan anonim di SMHWS Learn, jadi hindari menyebut detail identitas.' : '' ?></div>
          <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti tabler-send me-1"></i><?= $row['status'] === 'dijawab' ? 'Perbarui Jawaban' : 'Kirim Jawaban' ?></button>
          </div>
        </div>
      </form>
    <?php endif ?>
  </div>
</div>
<?= $this->endSection() ?>
