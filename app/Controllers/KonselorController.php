<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\KonselorModel;
use App\Models\HasilKonselingModel;
use App\Models\DassAssessmentModel;
use App\Models\InstansiRujukanModel;

class KonselorController extends BaseController
{
    private function getKonselorRecord(): ?array
    {
        $konselorModel = new KonselorModel();
        return $konselorModel
            ->where('user_id', session()->get('user_id'))
            ->first();
    }

    /** GET /konselor/dashboard */
    public function dashboard(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Data konselor tidak ditemukan.');
        }

        $db         = \Config\Database::connect();
        $janjiModel = new JanjiModel();

        $stats = [
            'terjadwal'   => $janjiModel->where('konselor_id', $konselor['id'])
                                        ->whereIn('status', ['dikonfirmasi', 'terjadwal'])
                                        ->countAllResults(),
            'berlangsung' => $janjiModel->where('konselor_id', $konselor['id'])
                                        ->where('status', 'berlangsung')
                                        ->countAllResults(),
            'selesai'     => $janjiModel->where('konselor_id', $konselor['id'])
                                        ->where('status', 'selesai')
                                        ->countAllResults(),
            'menunggu_hasil' => $db->table('janji')
                ->where('konselor_id', $konselor['id'])
                ->where('status', 'berlangsung')
                ->where('deleted_at', null)
                ->countAllResults(),
        ];

        // Sesi mendatang
        $sesiMendatang = $janjiModel
            ->withDetail()
            ->where('janji.konselor_id', $konselor['id'])
            ->whereIn('janji.status', ['dikonfirmasi', 'terjadwal', 'berlangsung'])
            ->orderBy('janji.tanggal_konseling', 'ASC')
            ->orderBy('janji.jam_konseling', 'ASC')
            ->limit(10)
            ->findAll();

        // Sesi yang menunggu pengisian hasil
        $menungguHasil = $janjiModel
            ->withDetail()
            ->where('janji.konselor_id', $konselor['id'])
            ->where('janji.status', 'berlangsung')
            ->orderBy('janji.tanggal_konseling', 'DESC')
            ->limit(5)
            ->findAll();

        return view('konselor/dashboard', [
            'konselor'      => $konselor,
            'stats'         => $stats,
            'sesiMendatang' => $sesiMendatang,
            'menungguHasil' => $menungguHasil,
        ]);
    }

    /** GET /konselor/janji */
    public function janjiList(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Data konselor tidak ditemukan.');
        }

        $janjiModel   = new JanjiModel();
        $statusFilter = $this->request->getGet('status') ?? 'semua';
        $validStatus  = ['semua', 'dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
        if (! in_array($statusFilter, $validStatus)) $statusFilter = 'semua';

        $q = $janjiModel->withDetail()
            ->where('janji.konselor_id', $konselor['id'])
            ->orderBy('janji.tanggal_konseling', 'DESC');
        if ($statusFilter !== 'semua') {
            $q->where('janji.status', $statusFilter);
        }
        $daftarJanji = $q->findAll();

        $db = \Config\Database::connect();
        $countRows = $db->query(
            "SELECT status, COUNT(*) AS cnt FROM janji
             WHERE konselor_id = ? AND deleted_at IS NULL GROUP BY status",
            [$konselor['id']]
        )->getResultArray();
        $counts = array_fill_keys($validStatus, 0);
        $counts['semua'] = 0;
        foreach ($countRows as $r) {
            if (isset($counts[$r['status']])) $counts[$r['status']] = (int) $r['cnt'];
            $counts['semua'] += (int) $r['cnt'];
        }

        return view('konselor/janji/index', [
            'konselor'    => $konselor,
            'daftarJanji' => $daftarJanji,
            'counts'      => $counts,
            'activeTab'   => $statusFilter,
        ]);
    }

    /** GET /konselor/janji/:id */
    public function janjiDetail(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Data konselor tidak ditemukan.');
        }

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->withDetail()->where('janji.id', $id)->first();

        if (! $janji || $janji['konselor_id'] != $konselor['id']) {
            return redirect()->to(base_url('konselor/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        $db     = \Config\Database::connect();
        $safety = $db->table('janji_safety_screening')
            ->where('janji_id', $id)->get()->getRowArray();

        $dassModel = new DassAssessmentModel();
        $dass = $dassModel->where('janji_id', $id)->first();

        $hasilModel = new HasilKonselingModel();
        $hasil = $hasilModel->byJanji($id);

        $instansiModel   = new InstansiRujukanModel();
        $instansiRujukan = $instansiModel->getAll();

        return view('konselor/janji/detail', [
            'konselor'        => $konselor,
            'janji'           => $janji,
            'safety'          => $safety,
            'dass'            => $dass,
            'hasil'           => $hasil,
            'instansiRujukan' => $instansiRujukan,
        ]);
    }

    /** POST /konselor/janji/hasil/:id — simpan hasil konseling */
    public function isiHasil(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'));
        }

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['konselor_id'] != $konselor['id']) {
            return redirect()->to(base_url('konselor/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        if (! in_array($janji['status'], ['berlangsung', 'terjadwal'])) {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Hasil hanya dapat diisi untuk sesi yang sedang berlangsung.');
        }

        $post       = $this->request->getPost();
        $adaRujukan = isset($post['ada_rujukan']) ? 1 : 0;

        $instansiRujukanId = null;
        $instansiRujukan   = null;
        if ($adaRujukan) {
            $pilihan = $post['instansi_rujukan_id'] ?? null;
            if ($pilihan === 'lainnya') {
                $instansiRujukan = $post['instansi_rujukan'] ?? null;
            } elseif ($pilihan) {
                $instansiRujukanId = (int) $pilihan;
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $hasilModel = new HasilKonselingModel();
        $existing   = $hasilModel->byJanji($id);

        $hasilData = [
            'janji_id'         => $id,
            'konselor_id'      => $konselor['id'],
            'ada_rujukan'         => $adaRujukan,
            'instansi_rujukan_id' => $instansiRujukanId,
            'instansi_rujukan'    => $instansiRujukan,
            'alasan_rujukan'   => $adaRujukan ? ($post['alasan_rujukan'] ?? null) : null,
            'sesi_lanjutan'    => isset($post['sesi_lanjutan']) ? 1 : 0,
            'catatan_sesi'     => $post['catatan_sesi'] ?? null,
        ];

        if ($existing) {
            $hasilModel->update($existing['id'], $hasilData);
        } else {
            $hasilModel->insert($hasilData);
        }

        // Update status janji jadi selesai
        $janjiModel->update($id, ['status' => 'selesai']);

        // Update total_sesi konselor
        $db->table('konselor')
            ->where('id', $konselor['id'])
            ->set('total_sesi', 'total_sesi + 1', false)
            ->update();

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Gagal menyimpan hasil. Silakan coba lagi.');
        }

        return redirect()->to(base_url('konselor/janji/' . $id))
            ->with('success', 'Hasil konseling berhasil disimpan. Mahasiswa dapat memberikan feedback.');
    }

    /** POST /konselor/janji/mulai/:id — ubah status menjadi berlangsung */
    public function mulaiSesi(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'));
        }

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['konselor_id'] != $konselor['id']) {
            return redirect()->to(base_url('konselor/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        if ($janji['status'] !== 'terjadwal') {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Hanya janji berstatus Terjadwal yang dapat dimulai.');
        }

        $janjiModel->update($id, ['status' => 'berlangsung']);

        return redirect()->to(base_url('konselor/janji/' . $id))
            ->with('success', 'Sesi konseling dimulai.');
    }
}
