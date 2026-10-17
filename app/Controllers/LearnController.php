<?php

namespace App\Controllers;

use App\Models\LearnKontenModel;
use App\Models\LearnTopikModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Halaman publik SMHWS Learn.
 */
class LearnController extends BaseController
{
    public function index(): string
    {
        $tipe  = $this->request->getGet('tipe');
        $tipe  = isset(LearnKontenModel::TIPE[$tipe]) ? $tipe : null;
        $q     = trim((string) $this->request->getGet('q'));
        $topik = null;

        $daftarTopik = (new LearnTopikModel())->getAll();
        $slugTopik   = (string) $this->request->getGet('topik');
        foreach ($daftarTopik as $t) {
            if ($t['slug'] === $slugTopik) $topik = $t;
        }

        $model = (new LearnKontenModel())->cari($tipe, $topik['id'] ?? null, $q);

        return view('learn/index', [
            'list'        => $model->paginate(12),
            'pager'       => $model->pager,
            'tipe'        => $tipe,
            'topik'       => $topik,
            'q'           => $q,
            'daftarTopik' => $daftarTopik,
        ]);
    }

    public function detail(string $slug): string
    {
        $model  = new LearnKontenModel();
        $konten = $model->bySlugTerbit($slug);
        if (! $konten) {
            throw PageNotFoundException::forPageNotFound('Konten tidak ditemukan.');
        }

        // Hitung kunjungan sekali per sesi
        $kunci = 'learn_dilihat_' . $konten['id'];
        if (! session()->get($kunci)) {
            $model->tambahDilihat((int) $konten['id']);
            session()->set($kunci, 1);
        }

        return view('learn/detail', [
            'konten'  => $konten,
            'terkait' => (new LearnKontenModel())->terkait($konten),
        ]);
    }
}
