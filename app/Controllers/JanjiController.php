<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\DassItemModel;
use App\Models\DassAssessmentModel;
use App\Models\FeedbackKonselingModel;
use App\Models\KonselorModel;
use App\Models\UserModel;

class JanjiController extends BaseController
{
    private function authCheck(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login?redirect=janji'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }
        return null;
    }

    /** GET /janji/buat — tampilkan form */
    public function buat(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $userModel    = new UserModel();
        $konselorModel = new KonselorModel();
        $dassItemModel = new DassItemModel();

        $user     = $userModel->find(session()->get('user_id'));
        $konselor = $konselorModel->withUser()->available()->findAll();
        $dassItems = $dassItemModel->orderBy('nomor')->findAll();

        return view('janji/buat', [
            'user'      => $user,
            'konselor'  => $konselor,
            'dassItems' => $dassItems,
        ]);
    }

    /** POST /janji/buat — proses simpan form */
    public function simpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $userId = session()->get('user_id');
        $post   = $this->request->getPost();

        // ── 1. Simpan Janji dulu → dapat janji_id ───────────────────────────
        $jadwalPilihan = [];
        foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $hari) {
            $waktu = $post['jadwal_' . $hari] ?? null;
            if ($waktu) {
                $jadwalPilihan[] = ['hari' => $hari, 'waktu' => $waktu];
            }
        }

        $konselorPilihan = $post['konselor_pilihan'] ?? [];
        if (! is_array($konselorPilihan)) $konselorPilihan = [];

        $janjiModel = new JanjiModel();
        $db         = \Config\Database::connect();

        $db->transStart();

        $janjiId = $janjiModel->insert([
            'user_id'                => $userId,
            'jenis_kelamin'          => $post['jenis_kelamin'],
            'usia'                   => (int) $post['usia'],
            'agama'                  => $post['agama'],
            'semester'               => (int) $post['semester'],
            'dosen_pa'               => $post['dosen_pa'] ?? null,
            'domisili'               => $post['domisili'] ?? null,
            'status_pernikahan'      => $post['status_pernikahan'] ?? 'belum_menikah',
            'pernah_konseling_smhws' => isset($post['pernah_konseling_smhws']) ? 1 : 0,
            'metode'                 => $post['metode'],
            'jadwal_pilihan'         => $jadwalPilihan,
            'konselor_pilihan'       => $konselorPilihan,
            'tema_konseling'         => $post['tema_konseling'] ?? null,
            'keluhan_utama'          => $post['keluhan_utama'],
            'mulai_keluhan'          => $post['mulai_keluhan'] ?? null,
            'upaya_dilakukan'        => $post['upaya_dilakukan'] ?? null,
            'status'                 => 'menunggu',
        ], true);

        // Guard: jika insert gagal validasi, janjiId = false → jangan lanjut
        if (! $janjiId) {
            $db->transRollback();
            $validationErrors = $janjiModel->errors();
            $dbError          = $db->error();
            log_message('error', '[JanjiController::simpan] Validation failed: ' . json_encode($validationErrors));
            log_message('error', '[JanjiController::simpan] DB error: ' . json_encode($dbError));
            log_message('error', '[JanjiController::simpan] POST data: ' . json_encode(array_diff_key($post, ['dass_item_1' => '', 'persetujuan' => ''])));

            // Tentukan step pertama yang ada error-nya
            $stepFields = [
                1 => ['jenis_kelamin', 'usia', 'agama', 'semester', 'metode'],
                2 => ['tema_konseling', 'keluhan_utama', 'mulai_keluhan', 'upaya_dilakukan'],
                4 => ['pernah_selfharm', 'merasa_aman', 'pikiran_mengakhiri_hidup'],
            ];
            $errorStep = 1;
            foreach ($stepFields as $step => $fields) {
                foreach ($fields as $f) {
                    if (isset($validationErrors[$f])) { $errorStep = $step; break 2; }
                }
            }

            return redirect()->back()->withInput()
                ->with('errors', $validationErrors)
                ->with('error_step', $errorStep)
                ->with('error', 'Terdapat kesalahan pada formulir. Silakan periksa kembali.');
        }

        // ── 2. Simpan DASS Assessment dengan janji_id ────────────────────────
        $jawaban = [];
        foreach ($post as $key => $val) {
            if (str_starts_with($key, 'dass_item_')) {
                $itemId = (int) str_replace('dass_item_', '', $key);
                $jawaban[$itemId] = (int) $val;
            }
        }

        if (count($jawaban) === 21) {
            $dassModel = new DassAssessmentModel();
            $dassModel->simpanAsesmenLengkap([
                'janji_id'          => $janjiId,
                'user_id'           => $userId,
                'tanggal_pengisian' => date('Y-m-d'),
                'pemeriksa'         => null,
            ], $jawaban);
        }

        // ── 3. Simpan Safety Screening ───────────────────────────────────────
        $db->table('janji_safety_screening')->insert([
            'janji_id'                    => $janjiId,
            'pernah_selfharm'             => $post['pernah_selfharm'] ?? 'tidak',
            'merasa_aman'                 => $post['merasa_aman'] ?? 'ya',
            'pikiran_mengakhiri_hidup'    => $post['pikiran_mengakhiri_hidup'] ?? 'tidak',
            'pikiran_mengganggu'          => $post['pikiran_mengganggu'] ?? 'tidak_berlaku',
            'riwayat_selfharm_keterangan' => $post['riwayat_selfharm_keterangan'] ?? null,
            'created_at'                  => date('Y-m-d H:i:s'),
            'updated_at'                  => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }

        return redirect()->to(base_url('janji/sukses/' . $janjiId))
            ->with('success', 'Pendaftaran konseling berhasil dikirim!');
    }

    /** GET /janji/sukses/:id */
    public function sukses(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $janjiModel = new JanjiModel();
        $janji = $janjiModel->find($id);

        if (! $janji || $janji['user_id'] != session()->get('user_id')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('janji/sukses', ['janji' => $janji]);
    }

    /** POST /janji/hapus/:id — hapus (soft-delete) janji berstatus menunggu */
    public function hapus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $janjiModel = new JanjiModel();
        $janji = $janjiModel->find($id);

        if (! $janji || $janji['user_id'] != session()->get('user_id')) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        if ($janji['status'] !== 'menunggu') {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Hanya janji berstatus Menunggu yang dapat dihapus.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Hapus safety screening terkait
        $db->table('janji_safety_screening')->where('janji_id', $id)->delete();

        // Hapus DASS assessment + jawaban (jawaban ikut CASCADE dari FK)
        $db->table('dass_assessments')->where('janji_id', $id)->delete();

        // Soft-delete janji
        $janjiModel->delete($id);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Gagal menghapus janji. Silakan coba lagi.');
        }

        return redirect()->to(base_url('janji'))
            ->with('success', 'Janji ' . '#' . str_pad($id, 5, '0', STR_PAD_LEFT) . ' berhasil dihapus.');
    }

    /** POST /janji/konfirmasi/:id — mahasiswa konfirmasi kehadiran */
    public function konfirmasiKehadiran(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['user_id'] != session()->get('user_id')) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        if ($janji['status'] !== 'dikonfirmasi') {
            return redirect()->to(base_url('janji/' . $id))
                ->with('error', 'Konfirmasi hanya dapat dilakukan untuk janji berstatus Dikonfirmasi.');
        }

        $janjiModel->update($id, [
            'status'                  => 'terjadwal',
            'mahasiswa_konfirmasi_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('janji/' . $id))
            ->with('success', 'Kehadiran berhasil dikonfirmasi. Sampai jumpa di sesi konselingmu!');
    }

    /** GET /janji — daftar janji milik mahasiswa */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $userId     = session()->get('user_id');
        $janjiModel = new JanjiModel();

        $daftarJanji = $janjiModel->byUser($userId)->findAll();

        // Bangun lookup id => nama konselor untuk tampilan
        $konselorMap = [];
        if (! empty($daftarJanji)) {
            $konselorIds = array_filter(array_unique(array_column($daftarJanji, 'konselor_id')));
            if (! empty($konselorIds)) {
                $konselorModel = new KonselorModel();
                $rows = $konselorModel
                    ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                    ->join('users', 'users.id = konselor.user_id')
                    ->whereIn('konselor.id', $konselorIds)
                    ->findAll();
                foreach ($rows as $k) {
                    $konselorMap[$k['id']] = KonselorModel::namaLengkap($k);
                }
            }
        }

        return view('janji/index', [
            'daftarJanji' => $daftarJanji,
            'konselorMap' => $konselorMap,
        ]);
    }

    /** GET /janji/:id — detail satu janji */
    public function detail(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $janjiModel = new JanjiModel();
        $janji = $janjiModel->find($id);

        if (! $janji || $janji['user_id'] != session()->get('user_id')) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        $konselorModel = new KonselorModel();

        // Konselor yang ditetapkan admin
        $konselorNama = null;
        if (! empty($janji['konselor_id'])) {
            $k = $konselorModel
                ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                ->join('users', 'users.id = konselor.user_id')
                ->find($janji['konselor_id']);
            if ($k) $konselorNama = KonselorModel::namaLengkap($k);
        }

        // Daftar konselor pilihan mahasiswa [id => nama_lengkap]
        $konselorPilihanList = [];
        $pilihanIds = $janji['konselor_pilihan'] ?? [];
        if (! is_array($pilihanIds)) {
            $pilihanIds = [];
        }
        if (! empty($pilihanIds)) {
            $rows = $konselorModel
                ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                ->join('users', 'users.id = konselor.user_id')
                ->whereIn('konselor.id', array_map('intval', $pilihanIds))
                ->findAll();
            foreach ($rows as $k) {
                $konselorPilihanList[$k['id']] = KonselorModel::namaLengkap($k);
            }
        }

        // DASS assessment terkait
        $dassModel = new DassAssessmentModel();
        $dass = $dassModel->where('janji_id', $id)->first();

        // Safety screening
        $db = \Config\Database::connect();
        $safety = $db->table('janji_safety_screening')
            ->where('janji_id', $id)
            ->get()->getRowArray();

        // Feedback (hanya untuk janji selesai)
        $feedback = null;
        if ($janji['status'] === 'selesai') {
            $feedbackModel = new FeedbackKonselingModel();
            $feedback = $feedbackModel->byJanji($id);
        }

        return view('janji/detail', [
            'janji'               => $janji,
            'konselorNama'        => $konselorNama,
            'konselorPilihanList' => $konselorPilihanList,
            'dass'                => $dass,
            'safety'              => $safety,
            'feedback'            => $feedback,
        ]);
    }
}
