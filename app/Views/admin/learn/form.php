<?= $this->extend('layouts/dashboard') ?>
<?php
use App\Models\LearnKontenModel as K;

$konten = $konten ?? null;
$v      = fn(string $k, $default = '') => old($k, $konten[$k] ?? $default);
$tipe   = old('tipe', $tipeAwal);
// Keterangan untuk tipe non-artikel disimpan sebagai HTML sederhana → tampilkan kembali sebagai teks
$keterangan = old('keterangan', $konten && $konten['tipe'] !== 'artikel'
    ? trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $konten['isi']))))
    : '');
$youtubeUrl = old('youtube_url', ! empty($konten['youtube_id']) ? 'https://www.youtube.com/watch?v=' . $konten['youtube_id'] : '');
?>
<?= $this->section('title') ?><?= $konten ? 'Edit' : 'Tambah' ?> Konten Learn<?= $this->endSection() ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/quill/typography.css') ?>" />
<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/quill/editor.css') ?>" />
<style>
  #editorArtikel { min-height: 320px; font-size: .95rem; }
  .field-tipe { display: none; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= base_url('admin/learn') ?>" class="text-muted text-decoration-none" style="font-size:.875rem;">
    <i class="ti tabler-arrow-left me-1"></i>SMHWS Learn
  </a>
  <span class="text-muted">/</span>
  <span class="fw-semibold" style="font-size:.875rem;"><?= $konten ? 'Edit' : 'Tambah' ?></span>
</div>

<?= $this->include('admin/learn/_flash') ?>

<form action="<?= $konten ? base_url('admin/learn/update/' . $konten['id']) : base_url('admin/learn/simpan') ?>"
      method="post" enctype="multipart/form-data" id="formLearn">
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Kolom utama -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <div class="mb-4">
            <label class="form-label fw-semibold">Tipe Konten <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
              <?php foreach (K::TIPE_MANUAL as $kode): $label = K::TIPE[$kode]; ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="tipe" id="tipe_<?= $kode ?>" value="<?= $kode ?>" <?= $tipe === $kode ? 'checked' : '' ?>>
                  <label class="form-check-label" for="tipe_<?= $kode ?>"><i class="ti <?= K::TIPE_ICON[$kode] ?> me-1"></i><?= $label ?></label>
                </div>
              <?php endforeach ?>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control" maxlength="255" required value="<?= esc($v('judul')) ?>">
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Ringkasan</label>
            <textarea name="ringkasan" class="form-control" rows="2" maxlength="500" placeholder="1–2 kalimat, tampil di kartu daftar konten"><?= esc($v('ringkasan')) ?></textarea>
          </div>

          <!-- Artikel -->
          <div class="field-tipe mb-2" data-tipe="artikel">
            <label class="form-label fw-semibold">Isi Artikel <span class="text-danger">*</span></label>
            <div id="editorArtikel"></div>
            <input type="hidden" name="isi" id="inputIsi" value="<?= esc(old('isi', $konten && $konten['tipe'] === 'artikel' ? $konten['isi'] : '')) ?>">
            <div class="form-text">Gambar disisipkan lewat URL (http/https). Skrip dan format berbahaya dibuang otomatis.</div>
          </div>

          <!-- Video -->
          <div class="field-tipe mb-4" data-tipe="video">
            <label class="form-label fw-semibold">URL YouTube <span class="text-danger">*</span></label>
            <input type="text" name="youtube_url" id="youtubeUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="<?= esc($youtubeUrl) ?>">
            <div class="form-text">Mendukung tautan youtube.com/watch, youtu.be, dan shorts.</div>
            <div id="ytPreview" class="mt-3 ratio ratio-16x9" style="display:none;max-width:480px;"></div>
          </div>

          <!-- Poster -->
          <div class="field-tipe mb-4" data-tipe="poster">
            <label class="form-label fw-semibold">Gambar Poster/Infografis <span class="text-danger">*</span> <small class="text-muted fw-normal">(JPG/PNG/WEBP, maks 5 MB)</small></label>
            <input type="file" name="file" class="form-control" accept="image/jpeg,image/png,image/webp" data-file-tipe="poster">
            <?php if ($konten && $konten['tipe'] === 'poster' && $konten['file']): ?>
              <img src="<?= base_url($konten['file']) ?>" alt="" class="mt-2 rounded" style="max-height:160px;">
            <?php endif ?>
          </div>

          <!-- Media -->
          <div class="field-tipe mb-4" data-tipe="media">
            <label class="form-label fw-semibold">File PDF <small class="text-muted fw-normal">(maks 10 MB)</small></label>
            <input type="file" name="file" class="form-control" accept="application/pdf" data-file-tipe="media">
            <?php if ($konten && $konten['tipe'] === 'media' && $konten['file']): ?>
              <div class="mt-2 d-flex align-items-center gap-3">
                <a href="<?= base_url($konten['file']) ?>" target="_blank"><i class="ti tabler-file-type-pdf me-1"></i><?= esc(basename($konten['file'])) ?></a>
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="hapus_file" id="hapus_file" value="1">
                  <label class="form-check-label" for="hapus_file">Hapus file</label>
                </div>
              </div>
            <?php endif ?>
            <label class="form-label fw-semibold mt-3">Link Eksternal</label>
            <input type="url" name="link_eksternal" class="form-control" maxlength="500" placeholder="https://drive.google.com/..." value="<?= esc($v('link_eksternal')) ?>">
            <div class="form-text">Isi minimal salah satu: file PDF atau link eksternal.</div>
          </div>

          <!-- Keterangan (non-artikel) -->
          <div class="field-tipe" data-tipe="video poster media">
            <label class="form-label fw-semibold">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="4" placeholder="Penjelasan singkat tentang konten ini"><?= esc($keterangan) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Kolom samping -->
    <div class="col-lg-4">
      <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="draft"  <?= $v('status', 'draft') === 'draft'  ? 'selected' : '' ?>>Draft</option>
              <option value="terbit" <?= $v('status', 'draft') === 'terbit' ? 'selected' : '' ?>>Terbit</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Topik</label>
            <select name="topik_id" class="form-select">
              <option value="">Tanpa topik</option>
              <?php foreach ($topik as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (string) $v('topik_id') === (string) $t['id'] ? 'selected' : '' ?>><?= esc($t['nama']) ?></option>
              <?php endforeach ?>
            </select>
            <div class="form-text"><a href="<?= base_url('admin/learn/topik') ?>">Kelola topik</a></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Penulis / Sumber</label>
            <input type="text" name="penulis" class="form-control" maxlength="255" value="<?= esc($v('penulis')) ?>">
          </div>
          <div>
            <label class="form-label fw-semibold">Thumbnail <small class="text-muted fw-normal">(opsional, maks 2 MB)</small></label>
            <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Gambar sampul di kartu. Jika kosong: video memakai thumbnail YouTube, poster memakai gambarnya sendiri.</div>
            <?php if (! empty($konten['thumbnail'])): ?>
              <div class="mt-2 d-flex align-items-center gap-3">
                <img src="<?= base_url($konten['thumbnail']) ?>" alt="" style="height:60px;border-radius:.35rem;">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" name="hapus_thumbnail" id="hapus_thumbnail" value="1">
                  <label class="form-check-label" for="hapus_thumbnail">Hapus</label>
                </div>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <a href="<?= base_url('admin/learn') ?>" class="btn btn-label-secondary flex-grow-1">Batal</a>
        <button type="submit" class="btn btn-primary flex-grow-1"><i class="ti tabler-device-floppy me-1"></i>Simpan</button>
      </div>
    </div>
  </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('extra_js') ?>
<script src="<?= base_url('assets/vendor/libs/quill/quill.js') ?>"></script>
<script>
  (function () {
    // ── Tampilkan field sesuai tipe; input file tipe lain dinonaktifkan agar tidak ikut terkirim ──
    function tipeAktif() { return document.querySelector('[name="tipe"]:checked')?.value || 'artikel'; }
    function terapkanTipe() {
      const t = tipeAktif();
      document.querySelectorAll('.field-tipe').forEach(el => {
        el.style.display = el.dataset.tipe.split(' ').includes(t) ? 'block' : 'none';
      });
      document.querySelectorAll('[data-file-tipe]').forEach(inp => { inp.disabled = inp.dataset.fileTipe !== t; });
    }
    document.querySelectorAll('[name="tipe"]').forEach(r => r.addEventListener('change', terapkanTipe));
    terapkanTipe();

    // ── Editor Quill ──
    const input = document.getElementById('inputIsi');
    const quill = new Quill('#editorArtikel', {
      theme: 'snow',
      placeholder: 'Tulis isi artikel di sini...',
      modules: {
        toolbar: [
          [{ header: [2, 3, 4, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['blockquote', 'link', 'image'],
          ['clean']
        ]
      }
    });
    if (input.value) quill.clipboard.dangerouslyPasteHTML(input.value);

    // Gambar lewat URL (tanpa base64)
    quill.getModule('toolbar').addHandler('image', function () {
      const url = prompt('URL gambar (https://...)');
      if (url && /^https?:\/\//i.test(url)) {
        const range = quill.getSelection(true);
        quill.insertEmbed(range.index, 'image', url, 'user');
      }
    });

    document.getElementById('formLearn').addEventListener('submit', function () {
      input.value = quill.getText().trim() === '' && !quill.root.querySelector('img') ? '' : quill.root.innerHTML;
    });

    // ── Pratinjau YouTube ──
    const yt = document.getElementById('youtubeUrl');
    const box = document.getElementById('ytPreview');
    function ytId(u) {
      u = (u || '').trim();
      if (/^[A-Za-z0-9_-]{11}$/.test(u)) return u;
      const m = u.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
      return m ? m[1] : null;
    }
    function preview() {
      const id = ytId(yt.value);
      box.style.display = id ? '' : 'none';
      box.innerHTML = id ? '<iframe src="https://www.youtube-nocookie.com/embed/' + id + '" allowfullscreen></iframe>' : '';
    }
    yt.addEventListener('change', preview);
    preview();
  })();
</script>
<?= $this->endSection() ?>
