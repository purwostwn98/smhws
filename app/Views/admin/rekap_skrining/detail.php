<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Detail Skrining<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('tanggal');
use App\Models\DassAssessmentModel as Dass;
$desk = \App\Libraries\DassDeskripsi::susun($row);
$skala = ['Tidak pernah', 'Kadang-kadang', 'Cukup sering', 'Sering sekali'];
$subLabel = ['D' => 'Depresi', 'A' => 'Kecemasan', 'S' => 'Stres'];
?>
<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/rekap-skrining') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;"><i class="ti tabler-arrow-left me-1"></i>Rekap Skrining</a>
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

<div class="row g-3 mb-4">
  <?php foreach ($desk['subskala'] as $s): ?>
    <div class="col-md-4">
      <div class="h-100 rounded-3 p-3 bg-white shadow-sm" style="border-top:3px solid <?= $s['color'] ?>;">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="ti <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;"></i><span class="fw-semibold"><?= $s['label'] ?></span>
          <span class="badge ms-auto <?= Dass::badgeKelas($s['kategori']) ?>"><?= Dass::labelKategori($s['kategori']) ?></span>
        </div>
        <div class="fw-bold" style="font-size:2rem;color:<?= $s['color'] ?>;line-height:1;"><?= (int) $s['skor'] ?></div>
        <div class="small text-muted mt-2"><?= esc($s['interpretasi']) ?></div>
      </div>
    </div>
  <?php endforeach ?>
</div>

<div class="card shadow-sm">
  <div class="card-header"><h6 class="mb-0 fw-bold">Jawaban per Pernyataan</h6></div>
  <div class="table-responsive">
    <table class="table mb-0" style="font-size:.85rem;">
      <thead style="background:#f8f9fa;"><tr><th class="px-3">No</th><th>Pernyataan</th><th>Subskala</th><th>Jawaban</th></tr></thead>
      <tbody>
        <?php foreach ($jawaban as $j): ?>
          <tr>
            <td class="px-3"><?= (int) $j['nomor_item'] ?></td>
            <td><?= esc($j['pernyataan']) ?></td>
            <td><?= $subLabel[$j['subscale']] ?? $j['subscale'] ?></td>
            <td><span class="fw-semibold"><?= (int) $j['jawaban'] ?></span> <span class="text-muted">· <?= $skala[(int) $j['jawaban']] ?? '' ?></span></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
