<?php
/** Kartu satu konten Learn. Variabel: $row */
use App\Models\LearnKontenModel as K;
$img = K::gambarKartu($row);
$url = base_url('learn/' . $row['slug']);
?>
<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
  <div class="card h-100 shadow-sm overflow-hidden">
    <a href="<?= $url ?>" class="d-block position-relative" style="aspect-ratio:16/9;background:#eef4f7;">
      <?php if ($img): ?>
        <img src="<?= esc($img) ?>" alt="<?= esc($row['judul']) ?>" loading="lazy" class="w-100 h-100"
             style="object-fit:<?= $row['tipe'] === 'poster' ? 'contain' : 'cover' ?>;">
      <?php else: ?>
        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="color:var(--smhws-primary);">
          <i class="ti <?= K::TIPE_ICON[$row['tipe']] ?>" style="font-size:2.5rem;opacity:.6;"></i>
        </div>
      <?php endif ?>
      <?php if ($row['tipe'] === 'video'): ?>
        <span class="position-absolute top-50 start-50 translate-middle d-flex align-items-center justify-content-center rounded-circle"
              style="width:3rem;height:3rem;background:rgba(0,0,0,.55);color:#fff;"><i class="ti tabler-player-play-filled"></i></span>
      <?php endif ?>
    </a>
    <div class="card-body d-flex flex-column">
      <div class="d-flex gap-2 mb-2 flex-wrap">
        <span class="badge bg-label-primary"><i class="ti <?= K::TIPE_ICON[$row['tipe']] ?> me-1"></i><?= esc(K::TIPE[$row['tipe']]) ?></span>
        <?php if (! empty($row['topik_nama'])): ?><span class="badge bg-label-secondary"><?= esc($row['topik_nama']) ?></span><?php endif ?>
      </div>
      <h6 class="fw-bold mb-2"><a href="<?= $url ?>" class="text-body"><?= esc($row['judul']) ?></a></h6>
      <?php if ($row['ringkasan']): ?>
        <p class="text-muted small mb-0 flex-grow-1"><?= esc(mb_strimwidth($row['ringkasan'], 0, 120, '…')) ?></p>
      <?php endif ?>
    </div>
  </div>
</div>
