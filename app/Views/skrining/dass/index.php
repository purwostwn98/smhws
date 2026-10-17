<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Mental Health Check<?= $this->endSection() ?>

<?= $this->section('meta_description') ?>Skrining kesehatan mental DASS-21 secara online tanpa login untuk mahasiswa.<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<style>
  .dass-row {
    padding: .6rem .75rem;
    border-radius: .5rem;
    transition: background .15s;
  }

  .dass-row:hover {
    background: rgba(26, 95, 122, .04);
  }

  .dass-answer {
    display: flex;
    gap: .5rem;
  }

  .dass-answer label {
    cursor: pointer;
    width: 2.2rem;
    height: 2.2rem;
    border-radius: .4rem;
    border: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    font-weight: 600;
    color: #888;
    transition: .15s;
    user-select: none;
    margin: 0;
  }

  .dass-answer input[type=radio] {
    display: none;
  }

  .dass-answer input[type=radio]:checked+label {
    border-color: #1a5f7a;
    background: #1a5f7a;
    color: #fff;
  }

  .skrining-wrap {
    padding-top: 7rem;
    padding-bottom: 4rem;
  }

  @media (max-width: 575.98px) {
    .dass-row {
      flex-wrap: wrap;
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$prodiPerFakultas = $prodiPerFakultas ?? [];
$dassItems = $dassItems ?? [];
$errors = session()->getFlashdata('errors') ?? [];
if ($e = session()->getFlashdata('error')) $errors[] = $e;
?>
<div class="container skrining-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="text-center mb-5">
        <h2 class="fw-bold mb-2" style="color:var(--smhws-dark);">Mental Health Check</h2>
        <p class="text-muted mb-0">
          Skrining DASS-21 untuk mengenali tingkat depresi, kecemasan, dan stres. Tanpa login, jawabanmu bersifat anonim.
        </p>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach ?>
          </ul>
        </div>
      <?php endif ?>

      <form action="<?= base_url('skrining/dass/submit') ?>" method="post" id="formSkrining" novalidate>
        <?= csrf_field() ?>

        <?= $this->include('skrining/_data_diri') ?>

        <div class="card mb-4 shadow-sm">
          <div class="card-header">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
              <div>
                <h5 class="card-title mb-0"><i class="ti tabler-brain me-2"></i>Pernyataan DASS-21</h5>
                <p class="text-muted mb-0 mt-1" style="font-size:.82rem;">
                  Pilih jawaban yang paling sesuai dengan kondisimu <strong>dalam 1 minggu terakhir</strong>.
                </p>
              </div>
              <div id="dassProgress" class="badge bg-label-primary" style="font-size:.8rem;">0 / <?= count($dassItems) ?> dijawab</div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="d-flex gap-3 flex-wrap px-4 py-3 border-bottom" style="background:#f8fafb;">
              <?php foreach (['Tidak pernah', 'Kadang-kadang', 'Cukup sering', 'Sering sekali'] as $n => $lbl): ?>
                <div class="d-flex align-items-center gap-2">
                  <div class="dass-answer"><label style="width:1.8rem;height:1.8rem;font-size:.75rem;cursor:default;"><?= $n ?></label></div>
                  <small class="text-muted"><?= $lbl ?></small>
                </div>
              <?php endforeach ?>
            </div>

            <div class="px-2 py-1">
              <?php foreach ($dassItems as $item): ?>
                <div class="dass-row d-flex align-items-center gap-3" id="row_dass_<?= $item['id'] ?>">
                  <div class="flex-shrink-0 text-muted fw-bold" style="width:1.8rem;font-size:.82rem;text-align:right;"><?= $item['nomor'] ?>.</div>
                  <div class="flex-grow-1" style="font-size:.875rem;"><?= esc($item['pernyataan']) ?></div>
                  <div class="dass-answer flex-shrink-0" style="gap:.35rem;">
                    <?php foreach ([0, 1, 2, 3] as $val): ?>
                      <input type="radio" name="dass_item_<?= $item['id'] ?>" id="di_<?= $item['id'] ?>_<?= $val ?>"
                        value="<?= $val ?>" class="dass-radio" data-item="<?= $item['id'] ?>"
                        <?= (string) ($old['dass_item_' . $item['id']] ?? '') === (string) $val ? 'checked' : '' ?> />
                      <label for="di_<?= $item['id'] ?>_<?= $val ?>"><?= $val ?></label>
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
    const total = <?= count($dassItems) ?>;
    const progress = document.getElementById('dassProgress');
    const radios = document.querySelectorAll('.dass-radio');

    function update() {
      const answered = new Set([...document.querySelectorAll('.dass-radio:checked')].map(r => r.dataset.item)).size;
      progress.textContent = answered + ' / ' + total + ' dijawab';
      progress.className = 'badge ' + (answered === total ? 'bg-label-success' : 'bg-label-primary');
    }
    radios.forEach(r => r.addEventListener('change', () => {
      update();
      const row = document.getElementById('row_dass_' + r.dataset.item);
      if (row) row.style.outline = '';
    }));
    update();

    document.getElementById('formSkrining').addEventListener('submit', function(e) {
      let first = null;
      const answered = new Set([...document.querySelectorAll('.dass-radio:checked')].map(r => r.dataset.item));
      document.querySelectorAll('.dass-row').forEach(row => {
        const id = row.id.replace('row_dass_', '');
        if (!answered.has(id)) {
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
        first.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        alert('Mohon lengkapi data diri dan jawab semua pernyataan.');
      }
    });
  })();
</script>
<?= $this->endSection() ?>