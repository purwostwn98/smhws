<?php

namespace App\Controllers;

use App\Models\KonselorModel;
use App\Models\PertanyaanPsikologModel;

/**
 * Ask Our Psychologist — sisi psikolog (filter konselor).
 * Psikolog hanya melihat & menjawab pertanyaan yang ditugaskan kepadanya.
 */
class KonselorTanyaController extends BaseController
{
    private function konselorId(): ?int
    {
        $k = (new KonselorModel())->where('user_id', session()->get('user_id'))->first();
        return $k ? (int) $k['id'] : null;
    }

    public function index()
    {
        $kid = $this->konselorId();
        if (! $kid) {
            return redirect()->to(base_url('konselor/dashboard'))->with('error', 'Profil psikolog tidak ditemukan.');
        }

        $tab = $this->request->getGet('tab') === 'dijawab' ? 'dijawab' : 'belum';
        $model = (new PertanyaanPsikologModel())->untukKonselor($kid);
        $tab === 'dijawab'
            ? $model->where('pertanyaan_psikolog.status', 'dijawab')->orderBy('pertanyaan_psikolog.dijawab_at', 'DESC')
            : $model->where('pertanyaan_psikolog.status', 'ditugaskan')->orderBy('pertanyaan_psikolog.ditugaskan_at', 'ASC');

        return view('konselor/tanya/index', [
            'list'   => $model->findAll(),
            'tab'    => $tab,
            'jumlah' => (new PertanyaanPsikologModel())->hitungPerStatus($kid),
        ]);
    }

    public function detail(int $id)
    {
        $row = $this->milikSaya($id);
        if (! $row) {
            return redirect()->to(base_url('konselor/tanya'))->with('error', 'Pertanyaan tidak ditemukan atau bukan untuk Anda.');
        }
        return view('konselor/tanya/detail', ['row' => $row]);
    }

    public function jawab(int $id)
    {
        $row = $this->milikSaya($id);
        if (! $row) {
            return redirect()->to(base_url('konselor/tanya'))->with('error', 'Pertanyaan tidak ditemukan atau bukan untuk Anda.');
        }
        if (! in_array($row['status'], ['ditugaskan', 'dijawab'], true)) {
            return redirect()->back()->with('error', 'Pertanyaan ini tidak dapat dijawab.');
        }
        if (! empty($row['learn_konten_id'])) {
            return redirect()->back()->with('error', 'Jawaban sudah diterbitkan di SMHWS Learn dan tidak dapat diubah lagi. Hubungi admin bila perlu revisi.');
        }

        $jawaban = trim((string) $this->request->getPost('jawaban'));
        if (mb_strlen($jawaban) < 20) {
            return redirect()->back()->withInput()->with('error', 'Jawaban terlalu singkat (minimal 20 karakter).');
        }

        $data = ['jawaban' => mb_substr($jawaban, 0, 10000), 'status' => 'dijawab'];
        if ($row['status'] !== 'dijawab') {
            $data['dijawab_at'] = date('Y-m-d H:i:s');
        } else {
            $data['dibaca_mahasiswa_at'] = null; // jawaban diperbarui → tandai baru lagi untuk mahasiswa
        }
        (new PertanyaanPsikologModel())->update($id, $data);

        return redirect()->to(base_url('konselor/tanya/' . $id))->with('success', 'Jawaban berhasil disimpan dan dapat dilihat mahasiswa.');
    }

    private function milikSaya(int $id): ?array
    {
        $kid = $this->konselorId();
        if (! $kid) return null;
        return (new PertanyaanPsikologModel())->untukKonselor($kid)->where('pertanyaan_psikolog.id', $id)->first();
    }
}
