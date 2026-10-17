<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>SMHWS Learn<?= $this->endSection() ?>
<?php
helper('tanggal');
use App\Models\LearnKontenModel as K;

$th   = 'px-4 py-3 fw-semibold text-muted';
$thSt = 'font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;';
$chip = function (?string $t, string $label) use ($tipe, $status) {
    $qs = http_build_query(array_filter(['tipe' => $t, 'status' => $status]));
    return '<a href="' . base_url('admin/learn' . ($qs ? '?' . $qs : '')) . '" class="btn btn-sm '
        . ($tipe === $t ? 'btn-primary' : 'btn-label-primary') . '">' . esc($label) . '</a>';
};
?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1" style="color:#1a2b40;">SMHWS Learn</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">Kelola artikel, video, poster/infografis, dan media belajar.</p>
  </div>
  <div class="dropdown">
    <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
      <i class="ti tabler-plus"></i>Tambah Konten
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <?php foreach (K::TIPE_MANUAL as $kode): $label = K::TIPE[$kode]; ?>
        <li><a class="dropdown-item" href="<?= base_url('admin/learn/buat?tipe=' . $kode) ?>"><i class="ti <?= K::TIPE_ICON[$kode] ?> me-2"></i><?= $label ?></a></li>
      <?php endforeach ?>
    </ul>
  </div>
</div>

<?= $this->include('admin/learn/_flash') ?>

<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
  <?= $chip(null, 'Semua') ?>
  <?php foreach (K::TIPE as $kode => $label): ?><?= $chip($kode, $label) ?><?php endforeach ?>
  <form method="get" class="ms-auto">
    <?php if ($tipe): ?><input type="hidden" name="tipe" value="<?= esc($tipe) ?>"><?php endif ?>
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">Semua status</option>
      <option value="terbit" <?= $status === 'terbit' ? 'selected' : '' ?>>Terbit</option>
      <option value="draft"  <?= $status === 'draft'  ? 'selected' : '' ?>>Draft</option>
    </select>
  </form>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($list)): ?>
      <div class="text-center py-5 text-muted">
        <i class="ti tabler-books" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.35;"></i>
        <div style="font-size:.9rem;">Belum ada konten.</div>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.875rem;">
          <thead style="background:#f8f9fa;border-bottom:2px solid #e9ecef;">
            <tr>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Konten</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Tipe</th>
              <th class="<?= $th ?>" style="<?= $thSt ?>">Topik</th>
              <th class="<?= $th ?> text-center" style="<?= $thSt ?>">Status</th>
              <th class="<?= $th ?> text-center" style="<?= $thSt ?>">Dilihat</th>
              <th class="<?= $th ?> text-end" style="<?= $thSt ?>">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $row): $img = K::gambarKartu($row); ?>
              <tr>
                <td class="px-4 py-3" style="max-width:340px;">
                  <div class="d-flex gap-3 align-items-center">
                    <?php if ($img): ?>
                      <img src="<?= esc($img) ?>" alt="" style="width:56px;height:42px;object-fit:cover;border-radius:.35rem;flex-shrink:0;">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center bg-label-primary" style="width:56px;height:42px;border-radius:.35rem;flex-shrink:0;"><i class="ti <?= K::TIPE_ICON[$row['tipe']] ?>"></i></div>
                    <?php endif ?>
                    <div>
                      <div class="fw-semibold"><?= esc($row['judul']) ?></div>
                      <div class="text-muted small">Diubah <?= esc(tanggal_id($row['updated_at'])) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3"><span class="badge bg-label-primary"><?= esc(K::TIPE[$row['tipe']]) ?></span></td>
                <td class="px-4 py-3"><?= $row['topik_nama'] ? esc($row['topik_nama']) : '<span class="text-muted">—</span>' ?></td>
                <td class="px-4 py-3 text-center">
                  <span class="badge <?= $row['status'] === 'terbit' ? 'bg-label-success' : 'bg-label-secondary' ?>"><?= $row['status'] === 'terbit' ? 'Terbit' : 'Draft' ?></span>
                </td>
                <td class="px-4 py-3 text-center"><?= number_format((int) $row['dilihat']) ?></td>
                <td class="px-4 py-3 text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <?php if ($row['status'] === 'terbit'): ?>
                      <a href="<?= base_url('learn/' . $row['slug']) ?>" target="_blank" class="btn btn-sm btn-icon btn-outline-secondary" title="Lihat"><i class="ti tabler-external-link"></i></a>
                    <?php endif ?>
                    <form action="<?= base_url('admin/learn/toggle/' . $row['id']) ?>" method="post">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-<?= $row['status'] === 'terbit' ? 'warning' : 'success' ?>"
                              title="<?= $row['status'] === 'terbit' ? 'Jadikan draft' : 'Terbitkan' ?>">
                        <i class="ti tabler-<?= $row['status'] === 'terbit' ? 'eye-off' : 'send' ?>"></i>
                      </button>
                    </form>
                    <a href="<?= base_url('admin/learn/edit/' . $row['id']) ?>" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="ti tabler-pencil"></i></a>
                    <form action="<?= base_url('admin/learn/hapus/' . $row['id']) ?>" method="post"
                          onsubmit="return confirm('Hapus konten &quot;<?= esc($row['judul'], 'js') ?>&quot;?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus"><i class="ti tabler-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <div class="p-3"><?= $pager->links() ?></div>
    <?php endif ?>
  </div>
</div>

<?= $this->endSection() ?>
