<?php

namespace App\Controllers;

use App\Models\AgendaKegiatanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Halaman publik agenda Psychoeducation, Webinar & Workshop.
 */
class AgendaController extends BaseController
{
    public function index(): string
    {
        $model = new AgendaKegiatanModel();
        $jenis = $this->request->getGet('jenis');
        $jenis = isset(AgendaKegiatanModel::JENIS[$jenis]) ? $jenis : null;

        return view('agenda/index', [
            'akanDatang'   => $model->akanDatang($jenis),
            'telahSelesai' => $model->telahSelesai($jenis),
            'jenisAktif'   => $jenis,
            'jenisList'    => AgendaKegiatanModel::JENIS,
        ]);
    }

    public function detail(int $id): string
    {
        $agenda = (new AgendaKegiatanModel())->find($id);
        if (! $agenda) {
            throw PageNotFoundException::forPageNotFound('Kegiatan tidak ditemukan.');
        }

        return view('agenda/detail', ['agenda' => $agenda]);
    }
}
