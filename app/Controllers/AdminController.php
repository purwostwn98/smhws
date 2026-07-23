<?php

namespace App\Controllers;

use App\Models\JanjiModel;
use App\Models\KonselorModel;
use App\Models\UserModel;
use App\Models\HasilKonselingModel;
use App\Models\DassAssessmentModel;
use App\Models\ChecklistItemModel;
use App\Models\InstansiRujukanModel;

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

        // Preload jadwal semua konselor → dipakai JS form tetapkan jadwal
        $timeToSlot = [
            '08:00:00' => 's1',
            '09:30:00' => 's2',
            '11:00:00' => 's3',
            '12:30:00' => 's4',
            '14:00:00' => 's5',
        ];
        $konselorJadwalMap = [];
        if (! empty($konselorList)) {
            $kIds = array_column($konselorList, 'id');
            $jRows = $db->table('konselor_jadwal')
                ->select('konselor_id, hari, jam_mulai, metode')
                ->whereIn('konselor_id', $kIds)
                ->where('is_active', 1)
                ->get()->getResultArray();
            foreach ($jRows as $r) {
                $slotKey = $timeToSlot[$r['jam_mulai']] ?? null;
                if (! $slotKey) continue;
                $konselorJadwalMap[$r['konselor_id']][$r['hari']][$slotKey] = $r['metode'] ?? null;
            }
        }

        $checklistData   = [];
        $instansiRujukan = [];
        if ($janji['status'] === 'selesai') {
            $checklistData   = (new ChecklistItemModel())->allForForm();
            $instansiRujukan = (new InstansiRujukanModel())->getAll();
        }

        return view('admin/janji/detail', [
            'janji'               => $janji,
            'konselorList'        => $konselorList,
            'konselorNama'        => $konselorNama,
            'konselorPilihanList' => $konselorPilihanList,
            'dass'                => $dass,
            'safety'              => $safety,
            'hasil'               => $hasil,
            'konselorJadwalMap'   => $konselorJadwalMap,
            'checklistData'       => $checklistData,
            'instansiRujukan'     => $instansiRujukan,
        ]);
    }

    /** GET /admin/janji/:id/pdf — ekspor laporan konseling sebagai PDF (TCPDF) */
    public function exportPdf(int $id): mixed
    {
        $db         = \Config\Database::connect();
        $janjiModel = new JanjiModel();

        $janji = $janjiModel->withDetail()->where('janji.id', $id)->first();
        if (! $janji || $janji['status'] !== 'selesai') {
            return redirect()->to(base_url('admin/janji/' . $id))
                ->with('error', 'Laporan hanya tersedia untuk sesi yang sudah selesai.');
        }

        $konselorModel = new KonselorModel();
        $konselorNama  = null;
        $konselorNoStr = '—';
        if (! empty($janji['konselor_id'])) {
            $k = $konselorModel
                ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang, konselor.no_str')
                ->join('users', 'users.id = konselor.user_id')
                ->find($janji['konselor_id']);
            if ($k) {
                $konselorNama  = KonselorModel::namaLengkap($k);
                $konselorNoStr = $k['no_str'] ?? '—';
            }
        }

        $dassModel = new DassAssessmentModel();
        $dass      = $dassModel->where('janji_id', $id)->first();
        $safety    = $db->table('janji_safety_screening')->where('janji_id', $id)->get()->getRowArray();

        $hasilModel      = new HasilKonselingModel();
        $hasil           = $hasilModel->byJanji($id);
        $checklistData   = (new ChecklistItemModel())->allForForm();
        $instansiRujukan = (new InstansiRujukanModel())->getAll();

        $htmlContent = view('admin/janji/pdf_content', [
            'janji'           => $janji,
            'konselorNama'    => $konselorNama,
            'konselorNoStr'   => $konselorNoStr,
            'dass'            => $dass,
            'safety'          => $safety,
            'hasil'           => $hasil,
            'checklistData'   => $checklistData,
            'instansiRujukan' => $instansiRujukan,
        ]);

        $logoPath   = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
        $nomorKlien = '#' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT);
        $headerHtml = <<<HTML
<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="width:72pt;text-align:center;vertical-align:middle;padding-right:8pt;border-left:10pt solid #FFB800;">
      <img src="{$logoPath}" style="width:150pt;" />
    </td>
    <td style="vertical-align:middle;">
      <div style="font-size:10pt;font-weight:bold;color:#1a3a7a;margin-bottom:1pt;">Student Mental Health and Wellbeing Support (SMHWS)</div>
      <div style="font-size:8.5pt;margin-bottom:1pt;">Direktorat Kemahasiswaan dan Pengembangan Talenta-Inovasi</div>
      <div style="font-size:9pt;font-weight:bold;margin-bottom:1pt;">Universitas Muhammadiyah Surakarta</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Jl. A. Yani No. 157, Pabelan, Kartasura, Sukoharjo, Jawa Tengah 57162</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Telp. +62271717417 ext. 1130, Fax. 0271-715448</div>
      <div style="font-size:7.5pt;color:#444;">Website: https://kemahasiswaan.ums.ac.id | E-mail: kemahasiswaan@ums.ac.id</div>
    </td>
  </tr>
