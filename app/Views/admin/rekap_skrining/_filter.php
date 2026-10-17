<?php /** Filter rekap skrining. Variabel: $filters, $opsi */ $f = $filters; ?>
<!-- Filter -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="get" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Fakultas</label>
        <select name="fakultas" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['fakultas'] as $o): ?><option value="<?= esc($o) ?>" <?= $f['fakultas'] === $o ? 'selected' : '' ?>><?= esc($o) ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Program Studi</label>
        <select name="id_lembaga" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['prodi'] as $o): ?><option value="<?= esc($o['id_lembaga']) ?>" <?= $f['id_lembaga'] === $o['id_lembaga'] ? 'selected' : '' ?>><?= esc($o['nama_prodi']) ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select form-select-sm">
          <option value="">Semua</option>
          <option value="laki-laki" <?= $f['jenis_kelamin'] === 'laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="perempuan" <?= $f['jenis_kelamin'] === 'perempuan' ? 'selected' : '' ?>>Perempuan</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Semester</label>
        <select name="semester" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach ($opsi['semester'] as $o): ?><option value="<?= $o ?>" <?= $f['semester'] === (string) $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-2"></div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Dari tanggal</label>
        <input type="date" name="dari" class="form-control form-control-sm" value="<?= esc($f['dari']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Sampai tanggal</label>
        <input type="date" name="sampai" class="form-control form-control-sm" value="<?= esc($f['sampai']) ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="ti tabler-filter me-1"></i>Terapkan</button>
        <a href="<?= current_url() ?>" class="btn btn-label-secondary btn-sm">Reset</a>
      </div>
    </form>
  </div>
</div>

