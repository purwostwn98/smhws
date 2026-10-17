<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>SMHWS Learn<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Artikel, video, poster/infografis, dan media belajar untuk kesehatan mental dan wellbeing mahasiswa.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Models\LearnKontenModel as K;

$qs = function (array $ubah) use ($tipe, $topik, $q) {
    $param = array_merge(['tipe' => $tipe, 'topik' => $topik['slug'] ?? null, 'q' => $q ?: null], $ubah);
    $param = array_filter($param, fn($v) => $v !== null && $v !== '');
    return base_url('learn' . ($param ? '?' . http_build_query($param) : ''));
};
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="text-center mb-4">
    <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">SMHWS Learn</h2>
    <p class="text-muted mb-0">Artikel, video, poster/infografis, dan media belajar untuk kesehatan mental &amp; wellbeing-mu.</p>
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
    <a href="<?= $qs(['tipe' => null]) ?>" class="btn btn-sm <?= $tipe === null ? 'btn-primary' : 'btn-label-primary' ?>">Semua</a>
    <?php foreach (K::TIPE as $kode => $label): ?>
      <a href="<?= $qs(['tipe' => $kode]) ?>" class="btn btn-sm <?= $tipe === $kode ? 'btn-primary' : 'btn-label-primary' ?>">
        <i class="ti <?= K::TIPE_ICON[$kode] ?> me-1"></i><?= $label ?>
      </a>
    <?php endforeach ?>
  </div>

  <form method="get" action="<?= base_url('learn') ?>" class="row g-2 justify-content-center mb-5">
    <?php if ($tipe): ?><input type="hidden" name="tipe" value="<?= esc($tipe) ?>"><?php endif ?>
    <?php if ($daftarTopik): ?>
      <div class="col-md-3 col-6">
        <select name="topik" class="form-select" onchange="this.form.submit()">
          <option value="">Semua topik</option>
          <?php foreach ($daftarTopik as $t): ?>
            <option value="<?= esc($t['slug']) ?>" <?= ($topik['slug'] ?? '') === $t['slug'] ? 'selected' : '' ?>><?= esc($t['nama']) ?></option>
          <?php endforeach ?>
        </select>
      </div>
    <?php endif ?>
    <div class="col-md-4 col-6">
      <div class="input-group">
        <input type="search" name="q" class="form-control" placeholder="Cari judul..." value="<?= esc($q) ?>">
        <button class="btn btn-primary" type="submit"><i class="ti tabler-search"></i></button>
      </div>
    </div>
  </form>

  <?php if ($list): ?>
    <div class="row g-4"><?php foreach ($list as $row): ?><?= view('learn/_kartu', ['row' => $row]) ?><?php endforeach ?></div>
    <div class="d-flex justify-content-center mt-5"><?= $pager->links() ?></div>
  <?php else: ?>
    <div class="text-center text-muted py-5">
      <i class="ti tabler-books" style="font-size:2.5rem;display:block;opacity:.4;"></i>
      <?= ($tipe || $topik || $q) ? 'Tidak ada konten yang cocok dengan pencarianmu.' : 'Belum ada konten.' ?>
    </div>
  <?php endif ?>

  <div class="text-center mt-5">
    <a href="<?= base_url() ?>" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Kembali ke Beranda</a>
  </div>
</div>
<?= $this->endSection() ?>