</table>
<hr style="border:none;border-top:2pt solid #1a3a7a;margin:4pt 0 0 0;" />
<hr style="border:none;border-top:0.5pt solid #000;margin:1.5pt 0 0 0;" />
<div style="text-align:center;font-size:11pt;font-weight:bold;text-transform:uppercase;letter-spacing:0.8pt;margin-top:3pt;">Laporan Konseling</div>
<div style="text-align:center;font-size:8.5pt;color:#555;">No. Klien: <b>{$nomorKlien}</b></div>
HTML;

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 20,
            'margin_right'      => 20,
            'margin_top'        => 50,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'margin_footer'     => 0,
            'default_font'      => 'dejavuserif',
            'default_font_size' => 11,
        ]);

        $mpdf->SetTitle('Laporan Konseling #' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT));
        $mpdf->SetAuthor('Admin SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->WriteHTML($htmlContent);

        $filename = 'laporan-konseling-' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT) . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /admin/janji/:id/surat-rujukan */
    public function suratRujukan(int $id): mixed
    {
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->withDetail()->where('janji.id', $id)->first();

        if (! $janji || $janji['status'] !== 'selesai') {
            return redirect()->to(base_url('admin/janji/' . $id))
                ->with('error', 'Surat rujukan hanya tersedia untuk sesi yang sudah selesai.');
        }

        $hasilModel = new HasilKonselingModel();
        $hasil      = $hasilModel->byJanji($id);

        if (! $hasil || ! $hasil['ada_rujukan']) {
            return redirect()->to(base_url('admin/janji/' . $id))
                ->with('error', 'Tidak ada rujukan untuk sesi ini.');
        }

        $konselorData = null;
        if (! empty($janji['konselor_id'])) {
            $konselorModel = new KonselorModel();
            $konselorData  = $konselorModel
                ->select('konselor.*, users.name')
                ->join('users', 'users.id = konselor.user_id')
                ->find($janji['konselor_id']);
        }

        $instansiRujukanList = (new InstansiRujukanModel())->getAll();
        $checklistData       = (new ChecklistItemModel())->allForForm();

        [$namaInstansi, $alamatInstansi] = $this->resolveInstansi($hasil, $instansiRujukanList);

        $htmlContent = view('admin/janji/pdf_surat_rujukan', [
            'janji'          => $janji,
            'hasil'          => $hasil,
            'konselor'       => $konselorData,
            'namaInstansi'   => $namaInstansi,
            'alamatInstansi' => $alamatInstansi,
            'checklistData'  => $checklistData,
        ]);

        $logoPath   = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
        $romanMonth = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $nomorSurat = str_pad($janji['id'], 5, '0', STR_PAD_LEFT)
            . '/' . $romanMonth[date('n') - 1]
            . '/' . date('Y');

        $headerHtml = <<<HTML
<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="width:72pt;text-align:center;vertical-align:middle;padding-right:8pt;border-left:10pt solid #FFB800;">
      <img src="{$logoPath}" style="width:150pt;" />
    </td>
    <td style="vertical-align:middle;">
      <div style="font-size:10pt;font-weight:bold;color:#1a3a7a;margin-bottom:1pt;">Student Mental Health and Wellbeing Support (SMHWS)</div>
      <div style="font-size:8.5pt;margin-bottom:1pt;">Direktorat Kemahasiswaan dan Pengembangan Talenta-Inovasi</div>
      <div style="font-size:9pt;font-weight:bold;margin-bottom:1pt;">Universitas Muhammadiyah Surakarta</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Jl. A. Yani No. 157, Pabelan, Kartasura, Sukoharjo, Jawa Tengah 57162</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Telp. +62271717417 ext. 1130, Fax. 0271-715448</div>
      <div style="font-size:7.5pt;color:#444;">Website: https://kemahasiswaan.ums.ac.id | E-mail: kemahasiswaan@ums.ac.id</div>
    </td>
  </tr>
</table>
<hr style="border:none;border-top:2pt solid #1a3a7a;margin:4pt 0 0 0;" />
<hr style="border:none;border-top:0.5pt solid #000;margin:1.5pt 0 0 0;" />
<div style="text-align:center;font-size:11pt;font-weight:bold;text-transform:uppercase;letter-spacing:0.8pt;margin-top:3pt;">Surat Rujukan</div>
<div style="text-align:center;font-size:8.5pt;color:#555;">No: {$nomorSurat}</div>
HTML;

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 25,
            'margin_right'      => 25,
            'margin_top'        => 50,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'margin_footer'     => 0,
            'default_font'      => 'dejavuserif',
            'default_font_size' => 11,
        ]);

        $mpdf->SetTitle('Surat Rujukan #' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT));
        $mpdf->SetAuthor('Admin SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->WriteHTML($htmlContent);

        $filename = 'surat-rujukan-' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT) . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /admin/dashboard-univ */
    public function dashboardUniv(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();

        // Semua prodi dari tabel lembaga (terurut: nama fakultas → nama prodi)
        $allLembaga = $db->table('lembaga')
            ->select('id_lembaga, nama_prodi, id_fakultas, nama_fakultas, level')
            ->orderBy('nama_fakultas', 'ASC')
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();

        // Daftar unik fakultas (pertahankan urutan hasil query di atas)
        $fakultasMap = [];
        foreach ($allLembaga as $row) {
            if ($row['id_fakultas'] && ! isset($fakultasMap[$row['id_fakultas']])) {
                $fakultasMap[$row['id_fakultas']] = $row['nama_fakultas'];
            }
        }

        $filters = [
            'fakultas'    => $this->request->getGet('fakultas')    ?? '',
            'lembaga'     => $this->request->getGet('lembaga')     ?? '',
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        // Resolve kode untuk buildProdiStats
        $kode      = '';
        $prodiNama = 'Semua Program Studi';

        if ($filters['lembaga'] !== '') {
            // Filter spesifik satu prodi
            $kode = $filters['lembaga'];
            foreach ($allLembaga as $r) {
                if ($r['id_lembaga'] === $kode) { $prodiNama = $r['nama_prodi']; break; }
            }
        } elseif ($filters['fakultas'] !== '') {
            // Filter seluruh prodi dalam satu fakultas
            $prodiDibalik = array_filter($allLembaga, fn($r) => $r['id_fakultas'] === $filters['fakultas']);
            $kode         = array_column(array_values($prodiDibalik), 'id_lembaga');
            $prodiNama    = 'Semua Prodi — ' . ($fakultasMap[$filters['fakultas']] ?? $filters['fakultas']);
        }

        $bulan = (int) date('n');
        $tahunMulai = $bulan >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $tahunAkdOptions = [];
        for ($i = 0; $i < 5; $i++) {
            $y = $tahunMulai - $i;
            $tahunAkdOptions[] = $y . '/' . ($y + 1);
        }

        $stats = $this->buildProdiStats($db, $kode, $filters);

        return view('admin/dashboard_univ', [
            'prodi'           => $prodiNama,
            'stats'           => $stats,
            'filters'         => $filters,
            'tahunAkdOptions' => $tahunAkdOptions,
            'fakultasMap'     => $fakultasMap,
            'allLembaga'      => $allLembaga,
        ]);
    }

    /** GET /admin/dashboard-univ/pdf?[filters] — Export laporan agregat ke PDF */
    public function dashboardUnivPdf(): mixed
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();

        $allLembaga = $db->table('lembaga')
            ->select('id_lembaga, nama_prodi, id_fakultas, nama_fakultas')
            ->orderBy('nama_fakultas', 'ASC')
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();

        $fakultasMap = [];
        foreach ($allLembaga as $row) {
            if ($row['id_fakultas'] && ! isset($fakultasMap[$row['id_fakultas']])) {
                $fakultasMap[$row['id_fakultas']] = $row['nama_fakultas'];
            }
        }

        $filters = [
            'fakultas'    => $this->request->getGet('fakultas')    ?? '',
            'lembaga'     => $this->request->getGet('lembaga')     ?? '',
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        $kode         = '';
        $prodiNama    = 'Semua Program Studi';
        $fakultasNama = 'Semua Fakultas';

        if ($filters['lembaga'] !== '') {
            $kode = $filters['lembaga'];
            foreach ($allLembaga as $r) {
                if ($r['id_lembaga'] === $kode) {
                    $prodiNama    = $r['nama_prodi'];
                    $fakultasNama = $r['nama_fakultas'] ?? 'Semua Fakultas';
                    break;
                }
            }
        } elseif ($filters['fakultas'] !== '') {
            $prodiDibalik = array_filter($allLembaga, fn($r) => $r['id_fakultas'] === $filters['fakultas']);
            $kode         = array_column(array_values($prodiDibalik), 'id_lembaga');
            $prodiNama    = 'Semua Program Studi';
            $fakultasNama = $fakultasMap[$filters['fakultas']] ?? $filters['fakultas'];
        }

        // Bangun label periode
        $bulanId = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        if (! empty($filters['tahun_akd'])) {
            $smtLabel   = match($filters['smt_akd'] ?? '') {
                'ganjil' => ' Semester Ganjil (Juli–Desember)',
                'genap'  => ' Semester Genap (Januari–Juni)',
                default  => '',
            };
            $periodeLabel = 'Tahun Akademik ' . $filters['tahun_akd'] . $smtLabel;
        } elseif (! empty($filters['tgl_mulai']) || ! empty($filters['tgl_selesai'])) {
            $fmt = static function (string $tgl) use ($bulanId): string {
                $t = strtotime($tgl);
                return date('j', $t) . ' ' . $bulanId[(int) date('n', $t)] . ' ' . date('Y', $t);
            };
            $dari    = $filters['tgl_mulai']   ? $fmt($filters['tgl_mulai'])   : '—';
            $sampai  = $filters['tgl_selesai'] ? $fmt($filters['tgl_selesai']) : '—';
            $periodeLabel = 'Periode ' . $dari . ' s.d. ' . $sampai;
        } else {
            $periodeLabel = 'Semua Periode';
        }

        $stats = $this->buildProdiStats($db, $kode, $filters);

        $htmlContent = view('admin/dashboard_univ_pdf', [
            'stats'        => $stats,
            'filters'      => $filters,
            'prodiNama'    => $prodiNama,
            'fakultasNama' => $fakultasNama,
            'periodeLabel' => $periodeLabel,
        ]);

        $logoPath = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
        $headerHtml = <<<HTML
<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="width:72pt;text-align:center;vertical-align:middle;padding-right:8pt;border-left:10pt solid #FFB800;">
      <img src="{$logoPath}" style="width:150pt;" />
    </td>
    <td style="vertical-align:middle;">
      <div style="font-size:10pt;font-weight:bold;color:#1a3a7a;margin-bottom:1pt;">Student Mental Health and Wellbeing Support (SMHWS)</div>
      <div style="font-size:8.5pt;margin-bottom:1pt;">Direktorat Kemahasiswaan dan Pengembangan Talenta-Inovasi</div>
      <div style="font-size:9pt;font-weight:bold;margin-bottom:1pt;">Universitas Muhammadiyah Surakarta</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Jl. A. Yani No. 157, Pabelan, Kartasura, Sukoharjo, Jawa Tengah 57162</div>
      <div style="font-size:7.5pt;color:#444;margin-bottom:0.5pt;">Telp. +62271717417 ext. 1130, Fax. 0271-715448</div>
      <div style="font-size:7.5pt;color:#444;">Website: https://kemahasiswaan.ums.ac.id | E-mail: kemahasiswaan@ums.ac.id</div>
    </td>
  </tr>
</table>
<hr style="border:none;border-top:2pt solid #1a3a7a;margin:4pt 0 0 0;" />
<hr style="border:none;border-top:0.5pt solid #000;margin:1.5pt 0 0 0;" />
HTML;

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 20,
            'margin_right'      => 20,
            'margin_top'        => 38,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'margin_footer'     => 5,
            'default_font'      => 'dejavusans',
            'default_font_size' => 10,
        ]);

        $bulan   = (int) date('n');
        $tglCetak = date('j') . ' ' . $bulanId[$bulan] . ' ' . date('Y');
        $mpdf->SetTitle('Laporan Agregat Konseling SMHWS UMS');
        $mpdf->SetAuthor('Admin SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter('<div style="text-align:right;font-size:7.5pt;color:#888;">Dicetak: ' . $tglCetak . ' &nbsp;|&nbsp; Hal. {PAGENO} dari {nbpg}</div>');
        $mpdf->WriteHTML($htmlContent);

        $filename = 'laporan-agregat-konseling-' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /admin/stressor-detail?key=akademik&[filters] — JSON detail untuk modal */
    public function stressorDetail(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in') ||
            ! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $validKeys = [
            'akademik'        => 'Akademik',
            'karier'          => 'Karir & Masa Depan',
            'keluarga'        => 'Keluarga',
            'relasi_sosial'   => 'Relasi Sosial',
            'relasi_romantis' => 'Relasi Romantis',
            'finansial'       => 'Finansial',
            'digital'         => 'Digital & Media Sosial',
            'kesehatan'       => 'Kesehatan',
        ];

        $key = $this->request->getGet('key');
        if (! array_key_exists($key, $validKeys)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid stressor key']);
        }

        $db = \Config\Database::connect();

        $filters = [
            'fakultas'    => $this->request->getGet('fakultas')    ?? '',
            'lembaga'     => $this->request->getGet('lembaga')     ?? '',
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        $allLembaga = $db->table('lembaga')->select('id_lembaga, id_fakultas')->get()->getResultArray();
        $kode = '';
        if ($filters['lembaga'] !== '') {
            $kode = $filters['lembaga'];
        } elseif ($filters['fakultas'] !== '') {
            $prodiFak = array_filter($allLembaga, fn($r) => $r['id_fakultas'] === $filters['fakultas']);
            $kode     = array_column(array_values($prodiFak), 'id_lembaga');
        }

        $base     = $this->buildJanjiBase($db, $kode, $filters);
        $metaRows = (clone $base)
            ->select('j.id, j.semester, j.jenis_kelamin')
            ->get()->getResultArray();

        $janjiIds = array_column($metaRows, 'id');
        if (empty($janjiIds)) {
            return $this->response->setJSON([
                'label' => $validKeys[$key], 'total' => 0,
                'sub_items' => [], 'by_semester' => [], 'by_jk' => [],
            ]);
        }

        $janjiMeta = array_column($metaRows, null, 'id');

        $hkRows = $db->table('hasil_konseling')
            ->select('janji_id, stressor')
            ->whereIn('janji_id', $janjiIds)
            ->where('deleted_at IS NULL')
            ->get()->getResultArray();

        $smtGroups  = ['1-2', '3-4', '5-6', '7-8', '9-10', '11-12', '13-14'];
        $subItems   = [];
        $bySemester = array_fill_keys($smtGroups, 0);
        $byJk       = ['laki-laki' => 0, 'perempuan' => 0];
        $total      = 0;
        $smtBucket  = static function (int $s): string {
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

        foreach ($hkRows as $row) {
            $str = json_decode($row['stressor'] ?? '{}', true) ?: [];
            if (empty($str[$key])) continue;

            $total++;
            $items = is_array($str[$key]) ? $str[$key] : [$str[$key]];
            foreach ($items as $item) {
                $item = trim((string) $item);
                if ($item === '') continue;
                if ($item === 'Lainnya' && ! empty($str[$key . '_lainnya'])) {
                    $item = 'Lainnya: ' . trim($str[$key . '_lainnya']);
                }
                $subItems[$item] = ($subItems[$item] ?? 0) + 1;
            }

            $meta = $janjiMeta[$row['janji_id']] ?? null;
            if ($meta) {
                $bySemester[$smtBucket((int) $meta['semester'])]++;
                $jk = strtolower(trim($meta['jenis_kelamin'] ?? ''));
                if (isset($byJk[$jk])) $byJk[$jk]++;
            }
        }

        arsort($subItems);
        $subItemsArr = array_map(
            fn($lbl, $cnt) => ['label' => $lbl, 'count' => $cnt],
            array_keys($subItems), array_values($subItems)
        );

        return $this->response->setJSON([
            'label'       => $validKeys[$key],
            'total'       => $total,
            'sub_items'   => $subItemsArr,
            'by_semester' => $bySemester,
            'by_jk'       => $byJk,
        ]);
    }

    /** GET /admin/kalender */
    public function kalender(): string
    {
        return view('admin/kalender');
    }

    /** GET /admin/kalender/events — JSON feed untuk FullCalendar */
    public function kalenderEvents(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rangeStart = $this->request->getGet('start') ? substr($this->request->getGet('start'), 0, 10) : null;
        $rangeEnd   = $this->request->getGet('end')   ? substr($this->request->getGet('end'),   0, 10) : null;

        $janjiModel = new JanjiModel();

        // Janji dengan jadwal (tanggal_konseling terisi) — semua status kecuali menunggu
        $qJadwal = $janjiModel->withDetail()
            ->select('ku.name AS konselor_name, konselor.gelar_depan, konselor.gelar_belakang')
            ->join('konselor', 'konselor.id = janji.konselor_id', 'left')
            ->join('users AS ku', 'ku.id = konselor.user_id', 'left')
            ->where('janji.tanggal_konseling IS NOT NULL');
        if ($rangeStart) $qJadwal->where('janji.tanggal_konseling >=', $rangeStart);
        if ($rangeEnd)   $qJadwal->where('janji.tanggal_konseling <=', $rangeEnd);
        $listJadwal = $qJadwal->orderBy('janji.tanggal_konseling')->orderBy('janji.jam_konseling')->findAll();

        // Janji menunggu (belum punya tanggal_konseling) — gunakan tanggal preferensi mahasiswa,
        // fallback ke created_at. Filter range dilakukan di PHP setelah tanggal acuan diketahui.
        $listMenunggu = (new JanjiModel())->withDetail()
            ->where('janji.status', 'menunggu')
            ->where('janji.tanggal_konseling IS NULL')
            ->orderBy('janji.created_at')
            ->findAll();

        // Build konselor name map for menunggu events (konselor_pilihan stores IDs only)
        $konselorNameMap = [];
        $allKonselorIds  = [];
        foreach ($listMenunggu as $j) {
            foreach ((array) ($j['konselor_pilihan'] ?? []) as $kid) {
                if ($kid) $allKonselorIds[] = (int) $kid;
            }
        }
        if ($allKonselorIds) {
            $kRows = (new KonselorModel())
                ->select('konselor.id, ku2.name, konselor.gelar_depan, konselor.gelar_belakang')
                ->join('users AS ku2', 'ku2.id = konselor.user_id')
                ->whereIn('konselor.id', array_unique($allKonselorIds))
                ->findAll();
            foreach ($kRows as $k) {
                $konselorNameMap[$k['id']] = KonselorModel::namaLengkap($k);
            }
        }

        $statusColor = [
            'menunggu'     => '#f59e0b',
            'dikonfirmasi' => '#00bad1',
            'terjadwal'    => '#7367f0',
            'berlangsung'  => '#22c55e',
            'selesai'      => '#94a3b8',
            'dibatalkan'   => '#ef4444',
        ];

        $events = [];

        foreach ($listJadwal as $j) {
            $color  = $statusColor[$j['status']] ?? '#888888';
            $tgl    = $j['tanggal_konseling'];
            $jam    = ! empty($j['jam_konseling']) ? substr($j['jam_konseling'], 0, 5) : null;
            $allDay = $jam === null;

            $kNama = '';
            if (! empty($j['konselor_name'])) {
                $gd    = ! empty($j['gelar_depan'])    ? $j['gelar_depan'] . ' '     : '';
                $gb    = ! empty($j['gelar_belakang']) ? ', ' . $j['gelar_belakang'] : '';
                $kNama = trim($gd . $j['konselor_name'] . $gb);
            }

            $events[] = [
                'id'              => $j['id'],
                'title'           => $j['name'],
                'start'           => $allDay ? $tgl : $tgl . 'T' . $jam . ':00',
                'allDay'          => $allDay,
                'url'             => base_url('admin/janji/' . $j['id']),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'display'         => 'block',
                'extendedProps'   => [
                    'status'   => $j['status'],
                    'nim'      => $j['uniid'] ?? '',
                    'metode'   => $j['metode'] ?? '',
                    'konselor' => $kNama,
                ],
            ];
        }

        foreach ($listMenunggu as $j) {
            $color = $statusColor['menunggu'];

            // Resolve date+time: first slot from jadwal_pilihan, fallback date to created_at
            $pref  = $j['jadwal_pilihan'] ?? [];
            $tgl   = null;
            $waktu = null;
            foreach ((array) $pref as $slot) {
                if (! empty($slot['tanggal'])) {
                    $tgl   = substr($slot['tanggal'], 0, 10);
                    $waktu = ! empty($slot['waktu']) ? substr($slot['waktu'], 0, 5) : null;
                    break;
                }
            }
            $tgl ??= substr($j['created_at'], 0, 10);

            // Apply range filter in PHP now that we have the resolved date
            if ($rangeStart && $tgl < $rangeStart) continue;
            if ($rangeEnd   && $tgl > $rangeEnd)   continue;

            $firstKid = (int) (($j['konselor_pilihan'] ?? [])[0] ?? 0);
            $kNama    = $firstKid ? ($konselorNameMap[$firstKid] ?? '') : '';

            $events[] = [
                'id'              => $j['id'],
                'title'           => $j['name'],
                'start'           => $waktu ? $tgl . 'T' . $waktu . ':00' : $tgl,
                'allDay'          => $waktu === null,
                'display'         => 'block',
                'url'             => base_url('admin/janji/' . $j['id']),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'status'   => 'menunggu',
                    'nim'      => $j['uniid'] ?? '',
                    'metode'   => $j['metode'] ?? '',
                    'konselor' => $kNama,
                ],
            ];
        }

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode($events));
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

    /** POST /admin/janji/konfirmasi-kehadiran/:id — admin konfirmasi kehadiran atas nama mahasiswa */
    public function konfirmasiKehadiran(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->find($id);

        if (! $janji || $janji['status'] !== 'dikonfirmasi') {
            return redirect()->to(base_url('admin/janji/' . $id))
                ->with('error', 'Konfirmasi kehadiran hanya dapat dilakukan untuk janji berstatus Dikonfirmasi.');
        }

        $janjiModel->update($id, [
            'status'                  => 'terjadwal',
            'mahasiswa_konfirmasi_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/janji/' . $id))
            ->with('success', 'Kehadiran mahasiswa berhasil dikonfirmasi oleh admin.');
    }

    // ── Kelola Konselor ─────────────────────────────────────────────────────

    /** GET /admin/mahasiswa */
    public function mahasiswaList(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return redirect()->to(base_url('dashboard'));
        }

        $userModel     = new \App\Models\UserModel();
        $mahasiswaList = $userModel->mahasiswa()->orderBy('name', 'ASC')->findAll();

        $thisMonth = date('Y-m');
        $stats = [
            'total'     => count($mahasiswaList),
            'aktif'     => count(array_filter($mahasiswaList, fn($m) => (int)$m['is_active'] === 1)),
            'nonaktif'  => count(array_filter($mahasiswaList, fn($m) => (int)$m['is_active'] === 0)),
            'bulan_ini' => count(array_filter($mahasiswaList, fn($m) =>
                ! empty($m['created_at']) && substr($m['created_at'], 0, 7) === $thisMonth
            )),
        ];

        return view('admin/mahasiswa/index', [
            'mahasiswaList' => $mahasiswaList,
            'stats'         => $stats,
        ]);
    }

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
        return view('admin/konselor/form', [
            'konselor'    => null,
            'user'        => null,
            'jadwalSlots' => self::jadwalSlots(),
            'jadwalGrid'  => [],
        ]);
    }

    /** POST /admin/konselor/simpan */
    public function konselorSimpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post          = $this->request->getPost();
        $userModel     = new UserModel();
        $konselorModel = new KonselorModel();
        $db            = \Config\Database::connect();

        $nimNip       = $post['uniid'] ?? null;
        $existingUser = $nimNip ? $userModel->where('uniid', $nimNip)->first() : null;

        if (! $existingUser) {
            $existingUser = $userModel->where('email', $post['email'])->first();
        }

        if (! $existingUser && (empty($post['password']) || strlen($post['password']) < 8)) {
            return redirect()->back()->withInput()
                ->with('error', 'Password minimal 8 karakter.');
        }

        $db->transStart();

        if ($existingUser) {
            $userModel->skipValidation(true)->update($existingUser['id'], ['role' => 'konselor']);
            $userId = $existingUser['id'];
        } else {
            $userId = $userModel->skipValidation(true)->insert([
                'name'              => $post['name'],
                'email'             => $post['email'],
                'password'          => $post['password'],
                'role'              => 'konselor',
                'uniid'           => $nimNip,
                'phone'             => $post['phone'] ?? null,
                'is_superadmin'     => 0,
                'is_admin_fakultas' => 0,
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ], true);
        }

        $spesialisasi = $this->parseSpesialisasi($post['spesialisasi'] ?? '');

        $konselorId = $konselorModel->insert([
            'user_id'             => $userId,
            'nip'                 => $post['uniid'] ?? null,
            'uniid'               => ! empty($post['is_dosen']) ? ($post['uniid'] ?? null) : null,
            'gelar_depan'         => $post['gelar_depan'] ?? null,
            'gelar_belakang'      => $post['gelar_belakang'] ?? null,
            'spesialisasi'        => json_encode($spesialisasi),
            'bio'                 => $post['bio'] ?? null,
            'no_str'              => $post['no_str'] ?? null,
            'tahun_pengalaman'    => (int) ($post['tahun_pengalaman'] ?? 0),
            'max_pasien_per_hari' => (int) ($post['max_pasien_per_hari'] ?? 5),
            'is_available'        => isset($post['is_available']) ? 1 : 0,
        ], true);

        if ($konselorId) {
            $this->saveJadwal($konselorId, $post['jadwal'] ?? []);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data konselor.');
        }

        if ($konselorId) {
            $fotoPath = $this->uploadFotoKonselor($konselorId);
            if ($fotoPath) {
                $konselorModel->update($konselorId, ['foto' => $fotoPath]);
            }
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
            'konselor'    => $konselor,
            'user'        => $user,
            'jadwalSlots' => self::jadwalSlots(),
            'jadwalGrid'  => $this->loadJadwalGrid($id),
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
            'uniid' => $post['uniid'] ?? null,
        ];
        if (! empty($post['password'])) {
            $userData['password'] = $post['password'];
        }
        $userModel->skipValidation(true)->update($konselor['user_id'], $userData);

        $spesialisasi = $this->parseSpesialisasi($post['spesialisasi'] ?? '');

        $konselorData = [
            'nip'                 => $post['uniid'] ?? null,
            'uniid'               => ! empty($post['is_dosen']) ? ($post['uniid'] ?? null) : null,
            'gelar_depan'         => $post['gelar_depan'] ?? null,
            'gelar_belakang'      => $post['gelar_belakang'] ?? null,
            'spesialisasi'        => json_encode($spesialisasi),
            'bio'                 => $post['bio'] ?? null,
            'no_str'              => $post['no_str'] ?? null,
            'tahun_pengalaman'    => (int) ($post['tahun_pengalaman'] ?? 0),
            'max_pasien_per_hari' => (int) ($post['max_pasien_per_hari'] ?? 5),
            'is_available'        => isset($post['is_available']) ? 1 : 0,
        ];

        $fotoFile  = $this->request->getFile('foto');
        $hasNewFoto = $fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved();

        if ($hasNewFoto) {
            if (! empty($konselor['foto']) && is_file(FCPATH . $konselor['foto'])) {
                @unlink(FCPATH . $konselor['foto']);
            }
            $newPath = $this->uploadFotoKonselor($id);
            if ($newPath) {
                $konselorData['foto'] = $newPath;
            }
        } elseif (! empty($post['hapus_foto'])) {
            if (! empty($konselor['foto']) && is_file(FCPATH . $konselor['foto'])) {
                @unlink(FCPATH . $konselor['foto']);
            }
            $konselorData['foto'] = null;
        }

        $konselorModel->update($id, $konselorData);

        $this->saveJadwal($id, $post['jadwal'] ?? []);

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

        if (! empty($konselor['foto']) && is_file(FCPATH . $konselor['foto'])) {
            @unlink(FCPATH . $konselor['foto']);
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

    // ── Instansi Rujukan CRUD ────────────────────────────────────────────────

    /** GET /admin/instansi-rujukan */
    public function instansiList(): string
    {
        $model = new InstansiRujukanModel();
        $list  = $model->getAll();

        $db     = \Config\Database::connect();
        $counts = [];
        foreach ($list as $item) {
            $counts[$item['id']] = $db->table('hasil_konseling')
                ->where('instansi_rujukan_id', $item['id'])
                ->countAllResults();
        }

        return view('admin/instansi_rujukan/index', [
            'list'   => $list,
            'counts' => $counts,
        ]);
    }

    /** GET /admin/instansi-rujukan/buat */
    public function instansiBuat(): string
    {
        return view('admin/instansi_rujukan/form', ['instansi' => null]);
    }

    /** POST /admin/instansi-rujukan/simpan */
    public function instansiSimpan(): \CodeIgniter\HTTP\RedirectResponse
    {
        $post = $this->request->getPost();

        if (empty(trim($post['nama_instansi'] ?? ''))) {
            return redirect()->back()->withInput()
                ->with('error', 'Nama instansi tidak boleh kosong.');
        }

        (new InstansiRujukanModel())->insert([
            'nama_instansi' => trim($post['nama_instansi']),
            'singkatan'     => trim($post['singkatan'] ?? ''),
            'alamat'        => trim($post['alamat'] ?? ''),
        ]);

        return redirect()->to(base_url('admin/instansi-rujukan'))
            ->with('success', 'Instansi rujukan berhasil ditambahkan.');
    }

    /** GET /admin/instansi-rujukan/edit/:id */
    public function instansiEdit(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $model    = new InstansiRujukanModel();
        $instansi = $model->find($id);

        if (! $instansi) {
            return redirect()->to(base_url('admin/instansi-rujukan'))
                ->with('error', 'Instansi tidak ditemukan.');
        }

        return view('admin/instansi_rujukan/form', ['instansi' => $instansi]);
    }

    /** POST /admin/instansi-rujukan/update/:id */
    public function instansiUpdate(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $post  = $this->request->getPost();
        $model = new InstansiRujukanModel();

        if (! $model->find($id)) {
            return redirect()->to(base_url('admin/instansi-rujukan'))
                ->with('error', 'Instansi tidak ditemukan.');
        }

        if (empty(trim($post['nama_instansi'] ?? ''))) {
            return redirect()->back()->withInput()
                ->with('error', 'Nama instansi tidak boleh kosong.');
        }

        $model->update($id, [
            'nama_instansi' => trim($post['nama_instansi']),
            'singkatan'     => trim($post['singkatan'] ?? ''),
            'alamat'        => trim($post['alamat'] ?? ''),
        ]);

        return redirect()->to(base_url('admin/instansi-rujukan'))
            ->with('success', 'Instansi rujukan berhasil diperbarui.');
    }

    /** POST /admin/instansi-rujukan/hapus/:id */
    public function instansiHapus(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $model    = new InstansiRujukanModel();
        $instansi = $model->find($id);

        if (! $instansi) {
            return redirect()->to(base_url('admin/instansi-rujukan'))
                ->with('error', 'Instansi tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(base_url('admin/instansi-rujukan'))
            ->with('success', 'Instansi rujukan berhasil dihapus.');
    }

    /** GET /admin/rekap-konseling */
    public function rekapKonseling(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();

        $filters = [
            'status'      => $this->request->getGet('status')      ?? '',
            'prodi'       => $this->request->getGet('prodi')       ?? '',
            'konselor_id' => $this->request->getGet('konselor_id') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
        ];

        $allProdi = $db->table('lembaga')
            ->select('id_lembaga, nama_prodi, id_fakultas, nama_fakultas')
            ->orderBy('nama_fakultas', 'ASC')
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();

        $konselorModel = new KonselorModel();
        $konselorList  = $konselorModel
            ->select('konselor.id, users.name, konselor.gelar_depan, konselor.gelar_belakang')
            ->join('users', 'users.id = konselor.user_id')
            ->orderBy('users.name', 'ASC')
            ->findAll();

        $bulanNow    = (int) date('n');
        $tahunMulai  = $bulanNow >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $tahunAkdOptions = [];
        for ($i = 0; $i < 5; $i++) {
            $y = $tahunMulai - $i;
            $tahunAkdOptions[] = $y . '/' . ($y + 1);
        }

        $q = $db->table('janji j')
            ->select('j.id, j.status, j.tanggal_konseling, j.jenis_kelamin, j.metode, j.keluhan_utama, j.semester')
            ->select('u.name AS mahasiswa_nama, u.uniid, u.prodi AS mahasiswa_prodi')
            ->select('ku.name AS konselor_name, k.gelar_depan, k.gelar_belakang')
            ->select('hk.diagnosis, hk.intervensi, hk.jam_mulai, hk.jam_selesai')
            ->join('users u', 'u.id = j.user_id')
            ->join('konselor k', 'k.id = j.konselor_id', 'left')
            ->join('users ku', 'ku.id = k.user_id', 'left')
            ->join('hasil_konseling hk', 'hk.janji_id = j.id AND hk.deleted_at IS NULL', 'left')
            ->where('j.deleted_at IS NULL')
            ->where('u.role', 'mahasiswa')
            ->whereNotIn('j.status', ['menunggu']);

        $validStatus = ['dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
        if ($filters['status'] !== '' && in_array($filters['status'], $validStatus, true)) {
            $q->where('j.status', $filters['status']);
        }
        if ($filters['jk'] !== '') {
            $q->where('j.jenis_kelamin', $filters['jk']);
        }
        if ($filters['konselor_id'] !== '') {
            $q->where('j.konselor_id', (int) $filters['konselor_id']);
        }
        if ($filters['prodi'] !== '') {
            $q->join('mahasiswa mhs', 'mhs.nim = u.uniid', 'inner')
              ->where('mhs.program_studi', $filters['prodi']);
        }
        if (! empty($filters['tgl_mulai'])) {
            $q->where('j.tanggal_konseling >=', $filters['tgl_mulai']);
        }
        if (! empty($filters['tgl_selesai'])) {
            $q->where('j.tanggal_konseling <=', $filters['tgl_selesai']);
        }
        if (! empty($filters['tahun_akd']) && str_contains($filters['tahun_akd'], '/')) {
            [$y1, $y2] = explode('/', $filters['tahun_akd']);
            $smt = $filters['smt_akd'] ?? '';
            if ($smt === 'ganjil') {
                $q->where('j.tanggal_konseling >=', "{$y1}-07-01")
                  ->where('j.tanggal_konseling <=', "{$y1}-12-31");
            } elseif ($smt === 'genap') {
                $q->where('j.tanggal_konseling >=', "{$y2}-01-01")
                  ->where('j.tanggal_konseling <=', "{$y2}-06-30");
            } else {
                $q->where('j.tanggal_konseling >=', "{$y1}-07-01")
                  ->where('j.tanggal_konseling <=', "{$y2}-06-30");
            }
        }

        $q->orderBy('j.tanggal_konseling', 'DESC')->orderBy('j.id', 'DESC');
        $rows = $q->get()->getResultArray();

        foreach ($rows as &$row) {
            $diag = ! empty($row['diagnosis'])  ? (json_decode($row['diagnosis'],  true) ?: []) : [];
            $intv = ! empty($row['intervensi']) ? (json_decode($row['intervensi'], true) ?: []) : [];
            $row['diagnosis_dsm5']   = $diag['dsm5']  ?? [];
            $row['intervensi_fokus'] = $intv['fokus'] ?? [];

            // Bobot sesi: >75 menit = 2, lainnya = 1
            $bobot = 1;
            if (! empty($row['jam_mulai']) && ! empty($row['jam_selesai'])) {
                $start = strtotime($row['jam_mulai']);
                $end   = strtotime($row['jam_selesai']);
                if ($end > $start && ($end - $start) / 60 > 75) {
                    $bobot = 2;
                }
            }
            $row['bobot_sesi'] = $bobot;
        }
        unset($row);

        return view('admin/rekap_konseling', [
            'rows'            => $rows,
            'filters'         => $filters,
            'allProdi'        => $allProdi,
            'konselorList'    => $konselorList,
            'tahunAkdOptions' => $tahunAkdOptions,
        ]);
    }

    /** GET /admin/rekap-konseling/export — unduh rekap sebagai file Excel */
    public function rekapKonselingExport(): mixed
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (! (session()->get('is_superadmin') || session()->get('is_admin_fakultas'))) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();

        $filters = [
            'status'      => $this->request->getGet('status')      ?? '',
            'prodi'       => $this->request->getGet('prodi')       ?? '',
            'konselor_id' => $this->request->getGet('konselor_id') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
        ];

        $q = $db->table('janji j')
            ->select('j.id, j.status, j.tanggal_konseling, j.jenis_kelamin, j.metode, j.keluhan_utama, j.semester')
            ->select('u.name AS mahasiswa_nama, u.uniid, u.prodi AS mahasiswa_prodi')
            ->select('ku.name AS konselor_name, k.gelar_depan, k.gelar_belakang')
            ->select('hk.diagnosis, hk.intervensi, hk.jam_mulai, hk.jam_selesai')
            ->join('users u', 'u.id = j.user_id')
            ->join('konselor k', 'k.id = j.konselor_id', 'left')
            ->join('users ku', 'ku.id = k.user_id', 'left')
            ->join('hasil_konseling hk', 'hk.janji_id = j.id AND hk.deleted_at IS NULL', 'left')
            ->where('j.deleted_at IS NULL')
            ->where('u.role', 'mahasiswa')
            ->whereNotIn('j.status', ['menunggu']);

        $validStatus = ['dikonfirmasi', 'terjadwal', 'berlangsung', 'selesai', 'dibatalkan'];
        if ($filters['status'] !== '' && in_array($filters['status'], $validStatus, true)) {
            $q->where('j.status', $filters['status']);
        }
        if ($filters['jk'] !== '') {
            $q->where('j.jenis_kelamin', $filters['jk']);
        }
        if ($filters['konselor_id'] !== '') {
            $q->where('j.konselor_id', (int) $filters['konselor_id']);
        }
        if ($filters['prodi'] !== '') {
            $q->join('mahasiswa mhs', 'mhs.nim = u.uniid', 'inner')
              ->where('mhs.program_studi', $filters['prodi']);
        }
        if (! empty($filters['tgl_mulai'])) {
            $q->where('j.tanggal_konseling >=', $filters['tgl_mulai']);
        }
        if (! empty($filters['tgl_selesai'])) {
            $q->where('j.tanggal_konseling <=', $filters['tgl_selesai']);
        }
        if (! empty($filters['tahun_akd']) && str_contains($filters['tahun_akd'], '/')) {
            [$y1, $y2] = explode('/', $filters['tahun_akd']);
            $smt = $filters['smt_akd'] ?? '';
            if ($smt === 'ganjil') {
                $q->where('j.tanggal_konseling >=', "{$y1}-07-01")
                  ->where('j.tanggal_konseling <=', "{$y1}-12-31");
            } elseif ($smt === 'genap') {
                $q->where('j.tanggal_konseling >=', "{$y2}-01-01")
                  ->where('j.tanggal_konseling <=', "{$y2}-06-30");
            } else {
                $q->where('j.tanggal_konseling >=', "{$y1}-07-01")
                  ->where('j.tanggal_konseling <=', "{$y2}-06-30");
            }
        }

        $q->orderBy('j.tanggal_konseling', 'ASC')->orderBy('j.id', 'ASC');
        $rows = $q->get()->getResultArray();

        // ── Build spreadsheet ────────────────────────────────────────────────
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Konseling');

        // Header row
        $headers = [
            'A' => 'No',
            'B' => 'Psikolog',
            'C' => 'NIM',
            'D' => 'Nama Mahasiswa',
            'E' => 'Kehadiran',
            'F' => 'Jml Sesi',
            'G' => 'Jenis Kelamin',
            'H' => 'Program Studi',
            'I' => 'Media',
            'J' => 'Masalah',
            'K' => 'Diagnosis Problem Normal Bermasalah',
            'L' => 'Fokus Intervensi',
        ];

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1A2B40']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'wrapText'   => true],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FFB0B0B0']]],
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        }
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Column widths
        $colWidths = ['A'=>6,'B'=>28,'C'=>16,'D'=>24,'E'=>12,'F'=>8,'G'=>12,'H'=>32,'I'=>10,'J'=>36,'K'=>40,'L'=>36];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Data rows
        $row = 2;
        foreach ($rows as $i => $r) {
            // Konselor name
            $gd   = ! empty($r['gelar_depan'])    ? $r['gelar_depan'] . ' '     : '';
            $gb   = ! empty($r['gelar_belakang']) ? ', ' . $r['gelar_belakang'] : '';
            $kNm  = ! empty($r['konselor_name']) ? $gd . $r['konselor_name'] . $gb : '—';

            // Kehadiran
            $kehadiran = match($r['status']) {
                'selesai'     => 'Hadir',
                'berlangsung' => 'Berlangsung',
                'dibatalkan'  => 'Tidak Hadir',
                'terjadwal'   => 'Terjadwal',
                default       => ucfirst($r['status']),
            };

            // Bobot sesi
            $bobot = 1;
            if (! empty($r['jam_mulai']) && ! empty($r['jam_selesai'])) {
                $start = strtotime($r['jam_mulai']);
                $end   = strtotime($r['jam_selesai']);
                if ($end > $start && ($end - $start) / 60 > 75) $bobot = 2;
            }

            // JK
            $jk = $r['jenis_kelamin'] === 'laki-laki' ? 'Laki-laki' : 'Perempuan';

            // Diagnosis
            $diag  = ! empty($r['diagnosis'])  ? (json_decode($r['diagnosis'],  true) ?: []) : [];
            $intv  = ! empty($r['intervensi']) ? (json_decode($r['intervensi'], true) ?: []) : [];
            $diagText  = implode("\n", $diag['dsm5']  ?? []);
            $fokusText = implode("\n", $intv['fokus'] ?? []);

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $kNm);
            $sheet->setCellValue('C' . $row, $r['uniid'] ?? '');
            $sheet->setCellValue('D' . $row, $r['mahasiswa_nama'] ?? '');
            $sheet->setCellValue('E' . $row, $kehadiran);
            $sheet->setCellValue('F' . $row, $bobot);
            $sheet->setCellValue('G' . $row, $jk);
            $sheet->setCellValue('H' . $row, $r['mahasiswa_prodi'] ?? '');
            $sheet->setCellValue('I' . $row, ucfirst($r['metode'] ?? ''));
            $sheet->setCellValue('J' . $row, $r['keluhan_utama'] ?? '');
            $sheet->setCellValue('K' . $row, $diagText);
            $sheet->setCellValue('L' . $row, $fokusText);

            // Row style: borders + wrap + valign top
            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                 'color'       => ['argb' => 'FFD0D0D0']]],
            ]);

            // Alternate row background
            if ($i % 2 === 1) {
                $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F7FA');
            }

            // Center align specific columns
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Freeze header row
        $sheet->freezePane('A2');

        // Output
        $filename = 'rekap-konseling-' . date('Ymd-His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveInstansi(array $hasil, array $instansiList): array
    {
        $nama   = '—';
        $alamat = '';
        if (! empty($hasil['instansi_rujukan_id'])) {
            foreach ($instansiList as $inst) {
                if ($inst['id'] == $hasil['instansi_rujukan_id']) {
                    $nama   = $inst['nama_instansi'];
                    $alamat = $inst['alamat'] ?? '';
                    break;
                }
            }
        } elseif (! empty($hasil['instansi_rujukan'])) {
            $nama = $hasil['instansi_rujukan'];
        }
        return [$nama, $alamat];
    }

    private function uploadFotoKonselor(int $konselorId): ?string
    {
        $file = $this->request->getFile('foto');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowed, true) || $file->getSize() > 2 * 1024 * 1024) {
            return null;
        }
        $dir = FCPATH . 'uploads/konselor/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = 'konselor_' . $konselorId . '_' . time() . '.' . $file->getClientExtension();
        $file->move($dir, $newName);
        return 'uploads/konselor/' . $newName;
    }

    private function parseSpesialisasi(string $raw): array
    {
        if (empty(trim($raw))) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private static function jadwalSlots(): array
    {
        return [
            's1' => ['mulai' => '08:00:00', 'selesai' => '09:00:00', 'label' => '08.00–09.00'],
            's2' => ['mulai' => '09:30:00', 'selesai' => '10:30:00', 'label' => '09.30–10.30'],
            's3' => ['mulai' => '11:00:00', 'selesai' => '12:00:00', 'label' => '11.00–12.00'],
            's4' => ['mulai' => '12:30:00', 'selesai' => '13:30:00', 'label' => '12.30–13.30'],
            's5' => ['mulai' => '14:00:00', 'selesai' => '15:00:00', 'label' => '14.00–15.00'],
        ];
    }

    private function loadJadwalGrid(int $konselorId): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('konselor_jadwal')
            ->where('konselor_id', $konselorId)
            ->where('is_active', 1)
            ->get()->getResultArray();

        $timeToKey = [];
        foreach (self::jadwalSlots() as $key => $slot) {
            $timeToKey[$slot['mulai']] = $key;
        }

        $grid = [];
        foreach ($rows as $r) {
            $slotKey = $timeToKey[$r['jam_mulai']] ?? null;
            if ($slotKey) {
                $grid[$r['hari']][$slotKey] = $r['metode'] ?? 'offline';
            }
        }
        return $grid;
    }

    private function saveJadwal(int $konselorId, array $jadwalPost): void
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->table('konselor_jadwal')->where('konselor_id', $konselorId)->delete();

        $inserts = [];
        $hariValid = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        foreach ($jadwalPost as $hari => $slotData) {
            if (! in_array($hari, $hariValid) || ! is_array($slotData)) continue;
            foreach ($slotData as $slotKey => $metode) {
                if (empty($metode) || ! isset(self::jadwalSlots()[$slotKey])) continue;
                $slot   = self::jadwalSlots()[$slotKey];
                // Sabtu hanya boleh offline
                if ($hari === 'sabtu') $metode = 'offline';
                $inserts[] = [
                    'konselor_id' => $konselorId,
                    'hari'        => $hari,
                    'jam_mulai'   => $slot['mulai'],
                    'jam_selesai' => $slot['selesai'],
                    'metode'      => $metode,
                    'kuota'       => 4,
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        if (! empty($inserts)) {
            $db->table('konselor_jadwal')->insertBatch($inserts);
        }
    }
}
