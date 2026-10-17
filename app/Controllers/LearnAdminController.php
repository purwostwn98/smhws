<?php

namespace App\Controllers;

use App\Libraries\HtmlSanitizer;
use App\Models\LearnKontenModel;
use App\Models\LearnTopikModel;

/**
 * Kelola konten SMHWS Learn & topiknya (admin).
 * Dilindungi oleh filter superadmin untuk URL admin/*.
 */
class LearnAdminController extends BaseController
{
    private const DIR = 'uploads/learn/';
    private const MB  = 1024 * 1024;

    // ══ Konten ══════════════════════════════════════════════════════════════

    public function index(): string
    {
        $tipe   = $this->request->getGet('tipe');
        $status = $this->request->getGet('status');

        $model = (new LearnKontenModel())->denganTopik();
        if (isset(LearnKontenModel::TIPE[$tipe]))           $model->where('learn_konten.tipe', $tipe);
        if (in_array($status, ['draft', 'terbit'], true))   $model->where('learn_konten.status', $status);

        return view('admin/learn/index', [
            'list'   => $model->orderBy('learn_konten.updated_at', 'DESC')->paginate(20),
            'pager'  => $model->pager,
            'tipe'   => $tipe,
            'status' => $status,
        ]);
    }

    public function buat(): string
    {
        return view('admin/learn/form', [
            'konten' => null,
            'topik'  => (new LearnTopikModel())->getAll(),
            'tipeAwal' => in_array($this->request->getGet('tipe'), LearnKontenModel::TIPE_MANUAL, true) ? $this->request->getGet('tipe') : 'artikel',
        ]);
    }

