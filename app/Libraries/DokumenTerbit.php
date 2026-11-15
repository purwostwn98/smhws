<?php

namespace App\Libraries;

use App\Models\DokumenTerbitModel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;

/**
 * Penerbitan laporan PDF bertanda tangan elektronik: setiap PDF diberi token acak,
 * QR pada blok tanda tangan mengarah ke halaman verifikasi publik, dan salinannya
 * disimpan agar isi dokumen bisa dicocokkan kembali.
 */
class DokumenTerbit
{
    public const PENANDATANGAN_NAMA    = 'Dr. Usmi Karyani, M.Si., Psikolog';
    public const PENANDATANGAN_JABATAN = 'Ketua SMHWS UMS';

    private const BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    /**
     * Render $view dengan data tanda tangan elektronik, tulis ke $mpdf, simpan salinannya,
     * dan kembalikan isi PDF.
     *
     * View menerima tambahan: $ttdNama, $ttdJabatan, $ttdQr (data URI SVG), $ttdUrl.
     *
     * @param array{jenis:string, judul:string, cakupan:string, periode:string, filters:array} $meta
     */
    public static function terbitkan(Mpdf $mpdf, string $view, array $data, array $meta): string
    {
        $token = bin2hex(random_bytes(16));
        $url   = self::urlVerifikasi($token);
        $qr    = (new \TCPDF2DBarcode($url, 'QRCODE,M'))->getBarcodeSVGcode(3, 3);

        $mpdf->WriteHTML(view($view, $data + [
            'ttdNama'    => self::PENANDATANGAN_NAMA,
            'ttdJabatan' => self::PENANDATANGAN_JABATAN,
            'ttdQr'      => 'data:image/svg+xml;base64,' . base64_encode($qr),
            'ttdUrl'     => $url,
        ]));
        $pdf = $mpdf->Output('', Destination::STRING_RETURN);

        $relPath = 'dokumen_terbit/' . date('Y/m') . '/' . $token . '.pdf';
        $absPath = WRITEPATH . $relPath;
        if (! is_dir(dirname($absPath)) && ! mkdir(dirname($absPath), 0775, true) && ! is_dir(dirname($absPath))) {
            throw new RuntimeException('Gagal membuat folder penyimpanan dokumen.');
        }
        if (file_put_contents($absPath, $pdf) === false) {
            throw new RuntimeException('Gagal menyimpan salinan dokumen.');
        }

        $tersimpan = (new DokumenTerbitModel())->insert([
            'token'                 => $token,
            'jenis'                 => $meta['jenis'],
            'judul'                 => $meta['judul'],
            'cakupan'               => $meta['cakupan'],
            'periode'               => $meta['periode'],
            'filters'               => $meta['filters'],
            'file_path'             => $relPath,
            'file_hash'             => hash('sha256', $pdf),
            'file_size'             => strlen($pdf),
            'penandatangan_nama'    => self::PENANDATANGAN_NAMA,
            'penandatangan_jabatan' => self::PENANDATANGAN_JABATAN,
            'diterbitkan_oleh'      => (int) session()->get('user_id'),
            'diterbitkan_oleh_nama' => (string) session()->get('user_name'),
        ]);
        if (! $tersimpan) {
            unlink($absPath);
            throw new RuntimeException('Gagal mencatat dokumen terbit.');
        }

        return $pdf;
    }

    public static function urlVerifikasi(string $token): string
    {
        return base_url('dokumen/' . $token);
    }

    public static function tanggalIndonesia(?string $datetime = null): string
    {
        $t = $datetime ? strtotime($datetime) : time();

        return date('j', $t) . ' ' . self::BULAN[(int) date('n', $t)] . ' ' . date('Y', $t);
    }
}
