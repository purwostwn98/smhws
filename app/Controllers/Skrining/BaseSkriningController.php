<?php

namespace App\Controllers\Skrining;

use App\Controllers\BaseController;

/**
 * Dasar semua assessment publik (tanpa login, anonim).
 *
 * Menyediakan: data diri (jenis kelamin, semester, prodi dari tabel `lembaga`),
 * jeda antar pengiriman, penyimpanan hasil di session per jenis, dan pembuatan PDF.
 *
 * Menambah assessment baru:
 *   1. Buat controller turunan (lihat DassController) yang mengimplementasikan kodeJenis().
 *   2. Buat tabel hasil sendiri (kolom data diri sama dengan skrining_publik) + model.
 *   3. Buat view di app/Views/skrining/<kode>/ (index, hasil, pdf_content);
 *      form cukup menyertakan partial 'skrining/_data_diri'.
 *   4. Daftarkan di Config\Assessment::$jenis dan group route `skrining/<kode>` di Routes.php.
 */
abstract class BaseSkriningController extends BaseController
{
    protected const COOLDOWN_DETIK = 30;

    /** Kode unik assessment, sama dengan kunci di Config\Assessment::$jenis (mis. 'dass'). */
    abstract protected function kodeJenis(): string;

    // ── Data diri ───────────────────────────────────────────────────────────

    /** Prodi dikelompokkan per fakultas, dari tabel `lembaga`. */
    protected function prodiPerFakultas(): array
    {
        $rows = \Config\Database::connect()->table('lembaga')
            ->select('id_lembaga, nama_prodi, nama_fakultas')
            ->where('nama_prodi IS NOT NULL')
            ->where('nama_prodi !=', '')
            ->orderBy('nama_fakultas', 'ASC')
            ->orderBy('nama_prodi', 'ASC')
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $row) {
            $out[$row['nama_fakultas'] ?: 'Lainnya'][] = $row;
        }
        return $out;
    }

    /** Nilai lama form (setelah validasi gagal) untuk data diri. */
    protected function oldDataDiri(): array
    {
        $old = [];
        foreach (['jenis_kelamin', 'semester', 'id_lembaga'] as $k) {
            $old[$k] = old($k);
        }
        return $old;
    }

    /**
     * Validasi data diri dari POST.
     *
     * @return array{0: array, 1: string[]} [data siap simpan, daftar error]
     */
    protected function validasiDataDiri(array $post): array
    {
        $errors = [];

        $jk = $post['jenis_kelamin'] ?? '';
        if (! in_array($jk, ['laki-laki', 'perempuan'], true)) {
            $errors[] = 'Jenis kelamin wajib dipilih.';
        }

        $semester = (int) ($post['semester'] ?? 0);
        if ($semester < 1 || $semester > 14) {
            $errors[] = 'Semester wajib dipilih (1–14).';
        }

        $prodi     = null;
        $idLembaga = trim((string) ($post['id_lembaga'] ?? ''));
        if ($idLembaga !== '') {
            $prodi = \Config\Database::connect()->table('lembaga')
                ->select('id_lembaga, nama_prodi, nama_fakultas')
                ->where('id_lembaga', $idLembaga)
                ->get()->getRowArray();
        }
        if (! $prodi) {
            $errors[] = 'Program studi wajib dipilih.';
        }

        $data = $errors ? [] : [
            'jenis_kelamin' => $jk,
            'semester'      => $semester,
            'id_lembaga'    => $prodi['id_lembaga'],
            'nama_prodi'    => $prodi['nama_prodi'],
            'nama_fakultas' => $prodi['nama_fakultas'],
        ];

        return [$data, $errors];
    }

    // ── Jeda & hasil di session ─────────────────────────────────────────────

    protected function masihCooldown(): bool
    {
        $terakhir = (int) session()->get('skrining_terakhir_' . $this->kodeJenis());
        return $terakhir && (time() - $terakhir) < static::COOLDOWN_DETIK;
    }

    /** Simpan hasil anonim di session (bukan di URL yang bisa ditebak). Waktu dalam Asia/Jakarta. */
    protected function simpanHasilSession(array $hasil): void
    {
        $session = session();
        $session->set('skrining_terakhir_' . $this->kodeJenis(), time());
        $session->set('skrining_hasil_' . $this->kodeJenis(), $hasil + [
            'tanggal' => (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s'),
        ]);
    }

    protected function hasilSession(): ?array
    {
        return session()->get('skrining_hasil_' . $this->kodeJenis()) ?: null;
    }

    protected function urlForm(): string
    {
        return base_url('skrining/' . $this->kodeJenis());
    }

    // ── PDF ────────────────────────────────────────────────────────────────

    /** Kirim PDF (unduhan) dengan header SMHWS/UMS. */
    protected function kirimPdf(string $judul, string $html, string $namaFile): never
    {
        $logoPath   = ROOTPATH . 'public/assets/img/branding/logo-ums.png';
        $judulEsc   = esc($judul);
        $headerHtml = <<<HTML
<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="width:72pt;text-align:center;vertical-align:middle;padding-right:8pt;border-left:10pt solid #FFB800;">
      <img src="{$logoPath}" style="width:150pt;" />
    </td>
    <td style="vertical-align:middle;">
      <div style="font-size:10pt;font-weight:bold;color:#1a3a7a;margin-bottom:1pt;">Student Mental Health and Wellbeing Support (SMHWS)</div>
      <div style="font-size:8.5pt;margin-bottom:1pt;">Direktorat Kemahasiswaan dan Pengembangan Talenta-Inovasi</div>
      <div style="font-size:9pt;font-weight:bold;">Universitas Muhammadiyah Surakarta</div>
    </td>
  </tr>
</table>
<hr style="border:none;border-top:2pt solid #1a3a7a;margin:4pt 0 0 0;" />
<div style="text-align:center;font-size:11pt;font-weight:bold;text-transform:uppercase;letter-spacing:0.8pt;margin-top:3pt;">{$judulEsc}</div>
HTML;

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_left'       => 20,
            'margin_right'      => 20,
            'margin_top'        => 45,
            'margin_bottom'     => 20,
            'margin_header'     => 5,
            'default_font'      => 'dejavusans',
            'default_font_size' => 10,
        ]);
        $mpdf->SetTitle($judul);
        $mpdf->SetAuthor('SMHWS UMS');
        $mpdf->SetCreator('SMHWS UMS');
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->WriteHTML($html);
        $mpdf->Output($namaFile, \Mpdf\Output\Destination::DOWNLOAD);
        exit;
    }
}
