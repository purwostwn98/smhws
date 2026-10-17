<?php

namespace App\Controllers;

use App\Models\DassAssessmentModel;
use App\Models\SkriningPublikModel;

/**
 * Rekap hasil Mental Health Check (skrining publik). Saat ini: DASS-21.
 * Dilindungi filter superadmin untuk URL admin/*.
 */
class RekapSkriningController extends BaseController
{
    private const PER_HALAMAN = 20;
    private const SUBSKALA    = ['depresi' => 'Depresi', 'anxiety' => 'Kecemasan', 'stress' => 'Stres'];
    private const KATEGORI    = ['normal', 'ringan', 'sedang', 'berat', 'sangat_berat'];

    public function index(): string
    {
        $filters = $this->filters();
        $model   = new SkriningPublikModel();
        $this->terapkanFilter($model, $filters);

        $list  = $model->orderBy('created_at', 'DESC')->paginate(self::PER_HALAMAN);
        $total = $model->pager->getTotal();

        return view('admin/rekap_skrining/index', [
            'list'    => $list,
            'pager'   => $model->pager,
            'total'   => $total,
            'filters' => $filters,
            'stats'   => $this->statistik($filters),
            'opsi'    => $this->opsiFilter(),
            'queryString' => http_build_query(array_filter($filters, fn($v) => $v !== '')),
        ]);
    }

    public function detail(int $id)
    {
        $row = (new SkriningPublikModel())->find($id);
        if (! $row) {
            return redirect()->to(base_url('admin/rekap-skrining'))->with('error', 'Data skrining tidak ditemukan.');
        }

        $jawaban = \Config\Database::connect()->table('skrining_publik_jawaban j')
            ->select('j.nomor_item, j.subscale, j.jawaban, i.pernyataan')
            ->join('dass_items i', 'i.id = j.item_id')
            ->where('j.skrining_id', $id)
            ->orderBy('j.nomor_item')
            ->get()->getResultArray();

        return view('admin/rekap_skrining/detail', ['row' => $row, 'jawaban' => $jawaban]);
    }

    /** Unduh CSV (hasil sesuai filter). */
    public function export()
    {
        $model = new SkriningPublikModel();
        $this->terapkanFilter($model, $this->filters());
        $rows = $model->orderBy('created_at', 'DESC')->findAll();

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
        fputcsv($out, ['No', 'Tanggal', 'Jenis Kelamin', 'Semester', 'Fakultas', 'Program Studi',
            'Skor Depresi', 'Kategori Depresi', 'Skor Kecemasan', 'Kategori Kecemasan', 'Skor Stres', 'Kategori Stres']);
        foreach ($rows as $i => $r) {
            fputcsv($out, [
                $i + 1, $r['created_at'], $r['jenis_kelamin'], $r['semester'], $r['nama_fakultas'], $r['nama_prodi'],
                $r['skor_depresi'], DassAssessmentModel::labelKategori($r['kategori_depresi']),
                $r['skor_anxiety'], DassAssessmentModel::labelKategori($r['kategori_anxiety']),
                $r['skor_stress'],  DassAssessmentModel::labelKategori($r['kategori_stress']),
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="rekap-skrining-dass-' . date('Ymd') . '.csv"')
            ->setBody($csv);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    private function filters(): array
    {
        $g = fn(string $k) => trim((string) ($this->request->getGet($k) ?? ''));
        return [
            'fakultas'      => $g('fakultas'),
            'id_lembaga'    => $g('id_lembaga'),
            'jenis_kelamin' => in_array($g('jenis_kelamin'), ['laki-laki', 'perempuan'], true) ? $g('jenis_kelamin') : '',
            'semester'      => ctype_digit($g('semester')) ? $g('semester') : '',
            'dari'          => preg_match('/^\d{4}-\d{2}-\d{2}$/', $g('dari'))   ? $g('dari')   : '',
            'sampai'        => preg_match('/^\d{4}-\d{2}-\d{2}$/', $g('sampai')) ? $g('sampai') : '',
        ];
    }

    /** Terapkan filter ke model/builder (where saja). */
    private function terapkanFilter($b, array $f): void
    {
        if ($f['fakultas'] !== '')      $b->where('nama_fakultas', $f['fakultas']);
        if ($f['id_lembaga'] !== '')    $b->where('id_lembaga', $f['id_lembaga']);
        if ($f['jenis_kelamin'] !== '') $b->where('jenis_kelamin', $f['jenis_kelamin']);
        if ($f['semester'] !== '')      $b->where('semester', (int) $f['semester']);
        if ($f['dari'] !== '')          $b->where('created_at >=', $f['dari'] . ' 00:00:00');
        if ($f['sampai'] !== '')        $b->where('created_at <=', $f['sampai'] . ' 23:59:59');
    }

    /** Jumlah per kategori untuk tiap subskala: [subskala => [kategori => jumlah]]. */
    private function statistik(array $filters): array
    {
        $stats = [];
        foreach (array_keys(self::SUBSKALA) as $sub) {
            $b = \Config\Database::connect()->table('skrining_publik');
            $this->terapkanFilter($b, $filters);
            $rows = $b->select("kategori_{$sub} AS kat, COUNT(*) AS n")->groupBy("kategori_{$sub}")->get()->getResultArray();

            $stats[$sub] = array_fill_keys(self::KATEGORI, 0);
            foreach ($rows as $r) {
                $stats[$sub][$r['kat']] = (int) $r['n'];
            }
        }
        return $stats;
    }

    private function opsiFilter(): array
    {
        $db = \Config\Database::connect();
        return [
            'fakultas' => array_column($db->table('skrining_publik')->select('nama_fakultas')->distinct()
                ->where('nama_fakultas IS NOT NULL')->orderBy('nama_fakultas')->get()->getResultArray(), 'nama_fakultas'),
            'prodi'    => $db->table('skrining_publik')->select('id_lembaga, nama_prodi')->distinct()
                ->orderBy('nama_prodi')->get()->getResultArray(),
            'semester' => range(1, 14),
        ];
    }
}
