<?php

namespace App\Controllers;

class DosenController extends BaseController
{
    // ── Auth guards ──────────────────────────────────────────────────────────

    private function requireDosen(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }
        if (session()->get('user_role') !== 'dosen') {
            return redirect()->to(base_url('dashboard'));
        }
        return null;
    }

    // ── Dashboard utama dosen ────────────────────────────────────────────────

    public function dashboard(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($redir = $this->requireDosen()) return $redir;

        $isKaprodi = $this->resolveKaprodi();
        $isDekan   = $this->resolveDekan();

        return view('dosen/dashboard', ['isKaprodi' => $isKaprodi, 'isDekan' => $isDekan]);
    }

    // ── Dashboard Prodi (dosen atau konselor yang merupakan Kaprodi) ────────

    public function dashboardProdi(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $role = session()->get('user_role');
        if (! in_array($role, ['dosen', 'konselor'], true)) {
            return redirect()->to(base_url('dashboard'));
        }

        if (! $this->resolveKaprodi()) {
            return redirect()->to(base_url($role === 'konselor' ? 'konselor/dashboard' : 'dosen/dashboard'));
        }

        // Ambil kode_lembaga & nama prodi dari session (diisi resolveKaprodi())
        $kodeLembaga = session()->get('kaprodi_kode_lembaga') ?? '';
        $prodiNama   = session()->get('kaprodi_prodi_nama')   ?? '';

        // Filter dari GET
        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        // Opsi tahun akademik (5 tahun terakhir, mulai Juli)
        $bulan = (int) date('n');
        $tahunMulai = $bulan >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $tahunAkdOptions = [];
        for ($i = 0; $i < 5; $i++) {
            $y = $tahunMulai - $i;
            $tahunAkdOptions[] = $y . '/' . ($y + 1);
        }

        $db    = \Config\Database::connect();
        $stats = $this->buildProdiStats($db, $kodeLembaga, $filters);

        return view('dosen/dashboard_prodi', [
            'prodi'           => $prodiNama,
            'stats'           => $stats,
            'filters'         => $filters,
            'tahunAkdOptions' => $tahunAkdOptions,
        ]);
    }

    // ── Dashboard Fakultas (dosen atau konselor yang merupakan Dekan) ───────────

    public function dashboardFakultas(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $role = session()->get('user_role');
        if (! in_array($role, ['dosen', 'konselor'], true)) {
            return redirect()->to(base_url('dashboard'));
        }

        if (! $this->resolveDekan()) {
            return redirect()->to(base_url($role === 'konselor' ? 'konselor/dashboard' : 'dosen/dashboard'));
        }

        $kodeDekan = session()->get('dekan_kode_lembaga') ?? '';
        $db        = \Config\Database::connect();

        // Ambil nama fakultas dan semua prodi di bawahnya
        $fakRow = $db->table('lembaga')
            ->select('nama_fakultas')
            ->where('id_fakultas', $kodeDekan)
            ->limit(1)
            ->get()->getRowArray();
        $fakultasNama = $fakRow['nama_fakultas'] ?? (session()->get('dekan_fakultas_nama') ?? '—');

        $prodiRows    = $db->table('lembaga')
            ->select('id_lembaga, nama_prodi')
            ->where('id_fakultas', $kodeDekan)
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();
        $prodiCodes   = array_column($prodiRows, 'id_lembaga');
        $prodiNameMap = array_column($prodiRows, 'nama_prodi', 'id_lembaga');

        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
            'prodi'       => $this->request->getGet('prodi')       ?? '',
        ];

        // Scope: satu prodi jika filter valid, seluruh prodi jika tidak
        $kode = ($filters['prodi'] !== '' && isset($prodiNameMap[$filters['prodi']]))
            ? $filters['prodi']
            : $prodiCodes;

        $bulan = (int) date('n');
        $tahunMulai = $bulan >= 7 ? (int) date('Y') : (int) date('Y') - 1;
        $tahunAkdOptions = [];
        for ($i = 0; $i < 5; $i++) {
            $y = $tahunMulai - $i;
            $tahunAkdOptions[] = $y . '/' . ($y + 1);
        }

        $stats = $this->buildProdiStats($db, $kode, $filters);

        return view('dosen/dashboard_fakultas', [
            'fakultas'        => $fakultasNama,
            'prodiList'       => $prodiRows,
            'prodiNameMap'    => $prodiNameMap,
            'stats'           => $stats,
            'filters'         => $filters,
            'tahunAkdOptions' => $tahunAkdOptions,
        ]);
    }

    /** GET /dosen/dashboard-fakultas/pdf?[filters] — Export laporan agregat fakultas ke PDF */
    public function dashboardFakultasPdf(): mixed
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $role = session()->get('user_role');
        if (! in_array($role, ['dosen', 'konselor'], true) || ! $this->resolveDekan()) {
            return redirect()->to(base_url('dashboard'));
        }

        $kodeDekan = session()->get('dekan_kode_lembaga') ?? '';
        $db        = \Config\Database::connect();

        $fakRow = $db->table('lembaga')
            ->select('nama_fakultas')
            ->where('id_fakultas', $kodeDekan)
            ->limit(1)
            ->get()->getRowArray();
        $fakultasNama = $fakRow['nama_fakultas'] ?? (session()->get('dekan_fakultas_nama') ?? '—');

        $prodiRows    = $db->table('lembaga')
            ->select('id_lembaga, nama_prodi')
            ->where('id_fakultas', $kodeDekan)
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();
        $prodiCodes   = array_column($prodiRows, 'id_lembaga');
        $prodiNameMap = array_column($prodiRows, 'nama_prodi', 'id_lembaga');

        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
            'prodi'       => $this->request->getGet('prodi')       ?? '',
        ];

        $kode       = ($filters['prodi'] !== '' && isset($prodiNameMap[$filters['prodi']]))
            ? $filters['prodi']
            : $prodiCodes;
        $prodiNama  = ($filters['prodi'] !== '' && isset($prodiNameMap[$filters['prodi']]))
            ? $prodiNameMap[$filters['prodi']]
            : 'Semua Program Studi';

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        if (! empty($filters['tahun_akd'])) {
            $smtLabel = match($filters['smt_akd'] ?? '') {
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
            $dari         = $filters['tgl_mulai']   ? $fmt($filters['tgl_mulai'])   : '—';
            $sampai       = $filters['tgl_selesai'] ? $fmt($filters['tgl_selesai']) : '—';
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

        $logoPath   = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
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
            'margin_top'        => 48,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'margin_footer'     => 5,
            'default_font'      => 'dejavusans',
            'default_font_size' => 10,
        ]);

        $bulan    = (int) date('n');
        $tglCetak = date('j') . ' ' . $bulanId[$bulan] . ' ' . date('Y');
        $mpdf->SetTitle('Laporan Agregat Konseling — ' . $fakultasNama);
        $mpdf->SetAuthor('SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter('<div style="text-align:right;font-size:7.5pt;color:#888;">Dicetak: ' . $tglCetak . ' &nbsp;|&nbsp; Hal. {PAGENO} dari {nbpg}</div>');
        $mpdf->WriteHTML($htmlContent);

        $filename = 'laporan-agregat-fakultas-' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /dosen/stressor-detail-fakultas?key=...&[filters] — JSON detail stressor untuk modal (scope: fakultas) */
    public function stressorDetailFakultas(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in') || ! $this->resolveDekan()) {
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

        $kodeDekan  = session()->get('dekan_kode_lembaga') ?? '';
        $db         = \Config\Database::connect();
        $prodiRows  = $db->table('lembaga')->select('id_lembaga')->where('id_fakultas', $kodeDekan)->get()->getResultArray();
        $prodiCodes = array_column($prodiRows, 'id_lembaga');
        $prodiAll   = array_flip($prodiCodes); // untuk validasi

        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
            'prodi'       => $this->request->getGet('prodi')       ?? '',
        ];

        $kode = ($filters['prodi'] !== '' && isset($prodiAll[$filters['prodi']]))
            ? $filters['prodi']
            : $prodiCodes;

        $base     = $this->buildJanjiBase($db, $kode, $filters);
        $metaRows = (clone $base)->select('j.id, j.semester, j.jenis_kelamin')->get()->getResultArray();

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

    /** GET /dosen/dashboard-prodi/pdf?[filters] — Export laporan agregat prodi ke PDF */
    public function dashboardProdiPdf(): mixed
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $role = session()->get('user_role');
        if (! in_array($role, ['dosen', 'konselor'], true) || ! $this->resolveKaprodi()) {
            return redirect()->to(base_url('dashboard'));
        }

        $kodeLembaga = session()->get('kaprodi_kode_lembaga') ?? '';
        $prodiNama   = session()->get('kaprodi_prodi_nama')   ?? 'Program Studi';

        $db = \Config\Database::connect();
        $lembaga = $db->table('lembaga')
            ->select('nama_fakultas')
            ->where('id_lembaga', $kodeLembaga)
            ->get()->getRowArray();
        $fakultasNama = $lembaga['nama_fakultas'] ?? '—';

        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        if (! empty($filters['tahun_akd'])) {
            $smtLabel = match($filters['smt_akd'] ?? '') {
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
            $dari        = $filters['tgl_mulai']   ? $fmt($filters['tgl_mulai'])   : '—';
            $sampai      = $filters['tgl_selesai'] ? $fmt($filters['tgl_selesai']) : '—';
            $periodeLabel = 'Periode ' . $dari . ' s.d. ' . $sampai;
        } else {
            $periodeLabel = 'Semua Periode';
        }

        $stats = $this->buildProdiStats($db, $kodeLembaga, $filters);

        $htmlContent = view('admin/dashboard_univ_pdf', [
            'stats'        => $stats,
            'filters'      => $filters,
            'prodiNama'    => $prodiNama,
            'fakultasNama' => $fakultasNama,
            'periodeLabel' => $periodeLabel,
        ]);

        $logoPath   = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
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
            'margin_top'        => 48,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'margin_footer'     => 5,
            'default_font'      => 'dejavusans',
            'default_font_size' => 10,
        ]);

        $bulan    = (int) date('n');
        $tglCetak = date('j') . ' ' . $bulanId[$bulan] . ' ' . date('Y');
        $mpdf->SetTitle('Laporan Agregat Konseling — ' . $prodiNama);
        $mpdf->SetAuthor('SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter('<div style="text-align:right;font-size:7.5pt;color:#888;">Dicetak: ' . $tglCetak . ' &nbsp;|&nbsp; Hal. {PAGENO} dari {nbpg}</div>');
        $mpdf->WriteHTML($htmlContent);

        $filename = 'laporan-agregat-prodi-' . date('Ymd') . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /dosen/stressor-detail?key=...&[filters] — JSON detail stressor untuk modal */
    public function stressorDetail(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! session()->get('is_logged_in') || ! $this->resolveKaprodi()) {
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

        $kodeLembaga = session()->get('kaprodi_kode_lembaga') ?? '';
        $filters = [
            'tahun_akd'   => $this->request->getGet('tahun_akd')   ?? '',
            'smt_akd'     => $this->request->getGet('smt_akd')     ?? '',
            'tgl_mulai'   => $this->request->getGet('tgl_mulai')   ?? '',
            'tgl_selesai' => $this->request->getGet('tgl_selesai') ?? '',
            'jk'          => $this->request->getGet('jk')          ?? '',
        ];

        $db       = \Config\Database::connect();
        $base     = $this->buildJanjiBase($db, $kodeLembaga, $filters);
        $metaRows = (clone $base)->select('j.id, j.semester, j.jenis_kelamin')->get()->getResultArray();

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

}
