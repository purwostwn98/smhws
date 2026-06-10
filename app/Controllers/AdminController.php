<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\KonselorModel;
use App\Models\UserModel;
use App\Models\HasilKonselingModel;
use App\Models\DassAssessmentModel;

class AdminController extends BaseController
{
    public function dashboard(): string
    {
        $db            = \Config\Database::connect();
        $janjiModel    = new JanjiModel();
        $konselorModel = new KonselorModel();
        $userModel     = new UserModel();

        // ── Stat kartu ──────────────────────────────────────────────────────
        $stats = [
            'janji_menunggu'  => (new JanjiModel())->where('status', 'menunggu')->countAllResults(),
            'janji_hari_ini'  => (new JanjiModel())
                ->whereIn('status', ['dikonfirmasi', 'berlangsung'])
                ->where('tanggal_konseling', date('Y-m-d'))
                ->countAllResults(),
            'total_mahasiswa' => $userModel->mahasiswa()->countAllResults(),
            'konselor_aktif'  => $konselorModel->where('is_available', 1)->countAllResults(),
        ];

        // ── Distribusi status (satu query) ───────────────────────────────────
        $statusRows  = $db->query(
            "SELECT status, COUNT(*) AS cnt FROM janji WHERE deleted_at IS NULL GROUP BY status"
        )->getResultArray();
        $statusDist  = array_fill_keys(['menunggu', 'dikonfirmasi', 'berlangsung', 'selesai', 'dibatalkan'], 0);
        foreach ($statusRows as $r) {
            $statusDist[$r['status']] = (int) $r['cnt'];
        }
        $totalJanji = array_sum($statusDist);

        // ── Janji paling baru berstatus menunggu ─────────────────────────────
        $janjiMenunggu = $janjiModel->withDetail()
            ->where('janji.status', 'menunggu')
            ->orderBy('janji.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        // Tandai janji mana yang punya safety flag
        $safetyFlagIds = [];
        if (! empty($janjiMenunggu)) {
            $ids     = array_column($janjiMenunggu, 'id');
            $flagRows = $db->table('janji_safety_screening')
                ->select('janji_id')
                ->whereIn('janji_id', $ids)
                ->groupStart()
                    ->where('pikiran_mengakhiri_hidup', 'ya')
                    ->orWhere('pernah_selfharm', 'ya')
                ->groupEnd()
                ->get()->getResultArray();
            $safetyFlagIds = array_column($flagRows, 'janji_id');
        }

        // ── Safety screening aktif (semua status aktif yang bermasalah) ──────
        $safetyAlerts = $db->table('janji')
            ->select('janji.id, janji.status, janji.created_at, users.name AS mahasiswa_nama,
                      jss.pernah_selfharm, jss.pikiran_mengakhiri_hidup, jss.pikiran_mengganggu')
            ->join('users', 'users.id = janji.user_id')
            ->join('janji_safety_screening jss', 'jss.janji_id = janji.id')
            ->where('janji.deleted_at', null)
            ->whereIn('janji.status', ['menunggu', 'dikonfirmasi', 'berlangsung'])
            ->groupStart()
                ->where('jss.pikiran_mengakhiri_hidup', 'ya')
                ->orWhere('jss.pernah_selfharm', 'ya')
            ->groupEnd()
            ->orderBy('janji.created_at', 'DESC')
            ->limit(6)
            ->get()->getResultArray();

        // ── Daftar konselor ──────────────────────────────────────────────────
        $konselorList = $konselorModel->withUser()
            ->orderBy('konselor.is_available', 'DESC')
            ->orderBy('konselor.total_sesi', 'DESC')
            ->findAll();

        // ── DASS overview ────────────────────────────────────────────────────
        $dassStats = $db->query("
            SELECT
                COALESCE(SUM(kategori_depresi IN ('berat','sangat_berat')), 0) AS depresi_berat,
                COALESCE(SUM(kategori_anxiety IN ('berat','sangat_berat')), 0) AS anxiety_berat,
                COALESCE(SUM(kategori_stress  IN ('berat','sangat_berat')), 0) AS stress_berat,
                COUNT(*) AS total
            FROM dass_assessments WHERE deleted_at IS NULL
        ")->getRowArray() ?? ['depresi_berat' => 0, 'anxiety_berat' => 0, 'stress_berat' => 0, 'total' => 0];

        return view('admin/dashboard', [
            'stats'         => $stats,
            'statusDist'    => $statusDist,
            'totalJanji'    => $totalJanji,
            'janjiMenunggu' => $janjiMenunggu,
            'safetyFlagIds' => $safetyFlagIds,
            'safetyAlerts'  => $safetyAlerts,
            'konselorList'  => $konselorList,
            'dassStats'     => $dassStats,
        ]);
    }

    // ── Kelola Janji ────────────────────────────────────────────────────────

    /** GET /admin/janji */
    public function janjiList(): string
    {
        $db         = \Config\Database::connect();
        $janjiModel = new JanjiModel();

        $statusFilter = $this->request->getGet('status') ?? 'semua';
        $validStatus  = ['semua', 'menunggu', 'dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
        if (! in_array($statusFilter, $validStatus)) $statusFilter = 'semua';

        $q = $janjiModel->withDetail()->orderBy('janji.created_at', 'DESC');
        if ($statusFilter !== 'semua') {
            $q->where('janji.status', $statusFilter);
        }
        $daftarJanji = $q->findAll();

        // Hitung per-status untuk badge tab
        $countRows = $db->query(
            "SELECT status, COUNT(*) AS cnt FROM janji WHERE deleted_at IS NULL GROUP BY status"
        )->getResultArray();
        $counts = array_fill_keys($validStatus, 0);
        $counts['semua'] = 0;
        foreach ($countRows as $r) {
            $counts[$r['status']] = (int) $r['cnt'];
            $counts['semua']     += (int) $r['cnt'];
        }

        // Safety flag ids
        $safetyFlagIds = [];
        if (! empty($daftarJanji)) {
            $ids      = array_column($daftarJanji, 'id');
            $flagRows = $db->table('janji_safety_screening')
                ->select('janji_id')
                ->whereIn('janji_id', $ids)
                ->groupStart()
                    ->where('pikiran_mengakhiri_hidup', 'ya')
                    ->orWhere('pernah_selfharm', 'ya')
                ->groupEnd()
                ->get()->getResultArray();
            $safetyFlagIds = array_column($flagRows, 'janji_id');
        }

        return view('admin/janji/index', [
            'daftarJanji'   => $daftarJanji,
            'counts'        => $counts,
            'activeTab'     => $statusFilter,
            'safetyFlagIds' => $safetyFlagIds,
        ]);
    }

    /** GET /admin/janji/:id */
    public function janjiDetail(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $db         = \Config\Database::connect();
        $janjiModel = new JanjiModel();

        $janji = $janjiModel->withDetail()->where('janji.id', $id)->first();
        if (! $janji) {
            return redirect()->to(base_url('admin/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        $konselorModel = new KonselorModel();
        $konselorList  = $konselorModel->withUser()->available()->findAll();

        // Konselor yang dipilih mahasiswa
        $konselorPilihanList = [];
        $pilihanIds = $janji['konselor_pilihan'] ?? [];
        if (! is_array($pilihanIds)) $pilihanIds = [];
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

        // Konselor ditetapkan admin
        $konselorNama = null;
        if (! empty($janji['konselor_id'])) {
            $k = $konselorModel
                ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
                ->join('users', 'users.id = konselor.user_id')
                ->find($janji['konselor_id']);
            if ($k) $konselorNama = KonselorModel::namaLengkap($k);
        }

        // DASS
        $dassModel = new DassAssessmentModel();
        $dass = $dassModel->where('janji_id', $id)->first();

        // Safety screening
        $safety = $db->table('janji_safety_screening')
            ->where('janji_id', $id)->get()->getRowArray();

        // Hasil konseling (jika sudah ada)
        $hasilModel = new HasilKonselingModel();
        $hasil = $hasilModel->byJanji($id);

        return view('admin/janji/detail', [
            'janji'               => $janji,
            'konselorList'        => $konselorList,
            'konselorNama'        => $konselorNama,
            'konselorPilihanList' => $konselorPilihanList,
            'dass'                => $dass,
            'safety'              => $safety,
            'hasil'               => $hasil,
        ]);
    }

    /** POST /admin/janji/proses/:id — tetapkan konselor + jadwal */
    public function prosesJanji(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji) {
            return redirect()->to(base_url('admin/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        $post = $this->request->getPost();

        $updateData = [
            'konselor_id'       => (int) $post['konselor_id'],
            'tanggal_konseling' => $post['tanggal_konseling'],
            'jam_konseling'     => $post['jam_konseling'],
            'lokasi_link'       => $post['lokasi_link'] ?? null,
            'catatan_admin'     => $post['catatan_admin'] ?? null,
            'status'            => 'dikonfirmasi',
        ];

        $janjiModel->update($id, $updateData);

        return redirect()->to(base_url('admin/janji/' . $id))
            ->with('success', 'Jadwal konseling berhasil ditetapkan. Menunggu konfirmasi mahasiswa.');
    }

    /** POST /admin/janji/batal/:id */
    public function batalJanji(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji) {
            return redirect()->to(base_url('admin/janji'))
                ->with('error', 'Janji tidak ditemukan.');
        }

        $janjiModel->update($id, ['status' => 'dibatalkan']);

        return redirect()->to(base_url('admin/janji/' . $id))
            ->with('success', 'Janji #' . str_pad($id, 5, '0', STR_PAD_LEFT) . ' telah dibatalkan.');
    }

    /** POST /admin/janji/mulai/:id — ubah status jadi berlangsung */
    public function mulaiJanji(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['status'] !== 'terjadwal') {
            return redirect()->to(base_url('admin/janji/' . $id))
                ->with('error', 'Hanya janji berstatus Terjadwal yang dapat dimulai.');
        }

        $janjiModel->update($id, ['status' => 'berlangsung']);

        return redirect()->to(base_url('admin/janji/' . $id))
            ->with('success', 'Sesi konseling dimulai.');
    }

    // ── Kelola Konselor ─────────────────────────────────────────────────────

    /** GET /admin/konselor */
    public function konselorList(): string
    {
        $konselorModel = new KonselorModel();
        $list = $konselorModel->withUser()
            ->orderBy('konselor.is_available', 'DESC')
            ->orderBy('konselor.total_sesi', 'DESC')
            ->findAll();

        $stats = [
            'total'    => count($list),
            'tersedia' => count(array_filter($list, fn($k) => $k['is_available'])),
            'total_sesi' => array_sum(array_column($list, 'total_sesi')),
        ];

        return view('admin/konselor/index', [
            'konselorList' => $list,
            'stats'        => $stats,
        ]);
    }

    /** GET /admin/konselor/buat */
    public function konselorBuat(): string
    {
        return view('admin/konselor/form', ['konselor' => null, 'user' => null]);
    }

    /** POST /admin/konselor/simpan */
    public function konselorSimpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post          = $this->request->getPost();
        $userModel     = new UserModel();
        $konselorModel = new KonselorModel();
        $db            = \Config\Database::connect();

        if ($userModel->where('email', $post['email'])->first()) {
            return redirect()->back()->withInput()
                ->with('error', 'Email sudah terdaftar.');
        }

        if (empty($post['password']) || strlen($post['password']) < 8) {
            return redirect()->back()->withInput()
                ->with('error', 'Password minimal 8 karakter.');
        }

        $db->transStart();

        $userId = $userModel->skipValidation(true)->insert([
            'name'              => $post['name'],
            'email'             => $post['email'],
            'password'          => $post['password'],
            'role'              => 'konselor',
            'nim_nip'           => $post['nim_nip'] ?? null,
            'phone'             => $post['phone'] ?? null,
            'is_superadmin'     => 0,
            'is_admin_fakultas' => 0,
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
        ], true);

        $spesialisasi = $this->parseSpesialisasi($post['spesialisasi'] ?? '');

        $konselorModel->insert([
            'user_id'             => $userId,
            'nip'                 => $post['nim_nip'] ?? null,
            'uniid'               => ! empty($post['is_dosen']) ? ($post['uniid'] ?? null) : null,
            'gelar_depan'         => $post['gelar_depan'] ?? null,
            'gelar_belakang'      => $post['gelar_belakang'] ?? null,
            'spesialisasi'        => json_encode($spesialisasi),
            'bio'                 => $post['bio'] ?? null,
            'no_str'              => $post['no_str'] ?? null,
            'tahun_pengalaman'    => (int) ($post['tahun_pengalaman'] ?? 0),
            'max_pasien_per_hari' => (int) ($post['max_pasien_per_hari'] ?? 5),
            'is_available'        => isset($post['is_available']) ? 1 : 0,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data konselor.');
        }

        return redirect()->to(base_url('admin/konselor'))
            ->with('success', 'Konselor berhasil ditambahkan.');
    }

    /** GET /admin/konselor/edit/:id */
    public function konselorEdit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konselorModel = new KonselorModel();
        $userModel     = new UserModel();

        $konselor = $konselorModel->find($id);
        if (! $konselor) {
            return redirect()->to(base_url('admin/konselor'))
                ->with('error', 'Konselor tidak ditemukan.');
        }

        $user = $userModel->find($konselor['user_id']);

        return view('admin/konselor/form', [
            'konselor' => $konselor,
            'user'     => $user,
        ]);
    }

    /** POST /admin/konselor/update/:id */
    public function konselorUpdate(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $post          = $this->request->getPost();
        $konselorModel = new KonselorModel();
        $userModel     = new UserModel();
        $db            = \Config\Database::connect();

        $konselor = $konselorModel->find($id);
        if (! $konselor) {
            return redirect()->to(base_url('admin/konselor'))
                ->with('error', 'Konselor tidak ditemukan.');
        }

        // Cek email unik (kecuali milik user sendiri)
        $existing = $userModel->where('email', $post['email'])
            ->where('id !=', $konselor['user_id'])->first();
        if ($existing) {
            return redirect()->back()->withInput()
                ->with('error', 'Email sudah digunakan akun lain.');
        }

        $db->transStart();

        $userData = [
            'name'    => $post['name'],
            'email'   => $post['email'],
            'phone'   => $post['phone'] ?? null,
            'nim_nip' => $post['nim_nip'] ?? null,
        ];
        if (! empty($post['password'])) {
            $userData['password'] = $post['password'];
        }
        $userModel->skipValidation(true)->update($konselor['user_id'], $userData);

        $spesialisasi = $this->parseSpesialisasi($post['spesialisasi'] ?? '');

        $konselorModel->update($id, [
            'nip'                 => $post['nim_nip'] ?? null,
            'uniid'               => ! empty($post['is_dosen']) ? ($post['uniid'] ?? null) : null,
            'gelar_depan'         => $post['gelar_depan'] ?? null,
            'gelar_belakang'      => $post['gelar_belakang'] ?? null,
            'spesialisasi'        => json_encode($spesialisasi),
            'bio'                 => $post['bio'] ?? null,
            'no_str'              => $post['no_str'] ?? null,
            'tahun_pengalaman'    => (int) ($post['tahun_pengalaman'] ?? 0),
            'max_pasien_per_hari' => (int) ($post['max_pasien_per_hari'] ?? 5),
            'is_available'        => isset($post['is_available']) ? 1 : 0,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data konselor.');
        }

        return redirect()->to(base_url('admin/konselor'))
            ->with('success', 'Data konselor berhasil diperbarui.');
    }

    /** POST /admin/konselor/hapus/:id */
    public function konselorHapus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $konselorModel = new KonselorModel();
        $userModel     = new UserModel();
        $db            = \Config\Database::connect();

        $konselor = $konselorModel->find($id);
        if (! $konselor) {
            return redirect()->to(base_url('admin/konselor'))
                ->with('error', 'Konselor tidak ditemukan.');
        }

        $db->transStart();
        $konselorModel->delete($id);
        $userModel->delete($konselor['user_id']);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(base_url('admin/konselor'))
                ->with('error', 'Gagal menghapus konselor.');
        }

        return redirect()->to(base_url('admin/konselor'))
            ->with('success', 'Konselor berhasil dihapus.');
    }

    /** POST /admin/konselor/toggle/:id — toggle is_available */
    public function konselorToggle(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $konselorModel = new KonselorModel();
        $konselor      = $konselorModel->find($id);

        if (! $konselor) {
            return redirect()->to(base_url('admin/konselor'))
                ->with('error', 'Konselor tidak ditemukan.');
        }

        $newStatus = $konselor['is_available'] ? 0 : 1;
        $konselorModel->update($id, ['is_available' => $newStatus]);

        $label = $newStatus ? 'Tersedia' : 'Tidak Tersedia';
        return redirect()->to(base_url('admin/konselor'))
            ->with('success', 'Status konselor berhasil diubah menjadi ' . $label . '.');
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function parseSpesialisasi(string $raw): array
    {
        if (empty(trim($raw))) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
