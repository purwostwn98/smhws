<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Libraries\InventoriMasalahUmum as IMU;
use App\Models\DassAssessmentModel;
use App\Models\ImuAssessmentModel;
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

        $userId    = session()->get('user_id');
        $user      = $userModel->find($userId);
        $konselor  = $konselorModel->withUser()->available()->findAll();

        $janjiModel       = new JanjiModel();
        $pernahSelesai    = $janjiModel->where('user_id', $userId)
                                       ->where('status', 'selesai')
                                       ->countAllResults() > 0;

        // Hari praktik tiap psikolog (untuk keterangan pada psikolog yang tidak tersedia)
        $praktik = [];
        $rows = \Config\Database::connect()->table('konselor_jadwal')
            ->select('konselor_id, hari')->distinct()
            ->where('is_active', 1)
            ->get()->getResultArray();
        foreach ($rows as $r) {
            $praktik[(int) $r['konselor_id']][] = $r['hari'];
        }

        return view('janji/buat', [
            'user'          => $user,
            'konselor'      => $konselor,
            'praktikMap'    => $praktik,
            'tanggalMin'    => date('Y-m-d', strtotime('+1 day')),
            'pernahSelesai' => $pernahSelesai,
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
        $jadwalRaw = $post['jadwal_pilihan'] ?? null;
        if ($jadwalRaw) {
            // format: "senin_08:00"
            $parts = explode('_', $jadwalRaw, 2);
            if (count($parts) === 2) {
                $entry = ['hari' => $parts[0], 'waktu' => $parts[1]];
                if (! empty($post['jadwal_tanggal'])) {
                    $entry['tanggal'] = $post['jadwal_tanggal'];
                }
                $jadwalPilihan[] = $entry;
            }
        }

        $konselorPilihan = [];
        if (! empty($post['konselor_pilihan'])) {
            $konselorPilihan = [(int) $post['konselor_pilihan']];
        }

        // Validasi jadwal: tanggal minimal H+1, sesi tersedia, psikolog (bila dipilih) praktik di sesi itu
        // error_step memakai penomoran server (1–4); view menggesernya +1 karena step 1 = informed consent
        if ($errJadwal = $this->validasiJadwal($post)) {
            return redirect()->back()->withInput()
                ->with('error_step', 1)
                ->with('error', $errJadwal);
        }

        $jawaban = $this->ambilJawabanImu($post);
        if ($jawaban === null) {
            return redirect()->back()->withInput()
                ->with('error_step', 3)
                ->with('error', 'Semua ' . count(IMU::BUTIR) . ' pertanyaan Asesmen Awal wajib dijawab.');
        }

        $safety = $this->ambilSafetyScreening($post);
        if ($safety === null) {
            return redirect()->back()->withInput()
                ->with('error_step', 4)
                ->with('error', 'Semua pertanyaan Skrining Keselamatan wajib dijawab.');
        }

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
            'pekerjaan'              => $post['pekerjaan'] ?? null,
            'status_pernikahan'      => $post['status_pernikahan'] ?? 'belum_menikah',
            'pernah_konseling_smhws' => isset($post['pernah_konseling_smhws']) ? 1 : 0,
            'metode'                 => $post['metode'],
            'jadwal_pilihan'         => $jadwalPilihan,
            'konselor_pilihan'       => $konselorPilihan,
            'tema_konseling'         => $post['tema_konseling'] ?? null,
            'keluhan_utama'          => $post['keluhan_utama'],
            'urgensi'                => $post['urgensi'] ?? null,
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
            log_message('error', '[JanjiController::simpan] POST data: ' . json_encode(array_diff_key($post, ['persetujuan' => ''])));

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

        // ── 2. Simpan Asesmen Awal (IMU) dengan janji_id ─────────────────────
        $lainnya     = trim((string) ($post['imu_lainnya_keterangan'] ?? ''));
        $lainnyaSkor = (int) ($post['imu_lainnya_skor'] ?? 0);
        $obat        = trim((string) ($post['imu_obat_keterangan'] ?? ''));

        (new ImuAssessmentModel())->simpanAsesmen([
            'janji_id'           => $janjiId,
            'user_id'            => $userId,
            'obat_keterangan'    => $obat !== '' ? mb_substr($obat, 0, 255) : null,
            'lainnya_keterangan' => $lainnya !== '' ? $lainnya : null,
            'lainnya_skor'       => $lainnya !== '' && isset(IMU::SKALA[$lainnyaSkor]) ? $lainnyaSkor : null,
        ], $jawaban);

        // ── 3. Simpan Safety Screening ───────────────────────────────────────
        $db->table('janji_safety_screening')->insert($safety + [
            'janji_id'   => $janjiId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
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

        // Hapus asesmen awal + jawaban (jawaban ikut CASCADE dari FK); DASS untuk janji lama
        $db->table('imu_assessments')->where('janji_id', $id)->delete();
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

    /** POST /janji/batal/:id — mahasiswa membatalkan konseling */
    public function batalKonseling(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($r = $this->authCheck()) return $r;

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['user_id'] != session()->get('user_id')) {
            return redirect()->to(base_url('janji'))
                ->with('error', 'Konseling tidak ditemukan.');
        }

        if (! in_array($janji['status'], ['menunggu', 'dikonfirmasi'])) {
            return redirect()->to(base_url('janji/' . $id))
                ->with('error', 'Konseling ini tidak dapat dibatalkan.');
        }

        $janjiModel->update($id, ['status' => 'dibatalkan']);

        return redirect()->to(base_url('janji/' . $id))
            ->with('success', 'Konseling #' . str_pad($id, 5, '0', STR_PAD_LEFT) . ' telah dibatalkan.');
    }

    /** GET /janji/konselor-jadwal — API: jadwal tersedia berdasarkan konselor yang dipilih */
    public function konselorJadwal(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $slots = [
            's1' => ['jam' => '08:00:00', 'label' => '08.00–08.50'],
            's2' => ['jam' => '09:20:00', 'label' => '09.20–10.10'],
            's3' => ['jam' => '10:40:00', 'label' => '10.40–11.30'],
            's4' => ['jam' => '12:30:00', 'label' => '12.30–13.20'],
            's5' => ['jam' => '13:50:00', 'label' => '13.50–14.40'],
        ];
        $timeToSlot = array_combine(array_column($slots, 'jam'), array_keys($slots));

        $ids = $this->request->getGet('ids') ?? [];
        if (! is_array($ids)) $ids = [];
        $ids = array_values(array_filter(array_map('intval', $ids)));

        $db = \Config\Database::connect();

        if (empty($ids)) {
            // Tidak ada konselor dipilih → tampilkan semua slot semua hari
            $jadwal = [];
            foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $hari) {
                foreach ($slots as $key => $s) {
                    $jadwal[$hari][$key] = ['jam' => substr($s['jam'], 0, 5), 'metode' => null];
                }
            }
            return $this->response->setJSON(['jadwal' => $jadwal]);
        }

        $rows = $db->table('konselor_jadwal')
            ->whereIn('konselor_id', $ids)
            ->where('is_active', 1)
            ->get()->getResultArray();

        $jadwal = [];
        foreach ($rows as $r) {
            $slotKey = $timeToSlot[$r['jam_mulai']] ?? null;
            if (! $slotKey) continue;
            $hari = $r['hari'];
            if (! isset($jadwal[$hari][$slotKey])) {
                $jadwal[$hari][$slotKey] = [
                    'jam'    => substr($r['jam_mulai'], 0, 5),
                    'metode' => $r['metode'],
                ];
            } else {
                // Gabungkan metode dari beberapa konselor yang berbeda
                if ($jadwal[$hari][$slotKey]['metode'] !== $r['metode']) {
                    $jadwal[$hari][$slotKey]['metode'] = 'keduanya';
                }
            }
        }

        return $this->response->setJSON(['jadwal' => $jadwal]);
    }

    /** Sesi standar konseling (50 menit). */
    private const SESI = [
        's1' => ['jam' => '08:00', 'label' => '08.00–08.50'],
        's2' => ['jam' => '09:20', 'label' => '09.20–10.10'],
        's3' => ['jam' => '10:40', 'label' => '10.40–11.30'],
        's4' => ['jam' => '12:30', 'label' => '12.30–13.20'],
        's5' => ['jam' => '13:50', 'label' => '13.50–14.40'],
    ];

    /**
     * GET /janji/ketersediaan?tanggal=YYYY-MM-DD
     * API: sesi pada tanggal tersebut beserta psikolog yang praktik & belum terisi.
     * Penyaringan metode dilakukan di browser memakai field `metode` tiap psikolog.
     */
    public function ketersediaan(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $tanggal = (string) $this->request->getGet('tanggal');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || ! strtotime($tanggal)) {
            return $this->response->setJSON(['error' => 'Tanggal tidak valid'])->setStatusCode(422);
        }

        return $this->response->setJSON($this->hitungKetersediaan($tanggal));
    }

    /**
     * Ketersediaan sesi pada satu tanggal.
     *
     * @return array{hari:string, umum:bool, sesi:array<string, array{jam:string,label:string,psikolog:list<array{id:int,metode:?string}>}>}
     *   umum = true bila belum ada jadwal psikolog sama sekali (semua sesi Senin–Sabtu terbuka, psikolog dipilih admin).
     */
    private function hitungKetersediaan(string $tanggal): array
    {
        $hariKeys = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $hari     = $hariKeys[(int) date('w', strtotime($tanggal))];
        $db       = \Config\Database::connect();

        $adaJadwal = $db->table('konselor_jadwal')->where('is_active', 1)->countAllResults() > 0;
        if (! $adaJadwal) {
            $sesi = [];
            if ($hari !== 'minggu') {
                foreach (self::SESI as $k => $s) $sesi[$k] = $s + ['psikolog' => []];
            }
            return ['hari' => $hari, 'umum' => true, 'sesi' => $sesi];
        }

        $jamKeSesi = [];
        foreach (self::SESI as $k => $s) $jamKeSesi[$s['jam']] = $k;

        // Psikolog yang praktik di hari tersebut (hanya yang aktif menerima konseling)
        $rows = $db->table('konselor_jadwal j')
            ->select('j.konselor_id, j.jam_mulai, j.metode')
            ->join('konselor k', 'k.id = j.konselor_id')
            ->where('j.is_active', 1)
            ->where('j.hari', $hari)
            ->where('k.is_available', 1)
            ->where('k.deleted_at', null)
            ->get()->getResultArray();

        // Sesi yang sudah terisi per psikolog pada tanggal ini: [konselor_id][HH:MM] = true
        $terisi = [];
        foreach ($db->table('janji')->select('konselor_id, jam_konseling')
                     ->where('tanggal_konseling', $tanggal)
                     ->whereNotIn('status', ['dibatalkan', 'selesai'])
                     ->where('konselor_id IS NOT NULL')->where('jam_konseling IS NOT NULL')
                     ->get()->getResultArray() as $r) {
            $terisi[(int) $r['konselor_id']][substr($r['jam_konseling'], 0, 5)] = true;
        }
        // Permintaan yang masih menunggu dengan psikolog pilihan + jadwal pilihan pada tanggal ini
        foreach ((new JanjiModel())->where('status', 'menunggu')->findAll() as $rec) {
            foreach ((array) ($rec['jadwal_pilihan'] ?? []) as $slot) {
                if (($slot['tanggal'] ?? null) !== $tanggal || empty($slot['waktu'])) continue;
                foreach ((array) ($rec['konselor_pilihan'] ?? []) as $kid) {
                    $terisi[(int) $kid][substr($slot['waktu'], 0, 5)] = true;
                }
            }
        }

        $sesi = [];
        foreach ($rows as $r) {
            $jam = substr($r['jam_mulai'], 0, 5);
            $key = $jamKeSesi[$jam] ?? null;
            $kid = (int) $r['konselor_id'];
            if (! $key || isset($terisi[$kid][$jam])) continue;

            $sesi[$key] ??= self::SESI[$key] + ['psikolog' => []];
            $sesi[$key]['psikolog'][] = ['id' => $kid, 'metode' => $r['metode']];
        }
        // Urutkan sesuai urutan sesi
        $sesi = array_intersect_key(array_merge(array_fill_keys(array_keys(self::SESI), null), $sesi), $sesi);

        return ['hari' => $hari, 'umum' => false, 'sesi' => $sesi];
    }

    /** Metode jadwal psikolog cocok dengan metode yang diminta mahasiswa? (null = belum diatur → dianggap keduanya) */
    private static function metodeCocok(?string $metodeJadwal, string $metodeMahasiswa): bool
    {
        return $metodeJadwal === null || $metodeJadwal === 'keduanya' || $metodeJadwal === $metodeMahasiswa;
    }

    /** Validasi pilihan jadwal di server. Mengembalikan pesan error atau null. */
    private function validasiJadwal(array $post): ?string
    {
        $tanggal = (string) ($post['jadwal_tanggal'] ?? '');
        $pilihan = (string) ($post['jadwal_pilihan'] ?? '');
        $metode  = (string) ($post['metode'] ?? 'offline');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || $pilihan === '') {
            return 'Pilih tanggal dan sesi konseling yang kamu inginkan.';
        }
        if ($tanggal < date('Y-m-d', strtotime('+1 day'))) {
            return 'Tanggal konseling paling cepat besok (H+1).';
        }

        $jam  = explode('_', $pilihan, 2)[1] ?? '';
        $data = $this->hitungKetersediaan($tanggal);
        $sesi = null;
        foreach ($data['sesi'] as $s) {
            if ($s['jam'] === substr($jam, 0, 5)) $sesi = $s;
        }
        if (! $sesi) {
            return 'Sesi yang dipilih sudah tidak tersedia. Silakan pilih sesi lain.';
        }
        if ($data['umum']) {
            return null;
        }

        $cocok = array_filter($sesi['psikolog'], fn($p) => self::metodeCocok($p['metode'], $metode));
        if (! $cocok) {
            return 'Tidak ada psikolog yang melayani konseling ' . ($metode === 'online' ? 'daring' : 'tatap muka') . ' pada sesi tersebut.';
        }

        $kid = (int) ($post['konselor_pilihan'] ?? 0);
        if ($kid && ! in_array($kid, array_column($cocok, 'id'), true)) {
            return 'Psikolog yang dipilih tidak tersedia pada sesi dan metode tersebut. Silakan pilih psikolog lain atau "Siapa saja yang tersedia".';
        }

        return null;
    }

    /**
     * Jawaban Asesmen Awal (IMU) dari POST: [nomor_item => 1..5].
     * Null bila ada butir yang belum dijawab atau nilainya di luar skala.
     */
    private function ambilJawabanImu(array $post): ?array
    {
        $jawaban = [];
        foreach (array_keys(IMU::BUTIR) as $nomor) {
            $val = (string) ($post['imu_item_' . $nomor] ?? '');
            if (! ctype_digit($val) || ! isset(IMU::SKALA[(int) $val])) return null;
            $jawaban[$nomor] = (int) $val;
        }
        return $jawaban;
    }

    /**
     * Data skrining keselamatan dari POST, sudah tervalidasi.
     * Null bila ada pertanyaan wajib yang kosong / bernilai tidak dikenal.
     */
    private function ambilSafetyScreening(array $post): ?array
    {
        $opsi = [
            'pernah_selfharm'          => ['tidak', 'ya', 'tidak_mau_menjawab'],
            'merasa_aman'              => ['ya', 'tidak', 'tidak_mau_menjawab'],
            'pikiran_mengakhiri_hidup' => ['tidak', 'ya', 'tidak_mau_menjawab'],
        ];

        $data = [];
        foreach ($opsi as $field => $valid) {
            $val = $post[$field] ?? null;
            if (! in_array($val, $valid, true)) return null;
            $data[$field] = $val;
        }

        // Pertanyaan lanjutan hanya berlaku bila ada (atau tidak mau menyebut) pikiran mengakhiri hidup
        if ($data['pikiran_mengakhiri_hidup'] === 'tidak') {
            $data['pikiran_mengganggu'] = 'tidak_berlaku';
        } else {
            $val = $post['pikiran_mengganggu'] ?? null;
            if (! in_array($val, ['ya', 'tidak'], true)) return null;
            $data['pikiran_mengganggu'] = $val;
        }

        $keterangan = trim((string) ($post['riwayat_selfharm_keterangan'] ?? ''));
        $data['riwayat_selfharm_keterangan'] = $keterangan !== '' ? $keterangan : null;

        return $data;
    }

    /** GET /janji/slot-booked — API: slot yang sudah terpakai untuk konselor+tanggal tertentu */
    public function slotBooked(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $konselorId = (int)($this->request->getGet('konselor_id') ?? 0);
        $tanggal    = $this->request->getGet('tanggal') ?? '';

        if (! $konselorId || ! $tanggal) {
            return $this->response->setJSON(['booked' => []]);
        }

        $db = \Config\Database::connect();

        // Confirmed/ongoing bookings (admin already set tanggal_konseling + jam_konseling)
        $rows = $db->table('janji')
            ->select('jam_konseling')
            ->where('konselor_id', $konselorId)
            ->where('tanggal_konseling', $tanggal)
            ->whereNotIn('status', ['dibatalkan', 'selesai'])
            ->where('jam_konseling IS NOT NULL')
            ->get()->getResultArray();

        $booked = array_values(array_filter(array_map(
            fn($r) => $r['jam_konseling'] ? substr($r['jam_konseling'], 0, 5) : null,
            $rows
        )));

        // Pending (menunggu) requests: preferred konselor is in konselor_pilihan JSON and
        // preferred date+time is in jadwal_pilihan JSON — neither is set by admin yet.
        // Load through the model so afterFind decodes JSON fields automatically.
        $pending = (new JanjiModel())->where('status', 'menunggu')->findAll();
        foreach ($pending as $record) {
            $kIds = array_map('intval', (array) ($record['konselor_pilihan'] ?? []));
            if (! in_array($konselorId, $kIds, true)) continue;
            foreach ((array) ($record['jadwal_pilihan'] ?? []) as $slot) {
                if (
                    isset($slot['tanggal'], $slot['waktu']) &&
                    $slot['tanggal'] === $tanggal
                ) {
                    $booked[] = $slot['waktu'];
                }
            }
        }

        $booked = array_values(array_unique($booked));

        return $this->response->setJSON(['booked' => $booked]);
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

        // Asesmen awal: IMU (janji baru) atau DASS (janji lama)
        $imu  = (new ImuAssessmentModel())->byJanji($id);
        $dass = $imu ? null : (new DassAssessmentModel())->where('janji_id', $id)->first();

        // Safety screening
        $db = \Config\Database::connect();
        $safety = $db->table('janji_safety_screening')
            ->where('janji_id', $id)
            ->get()->getRowArray();

        // Enrich jadwal_pilihan dengan metode dari konselor_jadwal
        $jadwalPilihan = $janji['jadwal_pilihan'] ?? [];
        if (! is_array($jadwalPilihan)) $jadwalPilihan = json_decode($jadwalPilihan, true) ?: [];
        if (! empty($jadwalPilihan)) {
            $lookupIds = array_map('intval', $pilihanIds);
            if (! empty($janji['konselor_id'])) {
                $lookupIds[] = (int) $janji['konselor_id'];
            }
            $lookupIds = array_values(array_unique(array_filter($lookupIds)));
            if (! empty($lookupIds)) {
                foreach ($jadwalPilihan as &$jSlot) {
                    $rows = $db->table('konselor_jadwal')
                        ->select('metode')
                        ->whereIn('konselor_id', $lookupIds)
                        ->where('hari', $jSlot['hari'])
                        ->where("LEFT(jam_mulai, 5)", $jSlot['waktu'])
                        ->where('is_active', 1)
                        ->get()->getResultArray();
                    if ($rows) {
                        $metodes = array_unique(array_column($rows, 'metode'));
                        $jSlot['metode'] = count($metodes) === 1 ? $metodes[0] : 'keduanya';
                    }
                }
                unset($jSlot);
            }
            $janji['jadwal_pilihan'] = $jadwalPilihan;
        }

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
            'imu'                 => $imu,
            'dass'                => $dass,
            'safety'              => $safety,
            'feedback'            => $feedback,
        ]);
    }
}
