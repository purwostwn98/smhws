<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Detail Skrining<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
use App\Libraries\PolaInternet as PI;
$warna = PI::warna($row['kategori']);
?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/rekap-skrining/internet') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Rekap Pola Penggunaan Internet</a>
  <span class="text-muted">/</span><span class="fw-semibold" style="font-size:.875rem;">Detail</span>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><div class="small text-muted">Tanggal</div><?= esc(tanggal_id($row['created_at'], true)) ?></div>
      <div class="col-md-2"><div class="small text-muted">Jenis Kelamin</div><?= $row['jenis_kelamin'] === 'laki-laki' ? 'Laki-laki' : 'Perempuan' ?></div>
      <div class="col-md-2"><div class="small text-muted">Semester</div><?= (int) $row['semester'] ?></div>
      <div class="col-md-5"><div class="small text-muted">Program Studi</div><?= esc($row['nama_prodi']) ?> <span class="text-muted">· <?= esc($row['nama_fakultas']) ?></span></div>
    </div>
  </div>
</div>

<div class="card shadow-sm mb-4" style="border-top:3px solid <?= $warna ?>;">
  <div class="card-body d-flex flex-wrap gap-4 align-items-center">
    <div>
      <div class="small text-muted">Skor Total</div>
      <div class="fw-bold" style="font-size:2.2rem;line-height:1;color:<?= $warna ?>;"><?= (int) $row['skor_total'] ?> <span class="fs-6 text-muted fw-normal">/ <?= PI::SKOR_MAKS ?></span></div>
    </div>
    <div>
      <div class="small text-muted">Kategori</div>
      <span class="badge fs-6" style="background:<?= $warna ?>;"><?= PI::labelKategori($row['kategori']) ?></span>
    </div>
    <div class="flex-grow-1 small text-muted" style="min-width:240px;"><?= esc(PI::rekomendasi($row['kategori'])) ?></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header"><h6 class="mb-0 fw-bold">Jawaban per Pertanyaan</h6></div>
  <div class="table-responsive">
    <table class="table mb-0" style="font-size:.85rem;">
      <thead style="background:#f8f9fa;"><tr><th class="px-3">No</th><th>Pertanyaan</th><th>Jawaban</th></tr></thead>
      <tbody>
        <?php foreach (PI::BUTIR as $no => $teks): $j = $jawaban[$no] ?? null; ?>
          <tr>
            <td class="px-3"><?= $no ?></td>
            <td><?= esc($teks) ?></td>
            <td class="text-nowrap"><?php if ($j !== null): ?><span class="fw-semibold"><?= (int) $j ?></span> <span class="text-muted">· <?= PI::SKALA[(int) $j] ?? '' ?></span><?php else: ?>—<?php endif ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
