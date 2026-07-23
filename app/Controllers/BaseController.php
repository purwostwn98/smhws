<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Cek apakah user yang sedang login adalah Kaprodi berdasarkan tabel jabatans.
     * Hasilnya di-cache ke session (is_kaprodi, kaprodi_kode_lembaga, kaprodi_prodi_nama).
     * Berlaku untuk role dosen maupun konselor.
     */
    protected function resolveKaprodi(): bool
    {
        if (session()->has('is_kaprodi')) {
            return (bool) session()->get('is_kaprodi');
        }

        $nimNip = strtolower(trim(session()->get('user_uniid') ?? ''));

        if ($nimNip === '') {
            session()->set('is_kaprodi', false);
            return false;
        }

        $db      = \Config\Database::connect();
        $jabatan = $db->table('jabatans')
            ->where('uniid_penjabat', $nimNip)
            ->like('nama', 'kaprodi', 'both')
            ->get()
            ->getRowArray();

        $isKaprodi = ! empty($jabatan);
        session()->set('is_kaprodi', $isKaprodi);

        if ($isKaprodi) {
            session()->set('kaprodi_kode_lembaga', $jabatan['kode_lembaga'] ?? '');
            session()->set('kaprodi_prodi_nama',   $jabatan['unit']         ?? '');
        }

        return $isKaprodi;
    }

    /**
     * Cek apakah user yang sedang login adalah Dekan berdasarkan tabel jabatans.
     * Hasilnya di-cache ke session (is_dekan, dekan_kode_lembaga, dekan_fakultas_nama).
     */
    protected function resolveDekan(): bool
    {
        if (session()->has('is_dekan')) {
            return (bool) session()->get('is_dekan');
        }

        $nimNip = strtolower(trim(session()->get('user_uniid') ?? ''));

        if ($nimNip === '') {
            session()->set('is_dekan', false);
            return false;
        }

        $db      = \Config\Database::connect();
        $jabatan = $db->table('jabatans')
            ->where('uniid_penjabat', $nimNip)
            ->like('nama', 'dekan', 'both')
            ->get()
            ->getRowArray();

        $isDekan = ! empty($jabatan);
        session()->set('is_dekan', $isDekan);

        if ($isDekan) {
            session()->set('dekan_kode_lembaga',  $jabatan['kode_lembaga'] ?? '');
            session()->set('dekan_fakultas_nama', $jabatan['unit']         ?? '');
        }

        return $isDekan;
    }

    /**
     * Bangun statistik konseling untuk satu prodi ($kodeLembaga) atau semua prodi ($kodeLembaga = '').
     */
    /**
     * @param string|array $kode  '' atau [] = semua prodi; string = satu prodi; array = beberapa prodi (satu fakultas)
     */
    /**
     * Buat base QueryBuilder janji yang sudah difilter (prodi + filters).
     * Dapat di-clone untuk berbagai agregasi.
     */
    protected function buildJanjiBase(
        \CodeIgniter\Database\BaseConnection $db,
        string|array $kode,
        array $filters = []
    ): \CodeIgniter\Database\BaseBuilder {
        $base = $db->table('janji j')
            ->join('users u', 'u.id = j.user_id')
            ->where('u.role', 'mahasiswa')
            ->where('j.deleted_at IS NULL');

        if (is_string($kode) && $kode !== '') {
            $base->join('mahasiswa m', 'm.nim = u.uniid')
                 ->where('m.program_studi', $kode);
        } elseif (is_array($kode) && ! empty($kode)) {
            $base->join('mahasiswa m', 'm.nim = u.uniid')
                 ->whereIn('m.program_studi', $kode);
        }

        if (! empty($filters['jk'])) {
            $base->where('j.jenis_kelamin', $filters['jk']);
        }
        if (! empty($filters['tgl_mulai'])) {
            $base->where('j.created_at >=', $filters['tgl_mulai'] . ' 00:00:00');
        }
        if (! empty($filters['tgl_selesai'])) {
            $base->where('j.created_at <=', $filters['tgl_selesai'] . ' 23:59:59');
        }
        if (! empty($filters['tahun_akd']) && str_contains($filters['tahun_akd'], '/')) {
            [$y1, $y2] = explode('/', $filters['tahun_akd']);
            $smt       = $filters['smt_akd'] ?? '';
            if ($smt === 'ganjil') {
                $base->where('j.created_at >=', "{$y1}-07-01 00:00:00")
                     ->where('j.created_at <=', "{$y1}-12-31 23:59:59");
            } elseif ($smt === 'genap') {
                $base->where('j.created_at >=', "{$y2}-01-01 00:00:00")
                     ->where('j.created_at <=', "{$y2}-06-30 23:59:59");
            } else {
                $base->where('j.created_at >=', "{$y1}-07-01 00:00:00")
                     ->where('j.created_at <=', "{$y2}-06-30 23:59:59");
            }
        }

        return $base;
    }

    protected function buildProdiStats(\CodeIgniter\Database\BaseConnection $db, string|array $kode, array $filters = []): array
    {
        $base = $this->buildJanjiBase($db, $kode, $filters);

        // ── Total & unik ─────────────────────────────────────────────────────
        $total = (clone $base)->countAllResults(false);

        $mahasiswaUnik = (clone $base)
            ->select('COUNT(DISTINCT j.user_id) AS jumlah')
            ->get()->getRow()->jumlah ?? 0;

        // ── Jenis Kelamin ─────────────────────────────────────────────────────
        $jkRows = (clone $base)
            ->select('j.jenis_kelamin, COUNT(*) AS jumlah')
            ->groupBy('j.jenis_kelamin')
            ->get()->getResultArray();
        $jkMap = array_column($jkRows, 'jumlah', 'jenis_kelamin');

        $jkMhsRows = (clone $base)
            ->select('j.jenis_kelamin, COUNT(DISTINCT j.user_id) AS jumlah')
            ->groupBy('j.jenis_kelamin')
            ->get()->getResultArray();
        $jkMahasiswaMap = array_column($jkMhsRows, 'jumlah', 'jenis_kelamin');

        // ── Semester (agregasi PHP) ───────────────────────────────────────────
        $smtRaw    = (clone $base)
            ->select('j.semester, COUNT(*) AS jumlah')
            ->groupBy('j.semester')
            ->get()->getResultArray();
        $smtRawMhs = (clone $base)
            ->select('j.semester, COUNT(DISTINCT j.user_id) AS jumlah')
            ->groupBy('j.semester')
            ->get()->getResultArray();
        $smtGroups       = ['1-2', '3-4', '5-6', '7-8', '9-10', '11-12', '13-14'];
        $smtMap          = array_fill_keys($smtGroups, 0);
        $smtMapMahasiswa = array_fill_keys($smtGroups, 0);
        $smtBucket = static function (int $s): string {
            return match (true) {
                $s <= 2  => '1-2',
                $s <= 4  => '3-4',
                $s <= 6  => '5-6',
                $s <= 8  => '7-8',
                $s <= 10 => '9-10',
                $s <= 12 => '11-12',
                default  => '13-14',
            };
        };
        foreach ($smtRaw as $r) {
            $smtMap[$smtBucket((int) $r['semester'])] += (int) $r['jumlah'];
        }
        foreach ($smtRawMhs as $r) {
            $smtMapMahasiswa[$smtBucket((int) $r['semester'])] += (int) $r['jumlah'];
        }

        // ── Status Janji ──────────────────────────────────────────────────────
        $statusRows = (clone $base)
            ->select('j.status, COUNT(*) AS jumlah')
            ->groupBy('j.status')
            ->get()->getResultArray();
        $statusMap = array_column($statusRows, 'jumlah', 'status');

        // ── Tema Konseling ────────────────────────────────────────────────────
        $temaRows = (clone $base)
            ->select('j.tema_konseling, COUNT(*) AS jumlah')
            ->where('j.tema_konseling IS NOT NULL')
            ->where('j.tema_konseling !=', '')
            ->groupBy('j.tema_konseling')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        // ── Tren bulanan (12 bulan terakhir) ──────────────────────────────────
        $trenRows = (clone $base)
            ->select("DATE_FORMAT(j.created_at, '%Y-%m') AS bulan, COUNT(*) AS jumlah")
            ->where('j.created_at >=', date('Y-m-d', strtotime('-11 months')))
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC')
            ->get()->getResultArray();

        // ── Kumpulkan janji IDs untuk query hasil_konseling ───────────────────
        $idRows   = (clone $base)->select('j.id')->get()->getResultArray();
        $janjiIds = array_column($idRows, 'id');

        // ── Agregasi hasil_konseling (JSON) ───────────────────────────────────
        $hk = $this->aggregateHasilKonseling($db, $janjiIds);

        return [
            'total'              => $total,
            'mahasiswa_unik'     => $mahasiswaUnik,
            'jk'                 => $jkMap,
            'jk_mahasiswa'       => $jkMahasiswaMap,
            'semester'           => $smtMap,
            'semester_mahasiswa' => $smtMapMahasiswa,
            'semester_groups'    => $smtGroups,
            'status'             => $statusMap,
            'tema'               => $temaRows,
            'tren'               => $trenRows,
            'masalah'            => $hk['masalah'],
            'stressor'           => $hk['stressor'],
            'stressor_labels'    => $hk['stressor_labels'],
            'status_kons'        => $hk['status_kons'],
            'status_kons_labels' => $hk['status_kons_labels'],
            'hasil'              => $hk['hasil'],
            'total_selesai'      => $hk['total_selesai'],
            'total_followup'     => $hk['total_followup'],
            'total_dirujuk'      => $hk['total_dirujuk'],
            'total_hk'           => $hk['total_hk'],
        ];
    }

    /**
     * Agregasi field JSON dari tabel hasil_konseling untuk array janji ID yang diberikan.
     */
    protected function aggregateHasilKonseling(\CodeIgniter\Database\BaseConnection $db, array $janjiIds): array
    {
        $empty = [
            'masalah'            => [],
            'stressor'           => [],
            'stressor_labels'    => [],
            'status_kons'        => [],
            'status_kons_labels' => [],
            'hasil'              => [],
            'total_selesai'      => 0,
            'total_followup'     => 0,
            'total_dirujuk'      => 0,
            'total_hk'           => 0,
        ];

        if (empty($janjiIds)) return $empty;

        $rows = $db->table('hasil_konseling')
            ->select('diagnosis, stressor, intervensi, rekomendasi')
            ->whereIn('janji_id', $janjiIds)
            ->where('deleted_at IS NULL')
            ->get()->getResultArray();

        $masalahLabels = [
            'Masalah relasional',
            'Masalah pendidikan dan pekerjaan',
            'Masalah perumahan dan ekonomi',
            'Masalah yang berkaitan dengan lingkungan sosial',
            'Masalah yang berkaitan dengan tindak kriminal atau interaksi dengan sistem hukum',
            'Kunjungan layanan kesehatan untuk konseling atau pemberian nasihat medis',
            'Masalah psikososial, personal, dan lingkungan lainnya',
            'Kondisi lain yang berkaitan dengan riwayat pribadi',
            'Kekerasan dan penelantaran',
            'Perilaku bunuh diri',
            'Perilaku melukai diri (self-harm)',
        ];

        $stressorKeys = [
            'akademik'        => 'Akademik',
            'karier'          => 'Karir & Masa Depan',
            'keluarga'        => 'Keluarga',
            'relasi_sosial'   => 'Relasi Sosial',
            'relasi_romantis' => 'Relasi Romantis',
            'finansial'       => 'Finansial',
            'digital'         => 'Digital & Media Sosial',
            'kesehatan'       => 'Kesehatan',
        ];

        $statusKonsLabels = [
            'Selesai'                            => 'Selesai Penanganan',
            'Monitoring'                         => 'Monitoring',
            'Follow-up'                          => 'Follow-up / Konseling Lanjutan',
            'Rujukan'                            => 'Dirujuk',
            'Terminasi atas kesepakatan bersama' => 'Terminasi atas Kesepakatan Bersama',
        ];

        $hasilOptions = ['Sangat baik', 'Baik', 'Cukup', 'Minimal', 'Belum tampak perubahan'];

        $masalahCount  = array_fill_keys($masalahLabels, 0);
        $stressorCount = array_fill_keys(array_keys($stressorKeys), 0);
        $statusKons    = array_fill_keys(array_keys($statusKonsLabels), 0);
        $hasilCount    = array_fill_keys($hasilOptions, 0);
        $totalSelesai  = 0;
        $totalFollowup = 0;
        $totalDirujuk  = 0;

        foreach ($rows as $row) {
            $diag = json_decode($row['diagnosis'] ?? '{}', true) ?: [];
            $dsm5 = $diag['dsm5'] ?? [];
            if (is_array($dsm5)) {
                foreach ($dsm5 as $item) {
                    if (array_key_exists($item, $masalahCount)) {
                        $masalahCount[$item]++;
                    }
                }
            }

            $str = json_decode($row['stressor'] ?? '{}', true) ?: [];
            foreach (array_keys($stressorKeys) as $key) {
                if (! empty($str[$key])) {
                    $stressorCount[$key]++;
                }
            }

            $rek       = json_decode($row['rekomendasi'] ?? '{}', true) ?: [];
            $statusVal = $rek['status'] ?? null;
            if ($statusVal && array_key_exists($statusVal, $statusKons)) {
                $statusKons[$statusVal]++;
            }
            if ($statusVal === 'Selesai')   $totalSelesai++;
            if ($statusVal === 'Follow-up') $totalFollowup++;
            if ($statusVal === 'Rujukan')   $totalDirujuk++;

            $inv     = json_decode($row['intervensi'] ?? '{}', true) ?: [];
            $respons = $inv['respons'] ?? null;
            if ($respons && array_key_exists($respons, $hasilCount)) {
                $hasilCount[$respons]++;
            }
        }

        arsort($masalahCount);

        return [
            'masalah'            => $masalahCount,
            'stressor'           => $stressorCount,
            'stressor_labels'    => $stressorKeys,
            'status_kons'        => $statusKons,
            'status_kons_labels' => $statusKonsLabels,
            'hasil'              => $hasilCount,
            'total_selesai'      => $totalSelesai,
            'total_followup'     => $totalFollowup,
            'total_dirujuk'      => $totalDirujuk,
            'total_hk'           => count($rows),
        ];
    }
}