    public function simpan()
    {
        $model = new LearnKontenModel();
        [$data, $error] = $this->ambilData(null);
        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $data['slug'] = $model->buatSlug($data['judul']);
        if ($data['status'] === 'terbit') {
            $data['diterbitkan_at'] = date('Y-m-d H:i:s');
        }
        $model->insert($data);

        return redirect()->to(base_url('admin/learn'))->with('success', 'Konten berhasil ditambahkan.');
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konten = (new LearnKontenModel())->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/learn'))->with('error', 'Konten tidak ditemukan.');
        }
        if ($konten['tipe'] === 'tanya_jawab') {
            return $this->keTanyaJawab($id);
        }
        return view('admin/learn/form', [
            'konten'   => $konten,
            'topik'    => (new LearnTopikModel())->getAll(),
            'tipeAwal' => $konten['tipe'],
        ]);
    }

    public function update(int $id)
    {
        $model  = new LearnKontenModel();
        $konten = $model->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/learn'))->with('error', 'Konten tidak ditemukan.');
        }

        if ($konten['tipe'] === 'tanya_jawab') {
            return $this->keTanyaJawab($id);
        }

        [$data, $error] = $this->ambilData($konten);
        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        if ($data['judul'] !== $konten['judul']) {
            $data['slug'] = $model->buatSlug($data['judul'], $id);
        }
        if ($data['status'] === 'terbit' && empty($konten['diterbitkan_at'])) {
            $data['diterbitkan_at'] = date('Y-m-d H:i:s');
        }
        $model->update($id, $data);

        return redirect()->to(base_url('admin/learn'))->with('success', 'Konten berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $model  = new LearnKontenModel();
        $konten = $model->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/learn'))->with('error', 'Konten tidak ditemukan.');
        }
        $this->hapusFile($konten['thumbnail']);
        $this->hapusFile($konten['file']);
        $model->delete($id);

        return redirect()->to(base_url('admin/learn'))->with('success', 'Konten berhasil dihapus.');
    }

    /** Terbitkan / jadikan draft. */
    public function toggle(int $id)
    {
        $model  = new LearnKontenModel();
        $konten = $model->find($id);
        if (! $konten) {
            return redirect()->to(base_url('admin/learn'))->with('error', 'Konten tidak ditemukan.');
        }

        $baru = $konten['status'] === 'terbit' ? 'draft' : 'terbit';
        $data = ['status' => $baru];
        if ($baru === 'terbit' && empty($konten['diterbitkan_at'])) {
            $data['diterbitkan_at'] = date('Y-m-d H:i:s');
        }
        $model->update($id, $data);

        return redirect()->back()->with('success', '"' . $konten['judul'] . '" ' . ($baru === 'terbit' ? 'diterbitkan.' : 'dijadikan draft.'));
    }

    /** Konten tanya jawab disunting dari menu Ask Our Psychologist. */
    private function keTanyaJawab(int $kontenId): \CodeIgniter\HTTP\RedirectResponse
    {
        $p = (new \App\Models\PertanyaanPsikologModel())->where('learn_konten_id', $kontenId)->first();
        return $p
            ? redirect()->to(base_url('admin/tanya/' . $p['id']))->with('success', 'Konten Tanya Jawab disunting dari halaman pertanyaan ini.')
            : redirect()->to(base_url('admin/learn'))->with('error', 'Pertanyaan asal konten ini tidak ditemukan.');
    }

    // ══ Topik ═══════════════════════════════════════════════════════════════

    public function topik(): string
    {
        return view('admin/learn/topik', ['list' => (new LearnTopikModel())->denganJumlah()]);
    }

    public function topikSimpan()
    {
        $nama = trim((string) $this->request->getPost('nama'));
        if ($nama === '') {
            return redirect()->back()->with('error', 'Nama topik tidak boleh kosong.');
        }
        $model = new LearnTopikModel();
        $model->insert([
            'nama'   => $nama,
            'slug'   => $this->slugTopik($model, $nama),
            'urutan' => (int) $this->request->getPost('urutan'),
        ]);
        return redirect()->to(base_url('admin/learn/topik'))->with('success', 'Topik berhasil ditambahkan.');
    }

    public function topikUpdate(int $id)
    {
        $model = new LearnTopikModel();
        if (! $model->find($id)) {
            return redirect()->to(base_url('admin/learn/topik'))->with('error', 'Topik tidak ditemukan.');
        }
        $nama = trim((string) $this->request->getPost('nama'));
        if ($nama === '') {
            return redirect()->back()->with('error', 'Nama topik tidak boleh kosong.');
        }
        $model->update($id, [
            'nama'   => $nama,
            'slug'   => $this->slugTopik($model, $nama, $id),
            'urutan' => (int) $this->request->getPost('urutan'),
        ]);
        return redirect()->to(base_url('admin/learn/topik'))->with('success', 'Topik berhasil diperbarui.');
    }

    public function topikHapus(int $id)
    {
        $model = new LearnTopikModel();
        if (! $model->find($id)) {
            return redirect()->to(base_url('admin/learn/topik'))->with('error', 'Topik tidak ditemukan.');
        }
        // Konten terkait otomatis menjadi tanpa topik (FK ON DELETE SET NULL)
        $model->delete($id);
        return redirect()->to(base_url('admin/learn/topik'))->with('success', 'Topik berhasil dihapus.');
    }

    // ══ Helper ══════════════════════════════════════════════════════════════

    /**
     * Validasi input + proses upload. File lama dihapus bila diganti/tidak relevan lagi.
     *
     * @return array{0: array, 1: ?string}
     */
    private function ambilData(?array $lama): array
    {
        $p = $this->request->getPost();
        $t = fn(string $k) => trim((string) ($p[$k] ?? ''));

        $tipe = $t('tipe');
        if (! in_array($tipe, LearnKontenModel::TIPE_MANUAL, true)) return [[], 'Tipe konten wajib dipilih.'];
        if ($t('judul') === '')                     return [[], 'Judul tidak boleh kosong.'];

        $topikId = (int) $t('topik_id') ?: null;
        if ($topikId && ! (new LearnTopikModel())->find($topikId)) $topikId = null;

        $data = [
            'tipe'      => $tipe,
            'judul'     => mb_substr($t('judul'), 0, 255),
            'ringkasan' => mb_substr($t('ringkasan'), 0, 500) ?: null,
            'topik_id'  => $topikId,
            'penulis'   => $t('penulis') ?: null,
            'status'    => $t('status') === 'terbit' ? 'terbit' : 'draft',
            'isi'       => null,
            'youtube_id'     => null,
            'link_eksternal' => null,
        ];

        // ── Field per tipe ──
        switch ($tipe) {
            case 'artikel':
                $data['isi'] = HtmlSanitizer::bersihkan($p['isi'] ?? '');
                if (trim(strip_tags($data['isi'], '<img>')) === '') return [[], 'Isi artikel tidak boleh kosong.'];
                break;

            case 'video':
                $yt = LearnKontenModel::youtubeId($t('youtube_url'));
                if (! $yt) return [[], 'URL YouTube tidak valid.'];
                $data['youtube_id'] = $yt;
                $data['isi'] = $this->keterangan($p);
                break;

            case 'poster':
                $data['isi'] = $this->keterangan($p);
                break;

            case 'media':
                $link = $t('link_eksternal');
                if ($link !== '' && (! preg_match('#^https?://#i', $link) || ! filter_var($link, FILTER_VALIDATE_URL))) {
                    return [[], 'Link eksternal harus berupa URL yang valid (diawali http:// atau https://).'];
                }
                $data['link_eksternal'] = $link ?: null;
                $data['isi'] = $this->keterangan($p);
                break;
        }

        // ── Upload (file baru dibuang lagi bila validasi berikutnya gagal) ──
        $baruDiunggah = [];
        $gagal = function (string $pesan) use (&$baruDiunggah): array {
            foreach ($baruDiunggah as $f) $this->hapusFile($f);
            return [[], $pesan];
        };

        // File utama (poster: gambar; media: PDF)
        $fileLama = $lama['file'] ?? null;
        $data['file'] = null;
        if (in_array($tipe, ['poster', 'media'], true)) {
            [$mime, $maks, $pesan] = $tipe === 'poster'
                ? [['image/jpeg', 'image/png', 'image/webp'], 5, 'Poster harus berformat JPG/PNG/WEBP dan maksimal 5 MB.']
                : [['application/pdf'], 10, 'File media harus berformat PDF dan maksimal 10 MB.'];

            // File lama hanya berlaku bila tipenya sama (mis. poster → media tidak)
            if ($fileLama && ($lama['tipe'] ?? null) === $tipe && empty($p['hapus_file'])) {
                $data['file'] = $fileLama;
            }

            $baru = $this->upload('file', $mime, $maks, $tipe, $err);
            if ($err) return $gagal($pesan);
            if ($baru) {
                $baruDiunggah[] = $baru;
                $data['file']   = $baru;
            }

            if ($tipe === 'poster' && ! $data['file'])                              return $gagal('Gambar poster/infografis wajib diunggah.');
            if ($tipe === 'media' && ! $data['file'] && ! $data['link_eksternal']) return $gagal('Unggah file PDF atau isi link eksternal (minimal salah satu).');
        }

        // Thumbnail (opsional, semua tipe)
        $thumbLama = $lama['thumbnail'] ?? null;
        $data['thumbnail'] = empty($p['hapus_thumbnail']) ? $thumbLama : null;
        $thumb = $this->upload('thumbnail', ['image/jpeg', 'image/png', 'image/webp'], 2, 'thumb', $err);
        if ($err) return $gagal('Thumbnail harus berformat JPG/PNG/WEBP dan maksimal 2 MB.');
        if ($thumb) {
            $data['thumbnail'] = $thumb;
        }

        // Semua valid → hapus file lama yang sudah tidak dipakai
        if ($fileLama && $fileLama !== $data['file'])            $this->hapusFile($fileLama);
        if ($thumbLama && $thumbLama !== $data['thumbnail'])     $this->hapusFile($thumbLama);

        return [$data, null];
    }

    /** Keterangan teks biasa untuk video/poster/media → paragraf HTML aman. */
    private function keterangan(array $p): ?string
    {
        $teks = trim((string) ($p['keterangan'] ?? ''));
        return $teks === '' ? null : HtmlSanitizer::bersihkan('<p>' . nl2br(esc($teks)) . '</p>');
    }

    /** Upload satu file; null bila tidak ada file. $error = true bila file ditolak. */
    private function upload(string $field, array $mime, int $maksMb, string $prefix, ?bool &$error = null): ?string
    {
        $error = false;
        $file  = $this->request->getFile($field);
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->hasMoved()
            || ! in_array($file->getMimeType(), $mime, true)
            || $file->getSize() > $maksMb * self::MB) {
            $error = true;
            return null;
        }

        $dir = FCPATH . self::DIR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $nama = $prefix . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $file->guessExtension();
        $file->move($dir, $nama);

        return self::DIR . $nama;
    }

    private function hapusFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIR)) {
            @unlink(FCPATH . $path);
        }
    }

    private function slugTopik(LearnTopikModel $model, string $nama, ?int $kecualiId = null): string
    {
        helper('url');
        $base = url_title($nama, '-', true) ?: 'topik';
        $slug = $base;
        for ($i = 2; ; $i++) {
            $b = $model->builder()->where('slug', $slug);
            if ($kecualiId) $b->where('id !=', $kecualiId);
            if ($b->countAllResults() === 0) return $slug;
            $slug = $base . '-' . $i;
        }
    }
}
