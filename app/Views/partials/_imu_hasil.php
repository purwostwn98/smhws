<?php
/**
 * Hasil Asesmen Awal — Inventori Masalah Umum (IMU).
 *
 * @var array $imu         baris imu_assessments + 'jawaban' [nomor => nilai] (ImuAssessmentModel::byJanji)
 * @var bool  $tampilArea  true untuk admin & psikolog: skor per area, isian obat/lainnya, jawaban per butir.
 *                         Mahasiswa hanya melihat skor total & interpretasi.
 */
use App\Libraries\InventoriMasalahUmum as IMU;

$tampilArea = $tampilArea ?? false;
[, , $katLabel, $katInterp, $katWarna] = IMU::KATEGORI[$imu['kategori']] ?? IMU::KATEGORI['rendah'];
$skorArea = IMU::skorAreaDariBaris($imu);
$jawaban  = $imu['jawaban'] ?? [];
?>
<div class="imu-hasil">
  <!-- Skor total & tingkat distres global -->
  <div class="d-flex gap-3 align-items-start p-3 rounded-3"
       style="background:<?= $katWarna ?>0f;border-left:3px solid <?= $katWarna ?>;">
    <div class="text-center flex-shrink-0" style="min-width:4.5rem;">
      <div class="fw-bold" style="font-size:2rem;color:<?= $katWarna ?>;line-height:1;"><?= (int) $imu['skor_total'] ?></div>
      <small class="text-muted" style="font-size:.7rem;">dari <?= IMU::SKOR_MAKS ?></small>
    </div>
    <div>
      <div class="fw-semibold mb-1" style="font-size:.85rem;">
        Tingkat Distres Global:
        <span class="badge" style="background:<?= $katWarna ?>;color:#fff;font-size:.72rem;"><?= esc($katLabel) ?></span>
      </div>
      <div style="font-size:.8rem;line-height:1.6;color:#444;"><?= esc($katInterp) ?></div>
    </div>
  </div>

  <?php if ($tampilArea): ?>
    <!-- Skor per area masalah (admin & psikolog) -->
    <div class="row g-3 mt-1">
      <?php foreach (IMU::AREA as $kode => [$awal, $akhir, $nama, $indikasi]):
        $skor   = $skorArea[$kode];
        $tinggi = IMU::areaTinggi($skor);
      ?>
        <div class="col-md-4">
          <div class="p-3 rounded-2 h-100" style="background:#fff;border-left:3px solid <?= $tinggi ? '#dc3545' : '#2d9b6e' ?>;">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
              <div>
                <div class="fw-semibold" style="font-size:.83rem;"><?= esc($nama) ?></div>
                <small class="text-muted" style="font-size:.7rem;">Butir <?= $awal ?>–<?= $akhir ?></small>
              </div>
              <span class="fw-bold" style="font-size:1.3rem;color:<?= $tinggi ? '#dc3545' : '#2d9b6e' ?>;"><?= $skor ?></span>
            </div>
            <?php if ($tinggi): ?>
              <span class="badge bg-label-danger mb-1" style="font-size:.68rem;">Di atas <?= IMU::AMBANG_AREA ?></span>
              <div style="font-size:.76rem;color:#555;line-height:1.5;"><?= esc($indikasi) ?></div>
            <?php else: ?>
              <span class="badge bg-label-success" style="font-size:.68rem;">Dalam batas</span>
            <?php endif ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>

    <?php if (! empty($imu['obat_keterangan']) || ! empty($imu['lainnya_keterangan'])): ?>
      <div class="mt-3 p-2 rounded-2 bg-white" style="font-size:.8rem;">
        <?php if (! empty($imu['obat_keterangan'])): ?>
          <div><span class="text-muted">Obat yang diresepkan dokter:</span> <?= esc($imu['obat_keterangan']) ?></div>
        <?php endif ?>
        <?php if (! empty($imu['lainnya_keterangan'])): ?>
          <div>
            <span class="text-muted">Masalah lainnya:</span> <?= esc($imu['lainnya_keterangan']) ?>
            <?php if (! empty($imu['lainnya_skor'])): ?>
              <span class="text-muted">(<?= esc(IMU::SKALA[(int) $imu['lainnya_skor']] ?? '') ?>, tidak dihitung dalam skor)</span>
            <?php endif ?>
          </div>
        <?php endif ?>
      </div>
    <?php endif ?>

    <?php if ($jawaban): ?>
      <details class="mt-3">
        <summary class="fw-semibold" style="font-size:.8rem;cursor:pointer;color:#1a5f7a;">Lihat jawaban per butir</summary>
        <div class="table-responsive mt-2">
          <table class="table table-sm mb-0 bg-white" style="font-size:.78rem;">
            <tbody>
              <?php foreach (IMU::BUTIR as $nomor => $pertanyaan):
                $nilai = (int) ($jawaban[$nomor] ?? 0);
              ?>
                <tr class="<?= $nilai >= 4 ? 'table-danger' : '' ?>">
                  <td class="text-muted text-end" style="width:2rem;"><?= $nomor ?>.</td>
                  <td><?= esc($pertanyaan) ?></td>
                  <td class="text-nowrap fw-semibold"><?= $nilai ?> · <?= esc(IMU::SKALA[$nilai] ?? '—') ?></td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </details>
    <?php endif ?>
  <?php endif ?>
</div>
