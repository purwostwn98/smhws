<?php

namespace App\Controllers;

use App\Models\DokumenTerbitModel;

/** Halaman publik verifikasi dokumen bertanda tangan elektronik (tujuan QR pada PDF). */
class DokumenController extends BaseController
{
    /** GET /dokumen/:token */
    public function info(string $token): string
    {
        $dokumen = (new DokumenTerbitModel())->findByToken($token);

        if (! $dokumen) {
            $this->response->setStatusCode(404);
            return view('dokumen/info', ['dokumen' => null, 'utuh' => false]);
        }

        return view('dokumen/info', ['dokumen' => $dokumen, 'utuh' => $this->salinanUtuh($dokumen)]);
    }

    /** GET /dokumen/:token/salinan — tampilkan salinan PDF yang tersimpan saat diterbitkan */
    public function salinan(string $token): \CodeIgniter\HTTP\ResponseInterface
    {
        $dokumen = (new DokumenTerbitModel())->findByToken($token);
        if (! $dokumen || ! $this->salinanUtuh($dokumen)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $nama = preg_replace('/[^A-Za-z0-9\-]+/', '-', strtolower($dokumen['judul'])) . '-' . substr($token, 0, 8) . '.pdf';

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nama . '"')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setBody(file_get_contents(WRITEPATH . $dokumen['file_path']));
    }

    /** Salinan masih ada dan isinya sama persis dengan saat diterbitkan. */
    private function salinanUtuh(array $dokumen): bool
    {
        $path = WRITEPATH . $dokumen['file_path'];

        return is_file($path) && hash_file('sha256', $path) === $dokumen['file_hash'];
    }
}
