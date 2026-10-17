<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($konten['judul']) ?><?= $this->endSection() ?>

<?= $this->section('meta_description') ?><?= esc($konten['ringkasan'] ?: mb_strimwidth(strip_tags((string) $konten['isi']), 0, 150, '…')) ?><?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
  .learn-isi { font-size: 1.02rem; line-height: 1.8; color: #333; }
  .learn-isi h2, .learn-isi h3, .learn-isi h4 { margin: 1.6rem 0 .8rem; color: var(--smhws-dark); font-weight: 700; }
  .learn-isi img { max-width: 100%; height: auto; border-radius: .5rem; margin: 1rem 0; }
  .learn-isi blockquote { border-left: 4px solid var(--smhws-primary); padding: .5rem 1rem; margin: 1rem 0; background: #f6fafb; color: #555; }
  .learn-isi ul, .learn-isi ol { padding-left: 1.4rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
use App\Models\LearnKontenModel as K;
$k = $konten;
?>
<div class="container" style="padding-top:7rem;padding-bottom:4rem;">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <a href="<?= base_url('learn' . ($k['tipe'] ? '?tipe=' . $k['tipe'] : '')) ?>" class="text-muted text-decoration-none small">
        <i class="ti tabler-arrow-left me-1"></i>SMHWS Learn
      </a>

      <div class="d-flex gap-2 mt-3 mb-2 flex-wrap">
        <span class="badge bg-label-primary"><i class="ti <?= K::TIPE_ICON[$k['tipe']] ?> me-1"></i><?= esc(K::TIPE[$k['tipe']]) ?></span>
        <?php if ($k['topik_nama']): ?>
          <a href="<?= base_url('learn?topik=' . rawurlencode($k['topik_slug'])) ?>" class="badge bg-label-secondary"><?= esc($k['topik_nama']) ?></a>
        <?php endif ?>
      </div>
      <h1 class="fw-bold mb-2" style="color:var(--smhws-dark);font-size:2rem;"><?= esc($k['judul']) ?></h1>
      <div class="text-muted small mb-4">
        <?= esc(tanggal_id($k['diterbitkan_at'] ?: $k['created_at'])) ?>
        <?php if ($k['penulis']): ?> · <?= esc($k['penulis']) ?><?php endif ?>
        · <i class="ti tabler-eye"></i> <?= number_format((int) $k['dilihat']) ?>
      </div>

      <?php if ($k['tipe'] === 'artikel'): ?>
        <?php if ($k['thumbnail']): ?>
          <img src="<?= base_url($k['thumbnail']) ?>" alt="" class="w-100 rounded mb-4" style="aspect-ratio:16/9;object-fit:cover;">
        <?php endif ?>
        <!-- Isi sudah disanitasi saat disimpan (App\Libraries\HtmlSanitizer) -->
        <div class="learn-isi"><?= $k['isi'] ?></div>

      <?php elseif ($k['tipe'] === 'video'): ?>
        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm mb-4">
          <iframe src="https://www.youtube-nocookie.com/embed/<?= esc($k['youtube_id'], 'url') ?>" title="<?= esc($k['judul']) ?>"
                  allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>

      <?php elseif ($k['tipe'] === 'poster'): ?>
        <div class="text-center mb-3 p-3 rounded" style="background:#f6fafb;">
          <img src="<?= base_url($k['file']) ?>" alt="<?= esc($k['judul']) ?>" class="img-fluid rounded shadow-sm" style="max-height:85vh;">
        </div>
        <div class="text-center mb-4">
          <a href="<?= base_url($k['file']) ?>" download class="btn btn-outline-primary btn-sm"><i class="ti tabler-download me-1"></i>Unduh Gambar</a>
        </div>

      <?php elseif ($k['tipe'] === 'media'): ?>
        <div class="d-flex flex-wrap gap-2 mb-4">
          <?php if ($k['file']): ?>
            <a href="<?= base_url($k['file']) ?>" download class="btn btn-primary"><i class="ti tabler-download me-1"></i>Unduh PDF</a>
          <?php endif ?>
          <?php if ($k['link_eksternal']): ?>
            <a href="<?= esc($k['link_eksternal']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary"><i class="ti tabler-external-link me-1"></i>Buka Materi</a>
          <?php endif ?>
        </div>
        <?php if ($k['file']): ?>
          <div class="ratio rounded overflow-hidden shadow-sm mb-4 d-none d-md-block" style="--bs-aspect-ratio:130%;">
            <iframe src="<?= base_url($k['file']) ?>" title="<?= esc($k['judul']) ?>"></iframe>
          </div>
        <?php endif ?>
      <?php endif ?>

      <?php if ($k['tipe'] !== 'artikel' && $k['isi']): ?>
        <div class="learn-isi"><?= $k['isi'] ?></div>
      <?php endif ?>
    </div>
  </div>

  <?php if ($terkait): ?>
    <div class="mt-5 pt-4 border-top">
      <h5 class="fw-bold mb-4" style="color:var(--smhws-dark);">Konten Terkait</h5>
      <div class="row g-4"><?php foreach ($terkait as $row): ?><?= view('learn/_kartu', ['row' => $row]) ?><?php endforeach ?></div>
    </div>
  <?php endif ?>
</div>
<?= $this->endSection() ?>
