<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('title') ?>Topik Learn<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
  <h4 class="fw-bold mb-1" style="color:#1a2b40;">Topik Learn</h4>
  <p class="text-muted mb-0" style="font-size:.875rem;">Topik dipakai untuk menyaring konten SMHWS Learn di halaman publik.</p>
</div>

<?= $this->include('admin/learn/_flash') ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header py-3" style="border-left:4px solid #696cff;background:#696cff0a;">
        <h6 class="fw-bold mb-0" style="color:#696cff;"><i class="ti tabler-plus me-2"></i>Tambah Topik</h6>
      </div>
      <div class="card-body pt-4">
        <form action="<?= base_url('admin/learn/topik/simpan') ?>" method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Topik</label>
            <input type="text" name="nama" class="form-control" maxlength="100" required placeholder="mis. Stres Akademik">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Urutan</label>
            <input type="number" name="urutan" class="form-control" min="0" value="0">
          </div>
          <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <?php if (empty($list)): ?>
          <div class="text-center py-5 text-muted">Belum ada topik.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table mb-0 align-middle" style="font-size:.875rem;">
              <thead style="background:#f8f9fa;">
                <tr><th class="px-4 py-3">Nama</th><th class="py-3" style="width:7rem;">Urutan</th><th class="py-3 text-center">Konten</th><th class="px-4 py-3 text-end">Aksi</th></tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): $fid = 'ft' . $row['id']; ?>
                  <tr>
                    <td class="px-4 py-2">
                      <input form="<?= $fid ?>" type="text" name="nama" class="form-control form-control-sm" maxlength="100" required value="<?= esc($row['nama']) ?>">
                    </td>
                    <td class="py-2"><input form="<?= $fid ?>" type="number" name="urutan" class="form-control form-control-sm" min="0" value="<?= (int) $row['urutan'] ?>"></td>
                    <td class="py-2 text-center"><?= (int) $row['jumlah'] ?></td>
                    <td class="px-4 py-2 text-end">
                      <div class="d-flex gap-2 justify-content-end">
                        <form id="<?= $fid ?>" action="<?= base_url('admin/learn/topik/update/' . $row['id']) ?>" method="post">
                          <?= csrf_field() ?>
                          <button type="submit" class="btn btn-sm btn-icon btn-outline-primary" title="Simpan perubahan"><i class="ti tabler-device-floppy"></i></button>
                        </form>
                        <form action="<?= base_url('admin/learn/topik/hapus/' . $row['id']) ?>" method="post"
                              onsubmit="return confirm('Hapus topik &quot;<?= esc($row['nama'], 'js') ?>&quot;?<?= $row['jumlah'] ? ' ' . (int) $row['jumlah'] . ' konten akan menjadi tanpa topik.' : '' ?>')">
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
        <?php endif ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
