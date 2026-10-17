<?php
/**
 * Partial: kartu "Data Diri" untuk semua assessment publik.
 * Variabel: $prodiPerFakultas (array fakultas => [prodi]), $old (nilai lama; kunci jenis_kelamin, semester, id_lembaga).
 */
?>
        <div class="card mb-4 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti tabler-user me-2"></i>Data Diri</h5>
          </div>
          <div class="card-body">
            <div class="row g-4">
              <div class="col-md-4">
                <label class="form-label d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                <?php foreach (['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan'] as $val => $lbl): ?>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_<?= $val ?>"
                      value="<?= $val ?>" required <?= ($old['jenis_kelamin'] ?? '') === $val ? 'checked' : '' ?>>
                    <label class="form-check-label" for="jk_<?= $val ?>"><?= $lbl ?></label>
                  </div>
                <?php endforeach ?>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="semester">Semester <span class="text-danger">*</span></label>
                <select class="form-select" name="semester" id="semester" required>
                  <option value="">Pilih</option>
                  <?php for ($i = 1; $i <= 14; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) ($old['semester'] ?? '') === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor ?>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label" for="id_lembaga">Program Studi <span class="text-danger">*</span></label>
                <select class="form-select" name="id_lembaga" id="id_lembaga" required>
                  <option value="">Pilih program studi</option>
                  <?php foreach ($prodiPerFakultas as $fakultas => $prodis): ?>
                    <optgroup label="<?= esc($fakultas) ?>">
                      <?php foreach ($prodis as $p): ?>
                        <option value="<?= esc($p['id_lembaga']) ?>" <?= ($old['id_lembaga'] ?? '') === $p['id_lembaga'] ? 'selected' : '' ?>>
                          <?= esc($p['nama_prodi']) ?>
                        </option>
                      <?php endforeach ?>
                    </optgroup>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
          </div>
        </div>
