<?php

namespace App\Controllers;

use App\Libraries\HtmlSanitizer;
use App\Models\KonselorModel;
use App\Models\LearnKontenModel;
use App\Models\LearnTopikModel;
use App\Models\PertanyaanPsikologModel;

/**
 * Ask Our Psychologist — sisi admin (filter superadmin):
 * menugaskan psikolog, menolak, dan menerbitkan tanya-jawab anonim ke SMHWS Learn.
 */
class TanyaAdminController extends BaseController
{
    public function index(): string
    {
        $status = $this->request->getGet('status');
        $status = isset(PertanyaanPsikologModel::STATUS[$status]) ? $status : 'menunggu';

        $model = (new PertanyaanPsikologModel())->lengkap()->where('pertanyaan_psikolog.status', $status);
        $model->orderBy('pertanyaan_psikolog.created_at', $status === 'menunggu' ? 'ASC' : 'DESC');

        return view('admin/tanya/index', [
            'list'   => $model->paginate(20),
            'pager'  => $model->pager,
            'status' => $status,
            'jumlah' => (new PertanyaanPsikologModel())->hitungPerStatus(),
        ]);
    }

    public function detail(int $id)
    {
        $row = (new PertanyaanPsikologModel())->lengkap()->where('pertanyaan_psikolog.id', $id)->first();
        if (! $row) {
            return redirect()->to(base_url('admin/tanya'))->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $learn = $row['learn_konten_id'] ? (new LearnKontenModel())->find($row['learn_konten_id']) : null;

        return view('admin/tanya/detail', [
            'row'          => $row,
            'konselorList' => (new KonselorModel())->withUser()->available()->findAll(),
            'topik'        => (new LearnTopikModel())->getAll(),
            'learn'        => $learn,
        ]);
    }

    /** Tugaskan / ganti psikolog (selama belum dijawab). */
    public function tugaskan(int $id)
    {
        $model = new PertanyaanPsikologModel();
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to(base_url('admin/tanya'))->with('error', 'Pertanyaan tidak ditemukan.');
        }
        if (! in_array($row['status'], ['menunggu', 'ditugaskan'], true)) {
            return redirect()->back()->with('error', 'Psikolog hanya dapat ditugaskan untuk pertanyaan yang belum dijawab.');
        }

        $konselorId = (int) $this->request->getPost('konselor_id');
        if (! $konselorId || ! (new KonselorModel())->find($konselorId)) {
            return redirect()->back()->with('error', 'Pilih psikolog yang akan menjawab.');
        }

        $model->update($id, [
            'konselor_id'     => $konselorId,
            'status'          => 'ditugaskan',
            'ditugaskan_oleh' => (int) session()->get('user_id'),
            'ditugaskan_at'   => date('Y-m-d H:i:s'),
            'alasan_ditolak'  => null,
        ]);

        return redirect()->to(base_url('admin/tanya/' . $id))->with('success', 'Psikolog berhasil ditugaskan.');
    }

    public function tolak(int $id)
    {
        $model = new PertanyaanPsikologModel();
        $row   = $model->find($id);
        if (! $row || $row['status'] === 'dijawab') {
            return redirect()->back()->with('error', 'Pertanyaan tidak dapat ditolak.');
        }
        $alasan = trim((string) $this->request->getPost('alasan_ditolak'));
        if ($alasan === '') {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi (akan dibaca mahasiswa).');
        }

        $model->update($id, ['status' => 'ditolak', 'alasan_ditolak' => $alasan]);

        return redirect()->to(base_url('admin/tanya/' . $id))->with('success', 'Pertanyaan ditolak.');
    }

    /** Terbitkan (atau perbarui) tanya-jawab anonim ke SMHWS Learn. */
    public function terbitkan(int $id)
    {
        $model = new PertanyaanPsikologModel();
        $row   = $model->lengkap()->where('pertanyaan_psikolog.id', $id)->first();
        if (! $row) {
            return redirect()->to(base_url('admin/tanya'))->with('error', 'Pertanyaan tidak ditemukan.');
        }
        if ($row['status'] !== 'dijawab' || ! $row['izin_publikasi']) {
            return redirect()->back()->with('error', 'Hanya pertanyaan yang sudah dijawab dan diizinkan mahasiswa yang dapat diterbitkan.');
        }

        $p = $this->request->getPost();
        $t = fn(string $k) => trim((string) ($p[$k] ?? ''));
        if ($t('judul') === '' || $t('pertanyaan') === '' || $t('jawaban') === '') {
            return redirect()->back()->withInput()->with('error', 'Judul, pertanyaan, dan jawaban wajib diisi.');
        }

        $topikId = (int) $t('topik_id') ?: null;
        if ($topikId && ! (new LearnTopikModel())->find($topikId)) $topikId = null;

        $data = [
            'tipe'      => 'tanya_jawab',
            'judul'     => mb_substr($t('judul'), 0, 255),
            'ringkasan' => mb_strimwidth(preg_replace('/\s+/', ' ', $t('pertanyaan')), 0, 200, '…'),
            'topik_id'  => $topikId,
            'isi'       => $this->susunIsi($t('pertanyaan'), $t('jawaban'), $row),
            'penulis'   => PertanyaanPsikologModel::namaPsikolog($row) ?: 'Psikolog SMHWS',
            'status'    => 'terbit',
        ];

        $learn = new LearnKontenModel();
        $ada   = $row['learn_konten_id'] ? $learn->find($row['learn_konten_id']) : null;

        if ($ada) {
            if ($data['judul'] !== $ada['judul']) $data['slug'] = $learn->buatSlug($data['judul'], (int) $ada['id']);
            if (empty($ada['diterbitkan_at']))    $data['diterbitkan_at'] = date('Y-m-d H:i:s');
            $learn->update($ada['id'], $data);
            $pesan = 'Tanya jawab di SMHWS Learn berhasil diperbarui.';
        } else {
            $data['slug']           = $learn->buatSlug($data['judul']);
            $data['diterbitkan_at'] = date('Y-m-d H:i:s');
            $kontenId = $learn->insert($data, true);
            $model->update($id, ['learn_konten_id' => $kontenId]);
            $pesan = 'Tanya jawab berhasil diterbitkan di SMHWS Learn.';
        }

        return redirect()->to(base_url('admin/tanya/' . $id))->with('success', $pesan);
    }

    /**
     * Isi konten Learn: identitas anonim + pertanyaan + jawaban (teks → HTML aman).
     */
    private function susunIsi(string $pertanyaan, string $jawaban, array $row): string
    {
        $paragraf = fn(string $teks) => implode('', array_map(
            fn($para) => '<p>' . nl2br(esc(trim($para))) . '</p>',
            array_filter(preg_split("/\R{2,}/", $teks), fn($x) => trim($x) !== '')
        ));

        $identitas = 'Mahasiswa semester ' . (int) $row['semester'] . ($row['fakultas'] ? ', ' . $row['fakultas'] : '');

        return HtmlSanitizer::bersihkan(
            '<h3>Pertanyaan</h3>'
            . '<p><em>' . esc($identitas) . '</em></p>'
            . '<blockquote>' . $paragraf($pertanyaan) . '</blockquote>'
            . '<h3>Jawaban Psikolog</h3>'
            . $paragraf($jawaban)
        );
    }
}
