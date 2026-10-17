<?php

namespace App\Controllers;

use App\Libraries\PolaInternet;
use App\Models\SkriningInternetModel;

/**
 * Rekap hasil skrining Pola Penggunaan Internet (admin). Memakai filter yang sama dengan rekap DASS.
 * Dilindungi filter superadmin untuk URL admin/*.
 */
class RekapInternetController extends RekapSkriningController
{
    public function index(): string
    {
        $filters = $this->filters();
        $model   = new SkriningInternetModel();
        $this->terapkanFilter($model, $filters);

        $list = $model->orderBy('created_at', 'DESC')->paginate(self::PER_HALAMAN);

        return view('admin/rekap_skrining/internet_index', [
            'list'        => $list,
            'pager'       => $model->pager,
            'total'       => $model->pager->getTotal(),
            'filters'     => $filters,
            'stats'       => $this->statistikInternet($filters),
            'opsi'        => $this->opsiFilter('skrining_internet'),
            'jenisAktif'  => 'internet',
            'queryString' => http_build_query(array_filter($filters, fn($v) => $v !== '')),
        ]);
    }

    public function detail(int $id)
    {
        $row = (new SkriningInternetModel())->find($id);
        if (! $row) {
            return redirect()->to(base_url('admin/rekap-skrining/internet'))->with('error', 'Data skrining tidak ditemukan.');
        }

        $jawaban = array_column(
            \Config\Database::connect()->table('skrining_internet_jawaban')
                ->where('skrining_id', $id)->orderBy('nomor_item')->get()->getResultArray(),
            'jawaban',
            'nomor_item'
        );

        return view('admin/rekap_skrining/internet_detail', ['row' => $row, 'jawaban' => $jawaban]);
    }

    public function export()
    {
        $model = new SkriningInternetModel();
        $this->terapkanFilter($model, $this->filters());
        $rows  = $model->orderBy('created_at', 'DESC')->findAll();

        // Jawaban per butir untuk semua baris (satu query)
        $jawaban = [];
        if ($rows) {
            foreach (\Config\Database::connect()->table('skrining_internet_jawaban')
                         ->whereIn('skrining_id', array_column($rows, 'id'))->get()->getResultArray() as $j) {
                $jawaban[$j['skrining_id']][$j['nomor_item']] = $j['jawaban'];
            }
        }

        $nomor  = array_keys(PolaInternet::BUTIR);
        $header = array_merge(['No', 'Tanggal', 'Jenis Kelamin', 'Semester', 'Fakultas', 'Program Studi', 'Skor Total', 'Kategori'],
            array_map(fn($n) => 'Butir ' . $n, $nomor));

        $baris = [];
        foreach ($rows as $i => $r) {
            $baris[] = array_merge(
                [$i + 1, $r['created_at'], $r['jenis_kelamin'], $r['semester'], $r['nama_fakultas'], $r['nama_prodi'],
                 $r['skor_total'], PolaInternet::labelKategori($r['kategori'])],
                array_map(fn($n) => $jawaban[$r['id']][$n] ?? '', $nomor)
            );
        }

        return $this->kirimCsv($header, $baris, 'rekap-pola-penggunaan-internet-' . date('Ymd') . '.csv');
    }

    /** [kategori => jumlah] + rata-rata skor sesuai filter. */
    private function statistikInternet(array $filters): array
    {
        $b = \Config\Database::connect()->table('skrining_internet');
        $this->terapkanFilter($b, $filters);
        $rows = $b->select('kategori, COUNT(*) AS n, SUM(skor_total) AS jml')->groupBy('kategori')->get()->getResultArray();

        $per = array_fill_keys(array_keys(PolaInternet::KATEGORI), 0);
        $n = $jml = 0;
        foreach ($rows as $r) {
            $per[$r['kategori']] = (int) $r['n'];
            $n   += (int) $r['n'];
            $jml += (int) $r['jml'];
        }
        return ['kategori' => $per, 'rata' => $n ? round($jml / $n, 1) : null];
    }
}
