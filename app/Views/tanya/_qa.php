<?php
/**
 * Tampilan pertanyaan + jawaban (dipakai mahasiswa, psikolog, admin).
 * Variabel: $row, $tampilNama (bool, tampilkan nama penanya)
 */
helper('tanggal');
use App\Models\PertanyaanPsikologModel as P;
$tampilNama = $tampilNama ?? false;
?>
<div class="card shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
      <?= P::badge($row['status']) ?>
      <?php if ($row['topik_nama']): ?><span class="badge bg-label-secondary"><?= esc($row['topik_nama']) ?></span><?php endif ?>
      <?php if ($row['izin_publikasi']): ?><span class="badge bg-label-info" title="Penanya mengizinkan ditampilkan anonim di SMHWS Learn"><i class="ti tabler-world me-1"></i>Boleh dipublikasikan anonim</span><?php endif ?>
    </div>
    <h5 class="fw-bold mb-1"><?= esc($row['judul']) ?></h5>
    <div class="text-muted small mb-3">
      <?= $tampilNama ? esc($row['nama']) . ' · ' : '' ?>
      <?= $row['jenis_kelamin'] === 'laki-laki' ? 'Laki-laki' : 'Perempuan' ?> · Semester <?= (int) $row['semester'] ?>
      <?php if ($row['prodi']): ?> · <?= esc($row['prodi']) ?><?php endif ?>
      · <?= esc(tanggal_id($row['created_at'], true)) ?>
    </div>
    <div style="white-space:pre-line;line-height:1.7;"><?= esc($row['pertanyaan']) ?></div>
  </div>
</div>

<?php if ($row['status'] === 'dijawab'): ?>
  <div class="card shadow-sm mb-4" style="border-left:4px solid #2d9b6e;">
    <div class="card-body p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <?php if (! empty($row['konselor_foto'])): ?>
          <img src="<?= base_url($row['konselor_foto']) ?>" alt="" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;">
        <?php else: ?>
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-success" style="width:44px;height:44px;"><i class="ti tabler-user-heart"></i></div>
        <?php endif ?>
        <div>
          <div class="fw-semibold"><?= esc(P::namaPsikolog($row)) ?></div>
          <div class="text-muted small">Psikolog SMHWS · <?= esc(tanggal_id($row['dijawab_at'], true)) ?></div>
        </div>
      </div>
      <div style="white-space:pre-line;line-height:1.75;"><?= esc($row['jawaban']) ?></div>
    </div>
  </div>
<?php elseif ($row['status'] === 'ditolak'): ?>
  <div class="alert alert-secondary">
    <div class="fw-semibold mb-1">Pertanyaan tidak dapat dijawab melalui layanan ini</div>
    <div style="white-space:pre-line;"><?= esc($row['alasan_ditolak']) ?></div>
  </div>
<?php endif ?>
