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
            ->withUser()
            ->where('konselor.user_id', session()->get('user_id'))
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

        // Cek & cache status kaprodi ke session (untuk menu sidebar)
        $this->resolveKaprodi();

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

        $checklistModel   = new \App\Models\ChecklistItemModel();
        $checklistData    = $checklistModel->allForForm();

        return view('konselor/janji/detail', [
            'konselor'        => $konselor,
            'janji'           => $janji,
            'safety'          => $safety,
            'dass'            => $dass,
            'hasil'           => $hasil,
            'instansiRujukan' => $instansiRujukan,
            'checklistData'   => $checklistData,
            'editMode'        => $this->request->getGet('edit') === '1',
        ]);
    }

    /** POST /konselor/janji/edit-hasil/:id — perbarui hasil konseling yang sudah selesai */
    public function editHasil(int $id): \CodeIgniter\HTTP\RedirectResponse
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

        if ($janji['status'] !== 'selesai') {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Hanya sesi selesai yang dapat diedit.');
        }

        $post       = $this->request->getPost();
        $adaRujukan = ((int)($post['ada_rujukan'] ?? 0)) === 1 ? 1 : 0;

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

        $encodeChecklist = static function (array $post, string $key): ?string {
            $val = $post[$key] ?? null;
            if (empty($val) || ! is_array($val)) return null;
            $filtered = array_map(
                fn($v) => is_array($v) ? array_values(array_filter($v)) : $v,
                $val
            );
            return json_encode($filtered, JSON_UNESCAPED_UNICODE);
        };

        $hasilModel = new HasilKonselingModel();
        $existing   = $hasilModel->byJanji($id);

        $hasilData = [
            'janji_id'            => $id,
            'konselor_id'         => $konselor['id'],
            'jam_mulai'           => $post['jam_mulai']   ?: null,
            'jam_selesai'         => $post['jam_selesai'] ?: null,
            'ada_rujukan'         => $adaRujukan,
            'instansi_rujukan_id' => $instansiRujukanId,
            'instansi_rujukan'    => $instansiRujukan,
            'alasan_rujukan'      => $adaRujukan ? ($post['alasan_rujukan'] ?? null) : null,
            'sesi_lanjutan'       => isset($post['sesi_lanjutan']) ? 1 : 0,
            'catatan_sesi'        => $post['catatan_sesi'] ?? null,
            'stressor'            => $encodeChecklist($post, 'stressor'),
            'faktor_kerentanan'   => $encodeChecklist($post, 'kerentanan'),
            'faktor_protektif'    => $encodeChecklist($post, 'protektif'),
            'strategi_koping'     => $encodeChecklist($post, 'koping'),
            'diagnosis'           => $encodeChecklist($post, 'diagnosis'),
            'intervensi'          => $encodeChecklist($post, 'intervensi'),
            'rekomendasi'         => $encodeChecklist($post, 'rekomendasi'),
            'prognosis'           => $encodeChecklist($post, 'prognosis'),
        ];

        if ($existing) {
            $hasilModel->update($existing['id'], $hasilData);
        } else {
            $hasilModel->insert($hasilData);
        }

        return redirect()->to(base_url('konselor/janji/' . $id))
            ->with('success', 'Hasil konseling berhasil diperbarui.');
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

        $encodeChecklist = static function (array $post, string $key): ?string {
            $val = $post[$key] ?? null;
            if (empty($val) || ! is_array($val)) return null;
            $filtered = array_map(
                fn($v) => is_array($v) ? array_values(array_filter($v)) : $v,
                $val
            );
            return json_encode($filtered, JSON_UNESCAPED_UNICODE);
        };

        $hasilData = [
            'janji_id'            => $id,
            'konselor_id'         => $konselor['id'],
            'jam_mulai'           => $post['jam_mulai']   ?: null,
            'jam_selesai'         => $post['jam_selesai'] ?: null,
            'ada_rujukan'         => $adaRujukan,
            'instansi_rujukan_id' => $instansiRujukanId,
            'instansi_rujukan'    => $instansiRujukan,
            'alasan_rujukan'      => $adaRujukan ? ($post['alasan_rujukan'] ?? null) : null,
            'sesi_lanjutan'       => isset($post['sesi_lanjutan']) ? 1 : 0,
            'catatan_sesi'        => $post['catatan_sesi'] ?? null,
            'stressor'            => $encodeChecklist($post, 'stressor'),
            'faktor_kerentanan'   => $encodeChecklist($post, 'kerentanan'),
            'faktor_protektif'    => $encodeChecklist($post, 'protektif'),
            'strategi_koping'     => $encodeChecklist($post, 'koping'),
            'diagnosis'           => $encodeChecklist($post, 'diagnosis'),
            'intervensi'          => $encodeChecklist($post, 'intervensi'),
            'rekomendasi'         => $encodeChecklist($post, 'rekomendasi'),
            'prognosis'           => $encodeChecklist($post, 'prognosis'),
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

    /** GET /konselor/profil */
    public function profil(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Data konselor tidak ditemukan.');
        }

        $userModel = new \App\Models\UserModel();
        $user      = $userModel->find($konselor['user_id']);

        return view('konselor/profil', [
            'konselor'    => $konselor,
            'user'        => $user,
            'jadwalSlots' => self::jadwalSlots(),
            'jadwalGrid'  => $this->loadJadwalGrid($konselor['id']),
        ]);
    }

    /** POST /konselor/profil/update */
    public function profilUpdate(): \CodeIgniter\HTTP\RedirectResponse
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'));
        }

        $post          = $this->request->getPost();
        $userModel     = new \App\Models\UserModel();
        $konselorModel = new KonselorModel();
        $db            = \Config\Database::connect();

        $existing = $userModel->where('email', $post['email'])
            ->where('id !=', $konselor['user_id'])->first();
        if ($existing) {
            return redirect()->back()->withInput()
                ->with('error', 'Email sudah digunakan akun lain.');
        }

        $userData = [
            'name'    => $post['name'],
            'email'   => $post['email'],
            'phone'   => $post['phone'] ?? null,
            'uniid' => $post['uniid'] ?? null,
        ];
        if (! empty($post['password'])) {
            if (strlen($post['password']) < 8) {
                return redirect()->back()->withInput()
                    ->with('error', 'Password minimal 8 karakter.');
            }
            $userData['password'] = $post['password'];
        }

        $spesialisasi = $this->parseSpesialisasi($post['spesialisasi'] ?? '');

        $db->transStart();

        $userModel->skipValidation(true)->update($konselor['user_id'], $userData);

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

        $fotoFile   = $this->request->getFile('foto');
        $hasNewFoto = $fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved();

        if ($hasNewFoto) {
            if (! empty($konselor['foto']) && is_file(FCPATH . $konselor['foto'])) {
                @unlink(FCPATH . $konselor['foto']);
            }
            $newPath = $this->uploadFotoKonselor($konselor['id']);
            if ($newPath) {
                $konselorData['foto'] = $newPath;
            }
        } elseif (! empty($post['hapus_foto'])) {
            if (! empty($konselor['foto']) && is_file(FCPATH . $konselor['foto'])) {
                @unlink(FCPATH . $konselor['foto']);
            }
            $konselorData['foto'] = null;
        }

        $konselorModel->update($konselor['id'], $konselorData);

        $this->saveJadwal($konselor['id'], $post['jadwal'] ?? []);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan perubahan.');
        }

        return redirect()->to(base_url('konselor/profil'))
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /** GET /konselor/janji/:id/pdf */
    public function exportPdf(int $id): mixed
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'));
        }

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->withDetail()->where('janji.id', $id)->first();

        if (! $janji || $janji['konselor_id'] != $konselor['id'] || $janji['status'] !== 'selesai') {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Laporan hanya tersedia untuk sesi selesai yang Anda tangani.');
        }

        $db   = \Config\Database::connect();
        $dass = (new DassAssessmentModel())->where('janji_id', $id)->first();
        $safety = $db->table('janji_safety_screening')->where('janji_id', $id)->get()->getRowArray();

        $hasil           = (new HasilKonselingModel())->byJanji($id);
        $checklistData   = (new \App\Models\ChecklistItemModel())->allForForm();
        $instansiRujukan = (new InstansiRujukanModel())->getAll();

        $konselorNama  = KonselorModel::namaLengkap($konselor);
        $konselorNoStr = $konselor['no_str'] ?? '—';

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
        $mpdf->SetAuthor('Konselor SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->WriteHTML($htmlContent);

        $filename = 'laporan-konseling-' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT) . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    /** GET /konselor/janji/:id/surat-rujukan */
    public function suratRujukan(int $id): mixed
    {
        $konselor = $this->getKonselorRecord();
        if (! $konselor) {
            return redirect()->to(base_url('dashboard'));
        }

        $janjiModel = new JanjiModel();
        $janji      = $janjiModel->withDetail()->where('janji.id', $id)->first();

        if (! $janji || $janji['konselor_id'] != $konselor['id'] || $janji['status'] !== 'selesai') {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Surat rujukan hanya tersedia untuk sesi selesai yang Anda tangani.');
        }

        $hasilModel = new HasilKonselingModel();
        $hasil      = $hasilModel->byJanji($id);

        if (! $hasil || ! $hasil['ada_rujukan']) {
            return redirect()->to(base_url('konselor/janji/' . $id))
                ->with('error', 'Tidak ada rujukan untuk sesi ini.');
        }

        $konselorData = $konselor;

        $instansiRujukanList = (new \App\Models\InstansiRujukanModel())->getAll();
        $checklistData       = (new \App\Models\ChecklistItemModel())->allForForm();

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
        $romanMonth = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $nomorSurat = str_pad($janji['id'], 5, '0', STR_PAD_LEFT)
                    . '/' . $romanMonth[date('n') - 1]
                    . '/' . date('Y');

        $headerHtml = <<<HTML
<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="width:72pt;text-align:center;vertical-align:middle;padding-right:8pt;border-left:10pt solid #1a3a7a;">
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
        $mpdf->SetAuthor('SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->WriteHTML($htmlContent);

        $filename = 'surat-rujukan-' . str_pad($janji['id'], 5, '0', STR_PAD_LEFT) . '.pdf';
        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }

    // ── Private helpers ─────────────────────────────────────────────────────

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

        $inserts   = [];
        $hariValid = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        foreach ($jadwalPost as $hari => $slotData) {
            if (! in_array($hari, $hariValid) || ! is_array($slotData)) continue;
            foreach ($slotData as $slotKey => $metode) {
                if (empty($metode) || ! isset(self::jadwalSlots()[$slotKey])) continue;
                $slot = self::jadwalSlots()[$slotKey];
                if ($hari === 'sabtu') $metode = 'online';
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

    /** POST /konselor/janji/tidak-hadir/:id — klien tidak hadir, batalkan sesi */
    public function tidakHadir(int $id): \CodeIgniter\HTTP\RedirectResponse
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
                ->with('error', 'Hanya janji berstatus Terjadwal yang dapat dibatalkan karena ketidakhadiran.');
        }

        $janjiModel->update($id, ['status' => 'dibatalkan']);

        return redirect()->to(base_url('konselor/janji'))
            ->with('success', 'Sesi dibatalkan karena klien tidak hadir.');
    }
}
