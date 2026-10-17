<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Pola Penggunaan Internet<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Skrining pola penggunaan internet secara online tanpa login untuk mahasiswa.<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
  .butir-row { padding: .7rem .75rem; border-radius: .5rem; transition: background .15s; }
  .butir-row:hover { background: rgba(45, 155, 110, .05); }
  .skala { display: flex; gap: .35rem; }
  .skala label {
    cursor: pointer; width: 2.2rem; height: 2.2rem; border-radius: .4rem; border: 2px solid #e0e0e0;
    display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 600;
    color: #888; transition: .15s; user-select: none; margin: 0;
  }
  .skala input[type=radio] { display: none; }
  .skala input[type=radio]:checked + label { border-color: #2d9b6e; background: #2d9b6e; color: #fff; }
  .skrining-wrap { padding-top: 7rem; padding-bottom: 4rem; }
  @media (max-width: 767.98px) {
    .butir-row { flex-wrap: wrap; }
    .butir-row .skala { width: 100%; padding-left: 2.6rem; }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
use App\Libraries\PolaInternet;

$prodiPerFakultas = $prodiPerFakultas ?? [];
$errors = session()->getFlashdata('errors') ?? [];
if ($e = session()->getFlashdata('error')) $errors[] = $e;
$total = count(PolaInternet::BUTIR);
?>
<div class="container skrining-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="text-center mb-5">
        <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Pola Penggunaan Internet</h2>
        <p class="text-muted mb-0">Kenali kebiasaan penggunaan internetmu. Tanpa login, jawabanmu bersifat anonim.</p>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach ?></ul>
        </div>
      <?php endif ?>

      <form action="<?= base_url('skrining/internet/submit') ?>" method="post" id="formSkrining" novalidate>
        <?= csrf_field() ?>

        <?= $this->include('skrining/_data_diri') ?>

        <div class="card mb-4 shadow-sm">
          <div class="card-header">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
              <div>
                <h5 class="card-title mb-0"><i class="ti tabler-world-www me-2"></i>Pertanyaan</h5>
                <p class="text-muted mb-0 mt-1" style="font-size:.82rem;">
                  Pilih jawaban yang paling sesuai dengan <strong>keadaan Anda saat ini</strong>. Tidak ada jawaban benar atau salah,
                  jadi pilihlah sesuai kondisi yang Anda alami dan rasakan. Pastikan tidak ada pertanyaan yang terlewat.
                </p>
              </div>
              <div id="butirProgress" class="badge bg-label-primary" style="font-size:.8rem;">0 / <?= $total ?> dijawab</div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="d-flex gap-3 flex-wrap px-4 py-3 border-bottom" style="background:#f8fafb;">
              <?php foreach (PolaInternet::SKALA as $n => $lbl): ?>
                <div class="d-flex align-items-center gap-2">
                  <div class="skala"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;cursor:default;"><?= $n ?></label></div>
                  <small class="text-muted"><?= $lbl ?></small>
                </div>
              <?php endforeach ?>
            </div>

            <div class="px-2 py-1">
              <?php foreach (PolaInternet::BUTIR as $no => $teks): ?>
                <div class="butir-row d-flex align-items-center gap-3" id="row_butir_<?= $no ?>">
                  <div class="flex-shrink-0 text-muted fw-bold" style="width:1.8rem;font-size:.82rem;text-align:right;"><?= $no ?>.</div>
                  <div class="flex-grow-1" style="font-size:.875rem;"><?= esc($teks) ?></div>
                  <div class="skala flex-shrink-0">
                    <?php foreach (array_keys(PolaInternet::SKALA) as $val): ?>
                      <input type="radio" name="butir_<?= $no ?>" id="b_<?= $no ?>_<?= $val ?>" value="<?= $val ?>"
                        class="butir-radio" data-item="<?= $no ?>"
                        <?= (string) ($old['butir_' . $no] ?? '') === (string) $val ? 'checked' : '' ?> />
                      <label for="b_<?= $no ?>_<?= $val ?>" title="<?= PolaInternet::SKALA[$val] ?>"><?= $val ?></label>
                    <?php endforeach ?>
                  </div>
                </div>
              <?php endforeach ?>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="<?= base_url('skrining') ?>" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Kembali</a>
          <button type="submit" class="btn btn-primary">Lihat Hasil <i class="ti tabler-arrow-right ms-1"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script>
  (function() {
    const total = <?= $total ?>;
    const progress = document.getElementById('butirProgress');
    const answeredSet = () => new Set([...document.querySelectorAll('.butir-radio:checked')].map(r => r.dataset.item));

    function update() {
      const n = answeredSet().size;
      progress.textContent = n + ' / ' + total + ' dijawab';
      progress.className = 'badge ' + (n === total ? 'bg-label-success' : 'bg-label-primary');
    }
    document.querySelectorAll('.butir-radio').forEach(r => r.addEventListener('change', () => {
      update();
      const row = document.getElementById('row_butir_' + r.dataset.item);
      if (row) row.style.outline = '';
    }));
    update();

    document.getElementById('formSkrining').addEventListener('submit', function(e) {
      let first = null;
      const answered = answeredSet();
      document.querySelectorAll('.butir-row').forEach(row => {
        if (!answered.has(row.id.replace('row_butir_', ''))) {
          row.style.outline = '2px solid #ea5455';
          first = first || row;
        }
      });
      ['jenis_kelamin', 'semester', 'id_lembaga'].forEach(n => {
        const el = this.querySelector('[name="' + n + '"]');
        const ok = el.type === 'radio' ? this.querySelector('[name="' + n + '"]:checked') : el.value;
        if (!ok) first = first || el;
      });
      if (first) {
        e.preventDefault();
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        alert('Mohon lengkapi data diri dan jawab semua pertanyaan.');
      }
    });
  })();
</script>
<?= $this->endSection() ?>
