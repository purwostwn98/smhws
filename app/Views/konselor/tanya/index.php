<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Pertanyaan Masuk<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper('tanggal'); ?>
<div class="mb-4">
  <h4 class="fw-bold mb-1" style="color:#1a2b40;">Pertanyaan Masuk</h4>
  <p class="text-muted mb-0" style="font-size:.875rem;">Pertanyaan Ask Our Psychologist yang ditugaskan admin kepada Anda.</p>
</div>

<?= $this->include('tanya/_flash') ?>

<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link <?= $tab === 'belum' ? 'active' : '' ?>" href="<?= base_url('konselor/tanya') ?>">Belum Dijawab <span class="badge bg-white text-primary ms-1"><?= $jumlah['ditugaskan'] ?></span></a></li>
  <li class="nav-item"><a class="nav-link <?= $tab === 'dijawab' ? 'active' : '' ?>" href="<?= base_url('konselor/tanya?tab=dijawab') ?>">Sudah Dijawab <span class="badge bg-white text-primary ms-1"><?= $jumlah['dijawab'] ?></span></a></li>
</ul>

<?php if (empty($list)): ?>
  <div class="card shadow-sm"><div class="card-body text-center text-muted py-5">
    <i class="ti tabler-inbox" style="font-size:2.5rem;display:block;opacity:.35;"></i>
    <?= $tab === 'belum' ? 'Tidak ada pertanyaan yang menunggu jawaban.' : 'Belum ada pertanyaan yang Anda jawab.' ?>
  </div></div>
<?php else: ?>
  <div class="d-flex flex-column gap-3">
    <?php foreach ($list as $q): ?>
      <a href="<?= base_url('konselor/tanya/' . $q['id']) ?>" class="card shadow-sm text-body text-decoration-none">
        <div class="card-body d-flex gap-3 align-items-start">
          <div class="flex-grow-1 min-width-0">
            <div class="fw-semibold mb-1"><?= esc($q['judul']) ?></div>
            <div class="text-muted small">
              <?= $q['jenis_kelamin'] === 'laki-laki' ? 'Laki-laki' : 'Perempuan' ?> · Semester <?= (int) $q['semester'] ?>
              <?php if ($q['topik_nama']): ?> · <?= esc($q['topik_nama']) ?><?php endif ?>
              · <?= $tab === 'belum' ? 'Ditugaskan ' . esc(tanggal_id($q['ditugaskan_at'], true)) : 'Dijawab ' . esc(tanggal_id($q['dijawab_at'], true)) ?>
            </div>
          </div>
          <i class="ti tabler-chevron-right text-muted mt-2"></i>
        </div>
      </a>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
