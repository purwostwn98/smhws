<?php

namespace App\Controllers;

use App\Models\AgendaKegiatanModel;

/**
 * Kelola agenda Psychoeducation, Webinar & Workshop (admin).
 * Dilindungi oleh filter superadmin untuk URL admin/*.
 */
class AgendaAdminController extends BaseController
{
    private const POSTER_DIR = 'uploads/agenda/';

    public function index(): string
    {
        return view('admin/agenda_kegiatan/index', ['list' => (new AgendaKegiatanModel())->getAll()]);
    }

    public function buat(): string
    {
        return view('admin/agenda_kegiatan/form', ['agenda' => null]);
    }

    public function simpan()
    {
        [$data, $error] = $this->ambilData();
        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $model = new AgendaKegiatanModel();
        $poster = $this->uploadPoster($uploadError);
        if ($uploadError) {
            return redirect()->back()->withInput()->with('error', $uploadError);
        }
        $data['poster'] = $poster;
        $model->insert($data);

        return redirect()->to(base_url('admin/agenda-kegiatan'))->with('success', 'Agenda kegiatan berhasil ditambahkan.');
    }

    public function edit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $agenda = (new AgendaKegiatanModel())->find($id);
        if (! $agenda) {
            return redirect()->to(base_url('admin/agenda-kegiatan'))->with('error', 'Agenda tidak ditemukan.');
        }
        return view('admin/agenda_kegiatan/form', ['agenda' => $agenda]);
    }

    public function update(int $id)
    {
        $model  = new AgendaKegiatanModel();
        $agenda = $model->find($id);
        if (! $agenda) {
            return redirect()->to(base_url('admin/agenda-kegiatan'))->with('error', 'Agenda tidak ditemukan.');
        }

        [$data, $error] = $this->ambilData();
        if ($error) {
            return redirect()->back()->withInput()->with('error', $error);
        }

        $poster = $this->uploadPoster($uploadError);
        if ($uploadError) {
            return redirect()->back()->withInput()->with('error', $uploadError);
        }
        if ($poster) {
            $this->hapusFile($agenda['poster']);
            $data['poster'] = $poster;
        } elseif ($this->request->getPost('hapus_poster')) {
            $this->hapusFile($agenda['poster']);
            $data['poster'] = null;
        }

        $model->update($id, $data);

        return redirect()->to(base_url('admin/agenda-kegiatan'))->with('success', 'Agenda kegiatan berhasil diperbarui.');
    }

    public function hapus(int $id)
    {
        $model  = new AgendaKegiatanModel();
        $agenda = $model->find($id);
        if (! $agenda) {
            return redirect()->to(base_url('admin/agenda-kegiatan'))->with('error', 'Agenda tidak ditemukan.');
        }
        $this->hapusFile($agenda['poster']);
        $model->delete($id);

        return redirect()->to(base_url('admin/agenda-kegiatan'))->with('success', 'Agenda kegiatan berhasil dihapus.');
    }

    /** Buka/tutup pendaftaran. */
    public function toggle(int $id)
    {
        $model  = new AgendaKegiatanModel();
        $agenda = $model->find($id);
        if (! $agenda) {
            return redirect()->to(base_url('admin/agenda-kegiatan'))->with('error', 'Agenda tidak ditemukan.');
        }
        $baru = $agenda['status'] === 'dibuka' ? 'ditutup' : 'dibuka';
        $model->update($id, ['status' => $baru]);

        return redirect()->to(base_url('admin/agenda-kegiatan'))
            ->with('success', 'Pendaftaran "' . $agenda['nama_kegiatan'] . '" ' . ($baru === 'dibuka' ? 'dibuka.' : 'ditutup.'));
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    /** Validasi & normalisasi input form. @return array{0: array, 1: ?string} */
    private function ambilData(): array
    {
        $p = $this->request->getPost();
        $t = fn(string $k) => trim((string) ($p[$k] ?? ''));

        $jenis = $t('jenis');
        if (! isset(AgendaKegiatanModel::JENIS[$jenis])) return [[], 'Jenis kegiatan wajib dipilih.'];
        if ($t('nama_kegiatan') === '')                  return [[], 'Nama kegiatan tidak boleh kosong.'];
        if ($t('lokasi') === '')                         return [[], 'Lokasi tidak boleh kosong.'];

        $mulai = $this->parseDatetime($t('tanggal_mulai'));
        if (! $mulai) return [[], 'Tanggal & waktu mulai wajib diisi.'];

        $selesai = null;
        if ($t('tanggal_selesai') !== '') {
            $selesai = $this->parseDatetime($t('tanggal_selesai'));
            if (! $selesai || $selesai < $mulai) return [[], 'Waktu selesai tidak boleh lebih awal dari waktu mulai.'];
        }

        $batas = null;
        if ($t('batas_pendaftaran') !== '') {
            $batas = $this->parseDatetime($t('batas_pendaftaran'));
            if (! $batas) return [[], 'Format batas pendaftaran tidak valid.'];
        }

        $biayaTipe = $t('biaya_tipe') === 'berbayar' ? 'berbayar' : 'gratis';
        $nominal   = null;
        if ($biayaTipe === 'berbayar') {
            $angka = preg_replace('/\D/', '', $t('biaya_nominal'));
            if ($angka === '' || (int) $angka <= 0) return [[], 'Nominal biaya wajib diisi untuk kegiatan berbayar.'];
            $nominal = (int) $angka;
        }

        $link = $t('link_pendaftaran');
        if ($link !== '' && (! preg_match('#^https?://#i', $link) || ! filter_var($link, FILTER_VALIDATE_URL))) {
            return [[], 'Link pendaftaran harus berupa URL yang valid (diawali http:// atau https://).'];
        }

        $status = $t('status') === 'ditutup' ? 'ditutup' : 'dibuka';

        return [[
            'jenis'             => $jenis,
            'nama_kegiatan'     => $t('nama_kegiatan'),
            'deskripsi'         => $t('deskripsi') ?: null,
            'tema'              => $t('tema') ?: null,
            'narasumber'        => $t('narasumber') ?: null,
            'tanggal_mulai'     => $mulai,
            'tanggal_selesai'   => $selesai,
            'lokasi'            => $t('lokasi'),
            'biaya_tipe'        => $biayaTipe,
            'biaya_nominal'     => $nominal,
            'kontak'            => $t('kontak') ?: null,
            'link_pendaftaran'  => $link ?: null,
            'batas_pendaftaran' => $batas,
            'status'            => $status,
        ], null];
    }

    /** "2026-10-16T09:30" → "2026-10-16 09:30:00"; null bila tidak valid. */
    private function parseDatetime(string $v): ?string
    {
        $d = \DateTime::createFromFormat('Y-m-d\TH:i', $v);
        return $d ? $d->format('Y-m-d H:i:00') : null;
    }

    /** Simpan poster upload; mengembalikan path relatif atau null. $error diisi bila file ditolak. */
    private function uploadPoster(?string &$error = null): ?string
    {
        $error = null;
        $file  = $this->request->getFile('poster');
        if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $file->isValid() || $file->hasMoved()) {
            $error = 'Poster gagal diunggah.';
            return null;
        }
        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true) || $file->getSize() > 2 * 1024 * 1024) {
            $error = 'Poster harus berformat JPG/PNG/WEBP dan maksimal 2 MB.';
            return null;
        }

        $dir = FCPATH . self::POSTER_DIR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $nama = 'agenda_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $file->guessExtension();
        $file->move($dir, $nama);

        return self::POSTER_DIR . $nama;
    }

    private function hapusFile(?string $path): void
    {
        if ($path && str_starts_with($path, self::POSTER_DIR)) {
            @unlink(FCPATH . $path);
        }
    }
}
