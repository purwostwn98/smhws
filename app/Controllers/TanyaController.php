<?php

namespace App\Controllers;

use App\Models\LearnTopikModel;
use App\Models\PertanyaanPsikologModel;
use App\Models\UserModel;

/**
 * Ask Our Psychologist — sisi mahasiswa (wajib login, filter mahasiswa).
 */
class TanyaController extends BaseController
{
    public function index(): string
    {
        $userId = (int) session()->get('user_id');
        $model  = new PertanyaanPsikologModel();

        return view('tanya/index', [
            'list' => $model->milikUser($userId)->orderBy('pertanyaan_psikolog.created_at', 'DESC')->findAll(),
        ]);
    }

    public function buat(): string
    {
        $userId = (int) session()->get('user_id');

        return view('tanya/form', [
            'user'      => (new UserModel())->find($userId),
            'topik'     => (new LearnTopikModel())->getAll(),
            'jumlahAktif' => (new PertanyaanPsikologModel())->jumlahAktifUser($userId),
        ]);
    }

    public function simpan()
    {
        $userId = (int) session()->get('user_id');
        $model  = new PertanyaanPsikologModel();

        if ($model->jumlahAktifUser($userId) >= PertanyaanPsikologModel::MAKS_AKTIF) {
            return redirect()->to(base_url('tanya'))->with('error',
                'Kamu masih punya ' . PertanyaanPsikologModel::MAKS_AKTIF . ' pertanyaan yang sedang diproses. Tunggu dijawab dulu, ya.');
        }

        $p = $this->request->getPost();
        $t = fn(string $k) => trim((string) ($p[$k] ?? ''));

        $errors = [];
        if (! in_array($t('jenis_kelamin'), ['laki-laki', 'perempuan'], true)) $errors[] = 'Jenis kelamin wajib dipilih.';
        $semester = (int) $t('semester');
        if ($semester < 1 || $semester > 14)                                   $errors[] = 'Semester wajib dipilih (1–14).';
        if (mb_strlen($t('judul')) < 5)                                        $errors[] = 'Inti pertanyaan wajib diisi (minimal 5 karakter).';
        if (mb_strlen($t('pertanyaan')) < 20)                                  $errors[] = 'Ceritakan pertanyaanmu lebih lengkap (minimal 20 karakter).';
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $topikId = (int) $t('topik_id') ?: null;
        if ($topikId && ! (new LearnTopikModel())->find($topikId)) $topikId = null;

        $user = (new UserModel())->find($userId);

        $id = $model->insert([
            'user_id'        => $userId,
            'nama'           => $user['name'] ?? (string) session()->get('user_name'),
            'prodi'          => $user['prodi'] ?? null,
            'fakultas'       => $user['fakultas'] ?? null,
            'jenis_kelamin'  => $t('jenis_kelamin'),
            'semester'       => $semester,
            'topik_id'       => $topikId,
            'judul'          => mb_substr($t('judul'), 0, 255),
            'pertanyaan'     => mb_substr($t('pertanyaan'), 0, 5000),
            'izin_publikasi' => ! empty($p['izin_publikasi']) ? 1 : 0,
            'status'         => 'menunggu',
        ], true);

        return redirect()->to(base_url('tanya/' . $id))
            ->with('success', 'Pertanyaanmu sudah terkirim. Psikolog SMHWS akan segera menjawabnya.');
    }

    public function detail(int $id)
    {
        $userId = (int) session()->get('user_id');
        $model  = new PertanyaanPsikologModel();
        $row    = $model->milikUser($userId)->where('pertanyaan_psikolog.id', $id)->first();
        if (! $row) {
            return redirect()->to(base_url('tanya'))->with('error', 'Pertanyaan tidak ditemukan.');
        }

        if ($row['status'] === 'dijawab' && empty($row['dibaca_mahasiswa_at'])) {
            (new PertanyaanPsikologModel())->update($id, ['dibaca_mahasiswa_at' => date('Y-m-d H:i:s')]);
        }

        return view('tanya/detail', ['row' => $row]);
    }
}
